
let lastNotificationTime = 0;
export function showNotification(message, type = 'info', duration = 5000) {
    const now = Date.now();
    if (now - lastNotificationTime < 500) return;
    
    const notificationId = 'notif-' + Date.now();
    const notification = document.createElements('div');
    notification.id = notificationId;
    notification.className = `animate-slideInRight p-4 rounded-lg shadow-lg border-l-4 ${
        type === 'warning' ? 'bg-yellow-500/20 border-yellow-500' :
        type === 'danger' ? 'bg-red-500/20 border-red-500' :
        type === 'success' ? 'bg-green-500/20 border-green-500' :
        'bg-blue-500/20 border-blue-500'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <i class="fas ${
                type === 'warning' ? 'fa-exclamation-triangle' :
                type === 'danger' ? 'fa-times-circle' :
                type === 'success' ? 'fa-check-circle' : 'fa-info-circle'
            } mt-1 ${
                type === 'warning' ? 'text-yellow-400' :
                type === 'danger' ? 'text-red-400' :
                type === 'success' ? 'text-green-400' : 'text-blue-400'
            }"></i>
            <div class="flex-1">
                <div class="font-medium">${message}</div>
                <div class="text-xs text-slate-400 mt-1">${new Date().toLocaleTimeString('id-ID')}</div>
            </div>
            <button onclick="removeNotification('${notificationId}')" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    const notificationArea = document.getElementById('notification-area');
    if (notificationArea) {
        notificationArea.prepend(notification);
        lastNotificationTime = now;
        
        // Auto remove
        setTimeout(() => {
            removeNotification(notificationId);
        }, duration);
    }
}

export function removeNotification(id) {
    const element = document.getElementById(id);
    if (element) {
        element.classList.add('animate-slideOutRight');
        setTimeout(() => element.remove(), 300);
    }
}

// Tambahkan ke window agar bisa dipanggil dari mana saja
window.showNotification = showNotification;
window.removeNotification = removeNotification;

