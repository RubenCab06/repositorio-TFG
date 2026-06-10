(function(){
    const btn = document.getElementById('notifyButton');
    function permiso() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'default') Notification.requestPermission();
    }
    if (btn) btn.addEventListener('click', function(){
        if (!('Notification' in window)) { alert('Tu navegador no soporta notificaciones.'); return; }
        Notification.requestPermission().then(p => {
            if (p === 'granted') new Notification('AgroConnect', { body: 'Notificaciones activadas correctamente.' });
        });
    });
    document.addEventListener('DOMContentLoaded', function(){
        const pending = document.querySelectorAll('[data-task-notify="pendiente"]').length;
        const urgent = document.querySelectorAll('[data-task-priority="alta"]').length;
        if (pending > 0 && 'Notification' in window && Notification.permission === 'granted') {
            new Notification('AgroConnect', { body: `Tienes ${pending} tarea(s) pendiente(s). ${urgent ? urgent + ' de prioridad alta.' : ''}` });
        }
    });
    window.agroSolicitarNotificaciones = permiso;
})();
