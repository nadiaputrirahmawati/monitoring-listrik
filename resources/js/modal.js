

export function initModal() {
    const modal = document.getElementById('settings-modal');
    if (!modal) return;
    
    document.getElementById('btn-settings')?.addEventListener('click', openModal);
    document.getElementById('close-modal')?.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Save batas kWh
    document.getElementById('btn-save-batas')?.addEventListener('click', function() {
        const batas = parseFloat(document.getElementById('batas-kwh-input').value);
        updateBatasKwh(batas);
    });

    document.querySelectorAll('.tarif-option').forEach(option => {
        option.addEventListener('click', function() {
            const daya = this.dataset.daya;
            const tarif = parseFloat(this.dataset.tarif);
            
            document.querySelectorAll('.tarif-option').forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-500/10');
            });
            this.classList.add('border-blue-500', 'bg-blue-500/10');
            
            window.currentSettings.tarif_per_kwh = tarif;
            window.currentSettings.daya_terpasang = parseInt(daya);
            document.getElementById('custom-tarif-input').value = tarif;
            
            showNotification(`Dipilih: ${daya}W - Rp ${formatNumber(tarif)}/kWh`, 'info', 3000);
        });
    });
    document.getElementById('btn-save-custom')?.addEventListener('click', function() {
        const tarif = parseFloat(document.getElementById('custom-tarif-input').value);
        if (!isNaN(tarif) && tarif > 0) {
            document.querySelectorAll('.tarif-option').forEach(opt => {
                opt.classList.remove('border-blue-500', 'bg-blue-500/10');
            });
            updateTarif(tarif);
        }
    });

    document.getElementById('batas-kwh-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const batas = parseFloat(this.value);
            updateBatasKwh(batas);
        }
    });

    document.getElementById('custom-tarif-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const tarif = parseFloat(this.value);
            if (!isNaN(tarif) && tarif > 0) {
                updateTarif(tarif);
            }
        }
    });
}

function openModal() {
    const modal = document.getElementById('settings-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    
    document.getElementById('batas-kwh-input').value = window.currentSettings.batas_kwh;
    document.getElementById('custom-tarif-input').value = window.currentSettings.tarif_per_kwh;
    
    document.querySelectorAll('.tarif-option').forEach(option => {
        option.classList.remove('border-blue-500', 'bg-blue-500/10');
        const daya = option.dataset.daya;
        if (window.TARIF_PLN[daya] && Math.abs(window.TARIF_PLN[daya].tarif - window.currentSettings.tarif_per_kwh) < 1) {
            option.classList.add('border-blue-500', 'bg-blue-500/10');
        }
    });
}

function closeModal() {
    const modal = document.getElementById('settings-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

async function updateBatasKwh(batas) {
    if (isNaN(batas) || batas <= 0) {
        showNotification('Batas kWh harus lebih dari 0!', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/dashboard/updateSetting', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                tarif_per_kwh: window.currentSettings.tarif_per_kwh,
                batas_kwh: batas
            })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            window.currentSettings.batas_kwh = batas;
            showNotification(`Batas kWh berhasil diubah menjadi ${formatNumber(batas)} kWh`, 'success');
            fetchRealtimeData();
            closeModal();
        }
    } catch (error) {
        console.error('Error updating batas kWh:', error);
        showNotification('Gagal memperbarui batas kWh!', 'danger');
    }
}

async function updateTarif(tarif) {
    if (isNaN(tarif) || tarif <= 0) {
        showNotification('Tarif harus lebih dari 0!', 'warning');
        return;
    }
    
    try {
        const response = await fetch('/dashboard/updateSetting', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                tarif_per_kwh: tarif,
                batas_kwh: window.currentSettings.batas_kwh
            })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            window.currentSettings.tarif_per_kwh = tarif;
            showNotification(`Tarif berhasil diubah menjadi Rp ${formatNumber(tarif)}/kWh`, 'success');
            fetchRealtimeData();
            closeModal();
        }
    } catch (error) {
        console.error('Error updating tarif:', error);
        showNotification('Gagal memperbarui tarif!', 'danger');
    }
}