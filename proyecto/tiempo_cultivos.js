(function () {
    const cards = document.querySelectorAll('.crop-weather');
    if (!cards.length) return;
    const weatherCodes = {0:['☀️','Despejado'],1:['🌤️','Mayormente despejado'],2:['⛅','Parcialmente nuboso'],3:['☁️','Nublado'],45:['🌫️','Niebla'],48:['🌫️','Niebla con escarcha'],51:['🌦️','Llovizna débil'],53:['🌦️','Llovizna moderada'],55:['🌧️','Llovizna intensa'],61:['🌧️','Lluvia débil'],63:['🌧️','Lluvia moderada'],65:['🌧️','Lluvia fuerte'],71:['🌨️','Nieve débil'],73:['🌨️','Nieve moderada'],75:['❄️','Nieve fuerte'],80:['🌦️','Chubascos débiles'],81:['🌧️','Chubascos moderados'],82:['⛈️','Chubascos fuertes'],95:['⛈️','Tormenta'],96:['⛈️','Tormenta con granizo'],99:['⛈️','Tormenta fuerte con granizo']};
    let userPos = null;
    function distanciaKm(lat1, lon1, lat2, lon2) { const R=6371,dLat=(lat2-lat1)*Math.PI/180,dLon=(lon2-lon1)*Math.PI/180; const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2; return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)); }
    function alertas(daily){ const max=daily.temperature_2m_max?.[0], min=daily.temperature_2m_min?.[0], rain=daily.precipitation_probability_max?.[0]; const a=[]; if(min!==undefined&&min<=2)a.push('❄️ Helada'); if(max!==undefined&&max>=35)a.push('🔥 Calor'); if(rain!==undefined&&rain>=70)a.push('🌧️ Lluvia'); return a; }
    async function cargarTiempo(card) {
        const lat = parseFloat(card.dataset.lat), lon = parseFloat(card.dataset.lon), place = card.dataset.place || 'Parcela';
        const icon = card.querySelector('.crop-weather-icon'), title = card.querySelector('strong'), detail = card.querySelector('small');
        if (Number.isNaN(lat) || Number.isNaN(lon)) { title.textContent='Sin coordenadas'; detail.textContent='Edita la parcela'; return; }
        try {
            const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat.toFixed(4)}&longitude=${lon.toFixed(4)}&current=temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max&forecast_days=3&timezone=auto`;
            const response = await fetch(url); if(!response.ok) throw new Error('Error meteorológico');
            const data = await response.json(); const current=data.current||{}, daily=data.daily||{};
            const codeInfo = weatherCodes[current.weather_code] || ['🌤️','Tiempo actual'];
            const lluvia = daily.precipitation_probability_max?.[0] ?? '-'; const avisos = alertas(daily);
            const distancia = userPos ? ` · ${distanciaKm(userPos.lat,userPos.lon,lat,lon).toFixed(1)} km desde ti` : '';
            icon.textContent = codeInfo[0]; title.textContent = `${Math.round(current.temperature_2m)}°C · ${codeInfo[1]}`;
            detail.innerHTML = `${place}${distancia}<br>Humedad ${Math.round(current.relative_humidity_2m)}% · Viento ${Math.round(current.wind_speed_10m)} km/h · Lluvia ${current.precipitation ?? 0} mm · Prob. lluvia ${lluvia}%${avisos.length ? '<br><b>Alertas:</b> '+avisos.join(' · ') : ''}`;
        } catch(e) { icon.textContent='⚠️'; title.textContent='No disponible'; detail.textContent='No se pudo cargar el tiempo de esta parcela'; }
    }
    function cargarTodas(){ cards.forEach(cargarTiempo); }
    if (navigator.geolocation) navigator.geolocation.getCurrentPosition(p=>{ userPos={lat:p.coords.latitude,lon:p.coords.longitude}; cargarTodas(); }, cargarTodas, {enableHighAccuracy:false,timeout:5000,maximumAge:600000}); else cargarTodas();
})();
