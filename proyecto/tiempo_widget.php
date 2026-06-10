<section class="weather-card weather-card-advanced" id="weatherCard">
    <div class="weather-header">
        <div>
            <span class="eyebrow">Tiempo en tu zona</span>
            <h3>Tiempo real y previsión agrícola</h3>
            <p id="weatherLocation">Usando la ubicación actual del dispositivo.</p>
        </div>
        <div class="weather-icon" id="weatherIcon">🌤️</div>
    </div>
    <div class="weather-main">
        <strong id="weatherTemp">--°C</strong>
        <span id="weatherDesc">Pulsa para activar la ubicación.</span>
    </div>
    <div class="weather-details">
        <div><span>Humedad</span><strong id="weatherHumidity">--%</strong></div>
        <div><span>Viento</span><strong id="weatherWind">-- km/h</strong></div>
        <div><span>Lluvia actual</span><strong id="weatherRain">-- mm</strong></div>
        <div><span>Lluvia hoy</span><strong id="weatherRainProb">--%</strong></div>
    </div>
    <div class="weather-alerts" id="weatherAlerts"></div>
    <div class="weather-forecast" id="weatherForecast"></div>
    <button type="button" class="btn btn-success btn-sm mt-3" id="weatherButton">Ver tiempo actual</button>
    <small class="weather-note" id="weatherNote">Funciona en localhost o HTTPS. El navegador pedirá permiso para acceder a la ubicación.</small>
</section>
<script src="tiempo.js"></script>
