(() => {
  const getCsrfToken = () => $("meta[name='csrf-token']").attr("content") || "";

  const request = async (url, method = "GET", data = {}, errorHandler) => {
    if (!url) throw new Error("No url provided");
    try {
      const upperMethod = String(method).toUpperCase();
      const headers = {};
      if (upperMethod === "POST") {
        headers["X-CSRF-Token"] = getCsrfToken();
      }

      return await $.ajax({
        url,
        type: upperMethod,
        data,
        headers,
      });
    } catch (error) {
      if (errorHandler) {
        errorHandler(error);
        return null;
      }
      console.error("Error sending request:", error);
      return null;
    }
  };

  const API = {
    request,
    fetchStations(city) {
      return request(`/api/stations?city=${encodeURIComponent(city)}`);
    },
    fetchArrivals(city, query, onError) {
      return request(`/api/arrivals?city=${encodeURIComponent(city)}&${$.param(query)}`, "GET", {}, onError);
    },
    fetchFavorites(onError) {
      return request("/api/favorites.php", "GET", {}, onError);
    },
    addFavorite(city, uid, note, onError) {
      return request("/api/favorites.php", "POST", { action: "add", city, uid, note }, onError);
    },
    removeFavorite(city, uid, onError) {
      return request("/api/favorites.php", "POST", { action: "remove", city, uid }, onError);
    },
  };

  window.BGPP = window.BGPP || {};
  window.BGPP.API = API;
})();
