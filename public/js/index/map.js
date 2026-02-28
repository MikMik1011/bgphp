(() => {
  const CITY_CENTERS = {
    bg: [44.81254796404323, 20.46145496621977],
    ns: [45.267136, 19.833549],
    ni: [43.3209, 21.8958],
  };

  const colorIcon = (color) => new L.Icon({
    iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${color}.png`,
    shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png",
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -14],
    shadowSize: [41, 41],
  });

  const createMarker = (coords, name, color, popupText) => {
    const marker = new L.marker(coords, { icon: colorIcon(color || "blue") });
    if (popupText) {
      const popupNode = document.createElement("span");
      popupNode.textContent = String(popupText);
      marker.bindPopup(popupNode, { autoClose: false, closeOnClick: false });
    }
    if (name) {
      const tooltipNode = document.createElement("span");
      tooltipNode.textContent = String(name);
      marker.bindTooltip(tooltipNode, {
        permanent: true,
        direction: "center",
        className: "my-labels",
      });
    }
    return marker;
  };

  const MapUI = {
    map: null,
    layerGroup: null,

    init() {
      this.map = L.map("map", {
        center: CITY_CENTERS.bg,
        zoom: 13,
      });
      this.layerGroup = L.layerGroup().addTo(this.map);

      L.control.layers(mapLayers).addTo(this.map);
      mapLayers.Transport.addTo(this.map);
    },

    moveToCityCenter(cityKey, canMove = true) {
      if (!canMove || !CITY_CENTERS[cityKey]) return;
      this.map.setView(CITY_CENTERS[cityKey], 13, { animation: true });
    },

    clear() {
      this.layerGroup.clearLayers();
    },

    renderArrivals(response, recenter) {
      this.clear();
      if (recenter) {
        this.map.setView(response.station.coords, 13, { animation: true });
      }

      const markers = [];
      const stationName = `${response.station.name} (${response.station.id})`;
      markers.push(createMarker(response.station.coords, "", "yellow", stationName));

      const rows = response.lines
        .flatMap((line) => line.arrivals.map((arrival) => {
          markers.push(createMarker(arrival.coords, line.lineNumber, "blue", arrival.garageNo));
          return { line, arrival };
        }))
      ;

      const tableBody = $("#tableBody");
      tableBody.empty();
      rows.forEach(({ line, arrival }) => {
        const row = $("<tr>");
        row.append($("<td>").text(String(line.lineNumber)));
        row.append($("<td>").text(window.BGPP.Helpers.formatSeconds(arrival.etaSeconds)));
        row.append($("<td>").text(String(arrival.etaStations)));
        row.append($("<td>").text(String(arrival.garageNo)));
        tableBody.append(row);
      });
      const group = L.featureGroup(markers).addTo(this.layerGroup);
      if (recenter) this.map.fitBounds(group.getBounds());
    },

    renderClosestStations(searchCoords, closestStations, optionSelector, onMarkerClick) {
      this.clear();

      const markers = [];
      markers.push(createMarker([searchCoords.latitude, searchCoords.longitude], "", "green"));

      const select = $(optionSelector);
      select.empty();

      closestStations.forEach((entry) => {
        const marker = createMarker(entry.station.coords, entry.station.id, "yellow");
        marker.on("click", () => onMarkerClick(entry.station.uid));
        markers.push(marker);

        select.append(
          $("<option>")
            .val(String(entry.station.uid))
            .text(`${entry.station.name} (${entry.station.id}) | ${entry.distance}m`)
        );
      });

      select.trigger("change");
      const group = L.featureGroup(markers).addTo(this.layerGroup);
      this.map.fitBounds(group.getBounds());
    },
  };

  window.BGPP = window.BGPP || {};
  window.BGPP.MapUI = MapUI;
})();
