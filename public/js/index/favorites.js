(() => {
  const getFavoriteKey = (city, uid) => `${city}:${uid}`;

  const Favorites = {
    set: new Set(),
    pendingAdd: null,

    showMessage(message, isError = false) {
      const messageElem = $("#favorite-message");
      if (!messageElem.length) return;
      messageElem.text(message);
      messageElem.css("color", isError ? "#ff6b6b" : "#1abc9c");
      messageElem.show();
    },

    async load() {
      const toggleButton = $("#favorite-toggle-btn");
      if (!toggleButton.length) return;

      const response = await window.BGPP.API.fetchFavorites(() => {
        this.showMessage("Could not load favorites.", true);
      });

      this.set = new Set();
      if (!response || response.status !== "success" || !Array.isArray(response.data)) {
        return;
      }

      response.data.forEach((favorite) => {
        this.set.add(getFavoriteKey(favorite.city_key, favorite.station_uid));
      });
    },

    updateToggle(state) {
      const wrapper = $("#favorite-toggle-wrapper");
      const button = $("#favorite-toggle-btn");
      if (!wrapper.length || !button.length) return;

      const uid = state.getSelectedStationUid();
      if (!state.currQuery || !uid) {
        wrapper.hide();
        return;
      }

      const city = state.getCityRaw();
      const isFavorite = this.set.has(getFavoriteKey(city, uid));
      const icon = isFavorite ? "★" : "☆";
      const label = isFavorite ? " Remove from favorites" : " Add to favorites";

      button.html(`<span class="favorite-icon" aria-hidden="true">${icon}</span>${label}`);
      wrapper.show();
    },

    openModal(city, uid) {
      const modal = $("#favorite-note-modal");
      const input = $("#favorite-note-input");
      if (!modal.length || !input.length) return;

      this.pendingAdd = { city, uid };
      input.val("");
      modal.show();
      input.trigger("focus");
    },

    closeModal() {
      $("#favorite-note-modal").hide();
      this.pendingAdd = null;
    },

    async saveWithNote(state) {
      if (!this.pendingAdd) return;

      const note = ($("#favorite-note-input").val() || "").trim();
      if (note.length > 255) {
        this.showMessage("Note is too long (max 255 chars).", true);
        return;
      }

      const { city, uid } = this.pendingAdd;
      const key = getFavoriteKey(city, uid);
      const response = await window.BGPP.API.addFavorite(city, uid, note, (error) => {
        const message = error?.responseJSON?.message || "Could not update favorites.";
        this.showMessage(message, true);
      });

      if (!response || response.status !== "success") return;

      this.set.add(key);
      this.showMessage("Station added to favorites.");
      this.closeModal();
      this.updateToggle(state);
    },

    async toggle(state) {
      const uid = state.getSelectedStationUid();
      if (!uid) {
        this.showMessage("Please select a station first.", true);
        return;
      }

      const city = state.getCityRaw();
      const key = getFavoriteKey(city, uid);
      const isFavorite = this.set.has(key);

      if (!isFavorite) {
        this.openModal(city, uid);
        return;
      }

      const response = await window.BGPP.API.removeFavorite(city, uid, (error) => {
        const message = error?.responseJSON?.message || "Could not update favorites.";
        this.showMessage(message, true);
      });

      if (!response || response.status !== "success") return;

      this.set.delete(key);
      this.showMessage("Station removed from favorites.");
      this.updateToggle(state);
    },
  };

  window.BGPP = window.BGPP || {};
  window.BGPP.Favorites = Favorites;
})();
