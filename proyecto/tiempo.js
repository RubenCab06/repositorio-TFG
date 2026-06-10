(function () {
    const card = document.getElementById('weatherCard');
    if (!card) return;
    const els = {
        location: document.getElementById('weatherLocation'), icon: document.getElementById('weatherIcon'), temp: document.getElementById('weatherTemp'), desc: document.getElementById('weatherDesc'), humidity: document.getElementById('weatherHumidity'), wind: document.getElementById('weatherWind'), rain: document.getElementById('weatherRain'), rainProb: document.getElementById('weatherRainProb'), alerts: document.getElementById('weatherAlerts'), forecast: document.getElementById('weatherForecast'), button: document.getElementById('weatherButton'), note: document.getElementById('weatherNote')
    };
    const weatherCodes = {0:['☀️','Cielo despejado'],1:['🌤️','Mayormente despejado'],2:['⛅','Parcialmente nuboso'],3:['☁️','Nublado'],45:['🌫️','Niebla'],48:['🌫️','Niebla con escarcha'],51:['🌦️','Llovizna débil'],53:['🌦️','Llovizna moderada'],55:['🌧️','Llovizna intensa'],61:['🌧️','Lluvia débil'],63:['🌧️','Lluvia moderada'],65:['🌧️','Lluvia fuerte'],71:['🌨️','Nieve débil'],73:['🌨️','Nieve moderada'],75:['❄️','Nieve fuerte'],80:['🌦️','Chubascos débiles'],81:['🌧️','Chubascos moderados'],82:['⛈️','Chubascos fuertes'],95:['⛈️','Tormenta'],96:['⛈️','Tormenta con granizo'],99:['⛈️','Tormenta fuerte con granizo']};
    function setLoading(){ els.button.disabled=true; els.button.textContent='Cargando tiempo...'; els.desc.textContent='Obteniendo ubicación y previsión meteorológica.'; els.note.textContent='Acepta el permiso de ubicación para ver datos de tu zona.'; }
    function setError(message){ els.button.disabled=false; els.button.textContent='Reintentar'; els.desc.textContent=message; els.note.textContent='En XAMPP entra desde http://localhost/proyecto/ para que el navegador permita ubicación.'; }
    function alertas(daily){
        const max = daily.temperature_2m_max?.[0], min = daily.temperature_2m_min?.[0], rain = daily.precipitation_probability_max?.[0];
        const a=[];
        if (min !== undefined && min <= 2) a.push(['❄️','Riesgo de heladas','Protege cultivos sensibles y revisa riego.']);
        if (max !== undefined && max >= 35) a.push(['🔥','Calor extremo','Evita trabajos en horas centrales y revisa humedad del suelo.']);
        if (rain !== undefined && rain >= 70) a.push(['🌧️','Lluvia probable','Planifica tratamientos y maquinaria teniendo en cuenta el terreno.']);
        return a;
    }
    function renderForecast(daily){
        const dias = daily.time || [];
        els.forecast.innerHTML = dias.slice(0,7).map((dia,i)=>{
            const fecha = new Date(dia+'T12:00:00').toLocaleDateString('es-ES',{weekday:'short',day:'numeric'});
            const max = Math.round(daily.temperature_2m_max?.[i] ?? 0);
            const min = Math.round(daily.temperature_2m_min?.[i] ?? 0);
            const rain = daily.precipitation_probability_max?.[i] ?? 0;
            return `<div><strong>${fecha}</strong><span>${min}° / ${max}°</span><small>🌧️ ${rain}%</small></div>`;
        }).join('');
    }
    async function loadWeather(lat, lon){
        const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat.toFixed(4)}&longitude=${lon.toFixed(4)}&current=temperature_2m,relative_humidity_2m,precipitation,weather_code,wind_speed_10m&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max&forecast_days=7&timezone=auto`;
        const response = await fetch(url); if(!response.ok) throw new Error('No se pudo consultar el tiempo');
        const data = await response.json(); const current = data.current || {}; const daily = data.daily || {};
        const codeInfo = weatherCodes[current.weather_code] || ['🌤️','Tiempo actual'];
        els.icon.textContent=codeInfo[0]; els.temp.textContent=`${Math.round(current.temperature_2m)}°C`; els.desc.textContent=codeInfo[1];
        els.humidity.textContent=`${Math.round(current.relative_humidity_2m)}%`; els.wind.textContent=`${Math.round(current.wind_speed_10m)} km/h`; els.rain.textContent=`${current.precipitation ?? 0} mm`; els.rainProb.textContent=`${daily.precipitation_probability_max?.[0] ?? '--'}%`;
        els.location.textContent=`Coordenadas: ${lat.toFixed(3)}, ${lon.toFixed(3)}`; els.note.textContent=`Actualizado: ${new Date(current.time).toLocaleString('es-ES')}`;
        const a = alertas(daily); els.alerts.innerHTML = a.length ? a.map(x=>`<div><span>${x[0]}</span><strong>${x[1]}</strong><small>${x[2]}</small></div>`).join('') : '<div><span>✅</span><strong>Sin alertas importantes</strong><small>Condiciones normales para la zona.</small></div>';
        renderForecast(daily); els.button.disabled=false; els.button.textContent='Actualizar tiempo';
        if (a.length && 'Notification' in window && Notification.permission === 'granted') new Notification('Alerta AgroConnect', { body: a.map(x=>x[1]).join(' · ') });
    }
    function requestWeather(){ if(!navigator.geolocation){ setError('Tu navegador no permite obtener la ubicación.'); return; } setLoading(); navigator.geolocation.getCurrentPosition(async position=>{ try{ await loadWeather(position.coords.latitude, position.coords.longitude); }catch(e){ setError('No se pudieron cargar los datos del tiempo.'); } }, ()=>setError('Permiso de ubicación denegado. Actívalo para ver el tiempo real de tu zona.'), {enableHighAccuracy:false,timeout:10000,maximumAge:600000}); }
    els.button.addEventListener('click', requestWeather); requestWeather();
})();
