(() => {
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
    toggleTable() {
      const shouldShow = Boolean(state.currQuery);
      $("table").css("display", shouldShow ? "table" : "none");
    },

    updateArrivalsMeta(response) {
      const date = new Date();
      const name = `${response.station.name} (${response.station.id})`;
      $("#stationName").html(`Stanica: ${name}`).show();
      $("#lastUpdated").html(`Poslednji put ažurirano: ${date.toLocaleTimeString()}`).show();
      $("#updateInProgress").hide();
    },

    showArrivalsError(error) {
      $("#updateInProgress").hide();
      const message = error?.responseJSON?.message || "Unknown error";
      $("#error").html(`Greška pri ažuriranju podataka: ${message}`).show();
    },

    resetResults() {
      state.currQuery = null;
      state.currInterval = clearInterval(state.currInterval);
      window.BGPP.MapUI.clear();
      this.toggleTable();
      $("#stationName").hide();
      $("#lastUpdated").hide();
      window.BGPP.Favorites.updateToggle(state);
    },

    setLoading(flag) {
      $("#updateInProgress").toggle(flag);
      if (flag) $("#error").hide();
    },
  };

  const stations = {
    async loadCity(cityKey) {
      $("#name-input").html("<option> Dobavljanje liste stanica, molimo sacekajte... </option>");
      const response = await window.BGPP.API.fetchStations(cityKey);
      state.allStations[cityKey] = response?.data || [];
      this.fillNameSearch(cityKey);
    },

    fillNameSearch(cityKey) {
      const list = state.allStations[cityKey] || [];
      const options = list.map((station) => `<option value="${station.uid}">${station.name} (${station.id})</option>`);
      $("#name-input").html(options);
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
      window.BGPP.MapUI.moveToCityCenter(cityKey, !state.currInterval);

      if (!state.allStations[cityKey]) {
        await stations.loadCity(cityKey);
      } else {
        stations.fillNameSearch(cityKey);
      }

      window.BGPP.Favorites.updateToggle(state);
    },

    onSearchModeChange() {
      const searchMode = state.getSearchMode();
      $("#searchMode option").toArray().forEach((option) => {
        const value = $(option).val();
        $(`.${value}-search`).toggle(value === searchMode);
      });
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
        $("#error").html("Greška pri dobavljanju lokacije.").show();
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

  const bindEvents = () => {
    $(window).on("blur", handlers.onTabOut);
    $(window).on("focus", handlers.onTabIn);

    $("#city").on("change", handlers.onCityChange);
    $("#searchMode").on("change", handlers.onSearchModeChange);
    $("#name-input").on("change", () => ui.toggleTable());
    $("#coords-input").on("change", () => ui.toggleTable());

    $("#stationsMaxDistance-input").on("change", function onRangeChange() {
      $("#stationsMaxDistance-label").html(`Najveća udaljenost (${this.value}m):`);
    });

    $("#sort-lines").on("change", () => {
      if (state.currQuery) arrivals.start(state.currQuery);
    });

    $("#search-by-gps-btn").on("click", handlers.searchByGps);

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

    bindEvents();
    handlers.onSearchModeChange();
    await handlers.onCityChange();

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
