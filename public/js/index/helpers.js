(() => {
  const formatSeconds = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    let secondsLeft = seconds % 60;
    if (secondsLeft < 10) secondsLeft = `0${secondsLeft}`;
    return `${minutes}:${secondsLeft}`;
  };

  const getDistanceFromCoords = (lat1, lon1, lat2, lon2) => {
    const earthRadiusInMeters = 6371000;
    const deltaLat = (lat2 - lat1) * (Math.PI / 180);
    const deltaLon = (lon2 - lon1) * (Math.PI / 180);

    const a =
      Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
      Math.cos(lat1 * (Math.PI / 180)) *
        Math.cos(lat2 * (Math.PI / 180)) *
        Math.sin(deltaLon / 2) *
        Math.sin(deltaLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return earthRadiusInMeters * c;
  };

  const getUserLocation = () => new Promise((resolve, reject) => {
    if (!("geolocation" in navigator)) {
      reject("Geolocation is not available in this browser.");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        });
      },
      (error) => reject(error.message)
    );
  });

  const findClosestStations = (userLocation, stationsArray, maxDistance = 300) => {
    return stationsArray
      .map((station) => {
        const distance = getDistanceFromCoords(
          userLocation.latitude,
          userLocation.longitude,
          ...station.coords
        );
        return { station, distance: Math.round(distance) };
      })
      .filter((entry) => entry.distance <= maxDistance)
      .sort((a, b) => a.distance - b.distance);
  };

  window.BGPP = window.BGPP || {};
  window.BGPP.Helpers = {
    formatSeconds,
    getUserLocation,
    findClosestStations,
  };
})();
