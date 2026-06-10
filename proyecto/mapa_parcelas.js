(function(){
    const el = document.getElementById('parcelMap');
    if (!el || !window.L) return;
    const parcelas = Array.isArray(window.AGRO_PARCELAS) ? window.AGRO_PARCELAS : [];
    const map = L.map(el, { scrollWheelZoom: true });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    const group = L.featureGroup().addTo(map);
    function distanciaKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2-lat1) * Math.PI / 180;
        const dLon = (lon2-lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }
    async function cargarTiempo(lat, lon) {
        try {
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat.toFixed(4)}&longitude=${lon.toFixed(4)}&current=temperature_2m,relative_humidity_2m,precipitation,wind_speed_10m,weather_code&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max&forecast_days=3&timezone=auto`;
            const r = await fetch(url); if (!r.ok) throw new Error('weather');
            const d = await r.json();
            const c = d.current || {};
            const lluvia = d.daily?.precipitation_probability_max?.[0] ?? '-';
            return `<div class="map-weather"><strong>${Math.round(c.temperature_2m ?? 0)} ºC</strong><span>Humedad ${Math.round(c.relative_humidity_2m ?? 0)}% · Viento ${Math.round(c.wind_speed_10m ?? 0)} km/h · Lluvia ${lluvia}%</span></div>`;
        } catch(e) { return '<div class="map-weather text-muted">Tiempo no disponible</div>'; }
    }
    if (!parcelas.length) {
        map.setView([37.4, -5.9], 8);
        return;
    }
    parcelas.forEach(p => {
        const lat = parseFloat(p.latitud), lon = parseFloat(p.longitud);
        if (Number.isNaN(lat) || Number.isNaN(lon)) return;
        const marker = L.marker([lat, lon]).addTo(group);
        const cultivos = p.cultivos || 'Sin cultivos registrados';
        marker.bindPopup(`<div class="map-popup"><strong>${p.nombre}</strong><p>${p.ubicacion || ''}</p><p><b>Empresa:</b> ${p.empresa || ''}</p><p><b>Cultivos:</b> ${cultivos}</p><p><b>Estado:</b> ${p.estado || ''}</p><div class="popup-weather">Cargando tiempo...</div></div>`);
        marker.on('popupopen', async (ev) => {
            const box = ev.popup.getElement().querySelector('.popup-weather');
            if (box) box.innerHTML = await cargarTiempo(lat, lon);
        });
    });
    map.fitBounds(group.getBounds().pad(0.18));
    const note = document.getElementById('mapUserNote');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const uLat = pos.coords.latitude, uLon = pos.coords.longitude;
            const userMarker = L.circleMarker([uLat, uLon], { radius: 8 }).addTo(map).bindPopup('Tu ubicación aproximada');
            parcelas.forEach(p => {
                const lat = parseFloat(p.latitud), lon = parseFloat(p.longitud);
                if (!Number.isNaN(lat) && !Number.isNaN(lon)) p.distancia = distanciaKm(uLat, uLon, lat, lon);
            });
            if (note) {
                const cercana = parcelas.filter(p => typeof p.distancia === 'number').sort((a,b)=>a.distancia-b.distancia)[0];
                note.textContent = cercana ? `Parcela más cercana: ${cercana.nombre}, a ${cercana.distancia.toFixed(1)} km aprox.` : 'Ubicación detectada.';
            }
        }, () => { if(note) note.textContent = 'Ubicación no activada. El mapa funciona igualmente.'; });
    }
})();
