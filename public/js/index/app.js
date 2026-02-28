(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const cookie = {
    get(name) {
      const escaped = name.replace(/[-[\]/{}()*+?.\\^$|]/g, "\\$&");
      const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));
      return match ? decodeURIComponent(match[1]) : null;
    },

    set(name, value, days = 30) {
      const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
      const secure = window.location.protocol === "https:" ? "; Secure" : "";
      document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax${secure}`;
    },
  };

  const state = {
    currInterval: null,
    currQuery: null,
    allStations: {},

    getSearchMode() {
      return $("#searchMode").val();
    },

    getCityRaw() {
      return $("#city").val();
    },

    getSelectedStationUid() {
      const mode = this.getSearchMode();
      if (mode === "name") return $("#name-input").val();
      if (mode === "coords") return $("#coords-input").val();
      return null;
    },
  };

  const ui = {
    setCityBackground(cityKey) {
      const body = document.body;
      if (!body || !cityKey) {
        document.documentElement.style.setProperty("--city-bg-image", "none");
        return;
      }

      const src = `/pics/${encodeURIComponent(cityKey)}.jpg`;
      const img = new Image();
      img.onload = () => {
        document.documentElement.style.setProperty("--city-bg-image", `url("${src}")`);
      };
      img.onerror = () => {
        document.documentElement.style.setProperty("--city-bg-image", "none");
      };
      img.src = src;
    },

    toggleTable() {
      const shouldShow = Boolean(state.currQuery);
      $("table").css("display", shouldShow ? "table" : "none");
      $("#result-empty").toggle(!shouldShow);
    },

    updateArrivalsMeta(response) {
      const date = new Date();
      const name = `${response.station.name} (${response.station.id})`;
      $("#stationName").text(`Stanica: ${name}`).show();
      $("#lastUpdated").text(`Poslednji put ažurirano: ${date.toLocaleTimeString()}`).show();
      $("#updateInProgress").hide();
    },

    showArrivalsError(error) {
      $("#updateInProgress").hide();
      const message = error?.responseJSON?.message || "Unknown error";
      $("#error").text(`Greška pri ažuriranju podataka: ${message}`).show();
    },

    resetResults() {
      state.currQuery = null;
      state.currInterval = clearInterval(state.currInterval);
      window.BGPP.MapUI.clear();
      this.toggleTable();
      $("#stationName").hide();
      $("#lastUpdated").hide();
      this.updateSubmitState();
      window.BGPP.Favorites.updateToggle(state);
    },

    setLoading(flag) {
      $("#updateInProgress").toggle(flag);
      if (flag) $("#error").hide();
    },

    updateSubmitState() {
      const mode = state.getSearchMode();
      const selectedUid = state.getSelectedStationUid();
      const canSubmit = Boolean(selectedUid && (mode === "name" || mode === "coords"));
      $("#submit").prop("disabled", !canSubmit);
      $("#submit").css("opacity", canSubmit ? "1" : "0.6");
      $("#submit").css("cursor", canSubmit ? "pointer" : "not-allowed");
    },
  };

  const stations = {
    async loadCity(cityKey) {
      $("#name-input").empty().append(
        $("<option>").text("Dobavljanje liste stanica, molimo sacekajte...")
      );
      const response = await window.BGPP.API.fetchStations(cityKey);
      state.allStations[cityKey] = response?.data || [];
      this.fillNameSearch(cityKey);
    },

    fillNameSearch(cityKey) {
      const list = state.allStations[cityKey] || [];
      const select = $("#name-input");
      select.empty();
      list.forEach((station) => {
        select.append(
          $("<option>")
            .val(String(station.uid))
            .text(`${station.name} (${station.id})`)
        );
      });
    },
  };

  const arrivals = {
    async refresh(cityKey, query, recenter) {
      ui.setLoading(true);
      const response = await window.BGPP.API.fetchArrivals(cityKey, query, (error) => {
        ui.showArrivalsError(error);
      });
      if (!response || response.status !== "success") return;

      ui.updateArrivalsMeta(response.data);
      ui.toggleTable();
      window.BGPP.MapUI.renderArrivals(response.data, recenter);
    },

    start(query) {
      if (!query) query = state.currQuery;
      if (!query) return;

      const cityKey = state.getCityRaw();
      this.refresh(cityKey, query, true);

      state.currInterval = clearInterval(state.currInterval);
      state.currInterval = setInterval(() => {
        this.refresh(cityKey, query, false);
      }, 10 * 1000);
    },
  };

  const handlers = {
    async onCityChange() {
      const cityKey = state.getCityRaw();
      cookie.set("bgpp_city", cityKey);
      ui.setCityBackground(cityKey);
      window.BGPP.MapUI.moveToCityCenter(cityKey, !state.currInterval);

      if (!state.allStations[cityKey]) {
        await stations.loadCity(cityKey);
      } else {
        stations.fillNameSearch(cityKey);
      }

      ui.updateSubmitState();
      window.BGPP.Favorites.updateToggle(state);
    },

    onSearchModeChange() {
      const searchMode = state.getSearchMode();
      cookie.set("bgpp_search_mode", searchMode);
      ["name", "coords"].forEach((value) => {
        $(`.${value}-search`).toggle(value === searchMode);
      });
      $(".mode-toggle-btn").removeClass("active");
      $(`.mode-toggle-btn[data-mode='${searchMode}']`).addClass("active");
      ui.updateSubmitState();
      window.BGPP.Favorites.updateToggle(state);
    },

    submitByName() {
      const uid = encodeURIComponent(($("#name-input").val() || "").trim());
      state.currQuery = { uid };
      arrivals.start(state.currQuery);
      window.BGPP.Favorites.updateToggle(state);
    },

    submitByCoords() {
      const uid = encodeURIComponent(($("#coords-input").val() || "").trim());
      state.currQuery = { uid };
      arrivals.start(state.currQuery);
      window.BGPP.Favorites.updateToggle(state);
    },

    async searchByGps() {
      $("#error").hide();
      try {
        const userLocation = await window.BGPP.Helpers.getUserLocation();
        const cityStations = state.allStations[state.getCityRaw()] || [];
        const maxDistance = Number($("#stationsMaxDistance-input").val());
        const closest = window.BGPP.Helpers.findClosestStations(userLocation, cityStations, maxDistance);

        ui.resetResults();
        window.BGPP.MapUI.renderClosestStations(userLocation, closest, "#coords-input", (uid) => {
          $("#coords-input").val(uid).trigger("change");
        });
      } catch (_error) {
        $("#error").text(`Greška pri dobavljanju lokacije: ${_error}`).show();
      }
    },

    onTabOut() {
      if (!$("#dataSaver").is(":checked")) return;
      clearInterval(state.currInterval);
    },

    onTabIn() {
      if (!$("#dataSaver").is(":checked")) return;
      arrivals.start();
    },
  };

  const bootstrapFromCookies = () => {
    const savedMode = cookie.get("bgpp_search_mode");
    if (savedMode === "name" || savedMode === "coords") {
      $("#searchMode").val(savedMode);
    }

    const savedCity = (cookie.get("bgpp_city") || "").trim();
    if (!savedCity) return;

    const cityExists = $("#city option").toArray().some((option) => option.value === savedCity);
    if (cityExists) {
      $("#city").val(savedCity);
    }
  };

  const bootstrapFromUrl = async () => {
    const cityFromUrl = (urlParams.get("city") || "").trim();
    const uidFromUrl = (urlParams.get("uid") || "").trim();

    if (!cityFromUrl) return;

    const cityExists = $("#city option").toArray().some((option) => option.value === cityFromUrl);
    if (!cityExists) return;

    if (state.getCityRaw() !== cityFromUrl) {
      $("#city").val(cityFromUrl);
      await handlers.onCityChange();
    }

    if (!uidFromUrl) return;

    $("#searchMode").val("name").trigger("change");

    const stationExists = $("#name-input option").toArray().some((option) => option.value === uidFromUrl);
    if (!stationExists) {
      $("#error").text("Stanica iz linka nije pronađena za izabrani grad.").show();
      return;
    }

    $("#name-input").val(uidFromUrl).trigger("change");
    handlers.submitByName();
  };

  const bindEvents = () => {
    $(window).on("blur", handlers.onTabOut);
    $(window).on("focus", handlers.onTabIn);

    $("#city").on("change", handlers.onCityChange);
    $("#searchMode").on("change", handlers.onSearchModeChange);
    $("#name-input").on("change", () => ui.toggleTable());
    $("#name-input").on("change", () => {
      ui.updateSubmitState();
    });
    $("#coords-input").on("change", () => {
      ui.toggleTable();
      ui.updateSubmitState();
    });

    $("#stationsMaxDistance-input").on("change", function onRangeChange() {
      $("#stationsMaxDistance-label").text(`Najveća udaljenost (${this.value}m):`);
    });

    $("#sort-lines").on("change", () => {
      if (state.currQuery) arrivals.start(state.currQuery);
    });

    $("#search-by-gps-btn").on("click", handlers.searchByGps);
    $(".mode-toggle-btn").on("click", function onModeToggle() {
      const mode = $(this).data("mode");
      $("#searchMode").val(mode).trigger("change");
    });

    $("#myForm").on("submit", (event) => {
      event.preventDefault();
      const mode = state.getSearchMode();
      if (mode === "name") handlers.submitByName();
      if (mode === "coords") handlers.submitByCoords();
    });

    $("#favorite-toggle-btn").on("click", () => window.BGPP.Favorites.toggle(state));
    $("#favorite-note-save").on("click", () => window.BGPP.Favorites.saveWithNote(state));
    $("#favorite-note-cancel").on("click", () => window.BGPP.Favorites.closeModal());

    $("#favorite-note-modal").on("click", (event) => {
      if (event.target.id === "favorite-note-modal") {
        window.BGPP.Favorites.closeModal();
      }
    });

    $("#favorite-note-input").on("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        window.BGPP.Favorites.saveWithNote(state);
      }
      if (event.key === "Escape") {
        window.BGPP.Favorites.closeModal();
      }
    });

    $("#open-fair-usage-link").on("click", (event) => {
      event.preventDefault();
      $("#fair-usage-modal").show();
    });

    $("#close-fair-usage-btn").on("click", () => {
      $("#fair-usage-modal").hide();
    });
  };

  const init = async () => {
    window.BGPP.MapUI.init();
    $(".select2").select2({ width: "resolve" });

    bootstrapFromCookies();
    bindEvents();
    handlers.onSearchModeChange();
    await handlers.onCityChange();
    await bootstrapFromUrl();
    ui.updateSubmitState();

    await window.BGPP.Favorites.load();
    window.BGPP.Favorites.updateToggle(state);
    ui.toggleTable();
  };

  window.BGPP = window.BGPP || {};
  window.BGPP.IndexApp = {
    init,
    toggleTable: () => ui.toggleTable(),
    onCityChange: handlers.onCityChange,
    onSearchModeChange: handlers.onSearchModeChange,
    searchByGps: handlers.searchByGps,
    get submitHandlers() {
      return {
        name: handlers.submitByName,
        coords: handlers.submitByCoords,
      };
    },
    get currQuery() {
      return state.currQuery;
    },
  };
})();
