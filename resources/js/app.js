
import { initModal } from './modal.js';
import { initControls } from './controls.js';
import { initNotifications } from './notifications.js';
import { initCalculations, updateDisplayValues, updateProgressBars, checkLimitWarning, updateModeDisplay, updateRelayDisplay } from './calculations.js';

// default setting
window.currentMode = 'otomatis';
window.currentRelayStatus = false;
window.currentSettings = {
    batas_kwh: 5.0,
    tarif_per_kwh: 1444.7,
    daya_terpasang: 1300
};

window.TARIF_PLN = {
    '450': { tarif: 415, kategori: 'R1/TR 450 VA', daya: 450 },
    '900': { tarif: 1352, kategori: 'R1/TR 900 VA', daya: 900 },
    '1300': { tarif: 1444.7, kategori: 'R1/TR 1.300 VA', daya: 1300 },
    '2200': { tarif: 1444.7, kategori: 'R1/TR 2.200 VA', daya: 2200 },
    '3500': { tarif: 1699.53, kategori: 'R2/TR 3.500-5.500 VA', daya: 3500 },
    '5500': { tarif: 1699.53, kategori: 'R2/TR 3.500-5.500 VA', daya: 5500 }
};

// Initialize semua modules
document.addEventListener('DOMContentLoaded', () => {
    initModal();
    initMQTT();
    initControls();
    initNotifications();
    initCalculations();

    fetchRealtimeData();
    
    // Setup polling setiap 3 detik
    setInterval(fetchRealtimeData, 3000);
});

async function fetchRealtimeData() {
    try {
        const response = await fetch('/dashboard/realtime');
        const data = await response.json();
        

        updateDashboard(data);
        
    } catch (error) {
        console.error('Error fetching data:', error);
        window.showNotification?.('Gagal mengambil data dari server', 'danger');
    }
}

function updateDashboard(data) {
    // Update semua elemen UI
    updateDisplayValues(data);
    updateProgressBars(data);
    updateModeDisplay(data.mode);
    updateRelayDisplay(data.relay);
    checkLimitWarning(data.energi_harian, data.batas_kwh, data.mode);
}

// Helper function untuk format angka
window.formatNumber = function(num) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num);
};

// Helper function untuk format mata uang
window.formatCurrency = function(num) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
};

// Simpan function di window agar bisa diakses dari mana saja
window.fetchRealtimeData = fetchRealtimeData;
window.updateDashboard = updateDashboard;