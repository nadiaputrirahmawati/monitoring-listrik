// resources/js/modules/controls.js

// Import function yang diperlukan
import { updateModeDisplay, updateRelayDisplay } from './calculations.js';

export function initControls() {
    // Control events
    document.getElementById('btn-auto')?.addEventListener('click', () => changeMode('otomatis'));
    document.getElementById('btn-manual')?.addEventListener('click', () => changeMode('manual'));
    
    document.getElementById('device-toggle')?.addEventListener('click', () => {
        if (window.currentMode === 'manual') {
            const action = window.currentRelayStatus ? 'off' : 'on';
            controlRelay(action);
        }
    });
    
    document.getElementById('btn-on')?.addEventListener('click', () => {
        if (window.currentMode === 'manual') controlRelay('on');
    });
    
    document.getElementById('btn-off')?.addEventListener('click', () => {
        if (window.currentMode === 'manual') controlRelay('off');
    });
}

async function changeMode(mode) {
    try {
        const response = await fetch('/dashboard/ubahMode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ mode: mode })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            window.currentMode = mode;
            updateModeDisplay();
            window.fetchRealtimeData?.();
            window.showNotification?.(`Mode berhasil diubah ke ${mode === 'otomatis' ? 'Otomatis' : 'Manual'}`, 'success');
        }
    } catch (error) {
        console.error('Error changing mode:', error);
        window.showNotification?.('Gagal mengubah mode!', 'danger');
    }
}

async function controlRelay(action) {
    try {
        const response = await fetch('/dashboard/controlRelay', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ action: action })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            // Update UI immediately
            if (action === 'on') {
                updateRelayDisplay(1);
            } else {
                updateRelayDisplay(0);
            }
            
            window.showNotification?.(`Perangkat berhasil ${action === 'on' ? 'dinyalakan' : 'dimatikan'}`, 'success');
            
            // Refresh data dari server
            setTimeout(() => {
                window.fetchRealtimeData?.();
            }, 500);
        }
    } catch (error) {
        console.error('Error controlling relay:', error);
        window.showNotification?.('Gagal mengontrol relay!', 'danger');
    }
}

// Simpan function di window
window.changeMode = changeMode;
window.controlRelay = controlRelay;