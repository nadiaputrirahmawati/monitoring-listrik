
export function initCalculations() {
}

export function updateDisplayValues(data) {
    if (!data) return;

    // Update nilai-nilai di UI
    const elements = {
        tegangan: document.getElementById("tegangan"),
        arus: document.getElementById("arus"),
        watt: document.getElementById("watt"),
        energiHarian: document.getElementById("energi-harian"),
        biayaHarian: document.getElementById("biaya-harian"),
        tarifPerKwh: document.getElementById("tarif-per-kwh"),
        batasKwh: document.getElementById("batas-kwh"),
        biayaBulanan: document.getElementById("biaya-bulanan"),
        proyeksiKwh: document.getElementById("proyeksi-kwh"),
        dayaterpasang: document.getElementById("daya-terpasang"),
    };

    // Update values jika elemen ada
    if (elements.tegangan)
        elements.tegangan.textContent =
            window.formatNumber?.(data.tegangan || 0) || "0";
    if (elements.arus)
        elements.arus.textContent =
            window.formatNumber?.(data.arus || 0) || "0";
    if (elements.watt)
        elements.watt.textContent =
            window.formatNumber?.(data.watt || 0) || "0";
    if (elements.energiHarian)
        elements.energiHarian.textContent =
            window.formatNumber?.(data.energi_harian || 0) || "0";
    if (elements.biayaHarian)
        elements.biayaHarian.textContent =
            window.formatNumber?.(data.biaya_harian || 0) || "0";
    if (elements.tarifPerKwh)
        elements.tarifPerKwh.textContent =
            window.formatNumber?.(data.tarif || 0) || "0";
    if (elements.batasKwh)
        elements.batasKwh.textContent =
            window.formatNumber?.(data.batas_kwh || 0) || "0";
    if (elements.dayaterpasang)
        elements.dayaterpasang.textContent = `${
            window.formatNumber?.(data.daya_terpasang || 0) || "0"
        }W`;

    // Update modal current values
    const currentBatas = document.getElementById("current-batas");
    const currentTarif = document.getElementById("current-tarif");
    if (currentBatas)
        currentBatas.textContent =
            (window.formatNumber?.(data.batas_kwh || 0) || "0") + " kWh";
    if (currentTarif)
        currentTarif.textContent =
            "Rp " + (window.formatNumber?.(data.tarif || 0) || "0") + "/kWh";

    // Perhitungan Estimasi Biaya Bulanan
    const monthlyProjection = (data.energi_harian || 0) * 30;
    const monthlyCost = monthlyProjection * (data.tarif || 0);
    if (elements.biayaBulanan)
        elements.biayaBulanan.textContent =
            window.formatCurrency?.(monthlyCost) || "0";
    if (elements.proyeksiKwh)
        elements.proyeksiKwh.textContent = `${
            window.formatNumber?.(monthlyProjection) || "0"
        } kWh`;

    window.currentMode = data.mode || "otomatis";
    window.currentRelayStatus = data.relay || false;
    window.currentSettings.batas_kwh = data.batas_kwh || 5.0;
    window.currentSettings.tarif_per_kwh = data.tarif || 1444.7;
    window.currentSettings.daya_terpasang = data.daya_terpasang || 1300;
}

export function updateProgressBars(data) {
    const tegangan = parseFloat(data.tegangan || 0);
    const arus = parseFloat(data.arus || 0);
    const watt = parseFloat(data.watt || 0);

    const voltagePercent = Math.min((tegangan / 240) * 100, 100);
    const voltageBar = document.getElementById("voltage-bar");
    if (voltageBar) voltageBar.style.width = `${voltagePercent}%`;

    const maxCurrent = window.currentSettings?.daya_terpasang / 220 || 5.9;
    const currentPercent = Math.min((arus / maxCurrent) * 100, 100);
    const currentBar = document.getElementById("current-bar");
    if (currentBar) currentBar.style.width = `${currentPercent}%`;

    // Update power bar
    const powerPercent = Math.min(
        (watt / (window.currentSettings?.daya_terpasang || 1300)) * 100,
        100
    );
    const powerBar = document.getElementById("power-bar");
    if (powerBar) powerBar.style.width = `${powerPercent}%`;
}

export function checkLimitWarning(energiHarian, batasKwh, mode) {
    const percentage = (energiHarian / batasKwh) * 100;
    // Update progress bar
    const progressBar = document.getElementById("konsumsi-progress");
    const persentaseText = document.getElementById("persentase-text");
    const persentaseKonsumsi = document.getElementById("persentase-konsumsi");
    const statusKonsumsi = document.getElementById("status-konsumsi");

    if (progressBar) {
        progressBar.style.width = `${Math.min(percentage, 100)}%`;

        if (percentage >= 100) {
            progressBar.className =
                "h-full bg-gradient-to-r from-red-500 to-red-600 transition-all duration-500";
            if (statusKonsumsi) {
                statusKonsumsi.textContent = "Melebihi Batas";
                statusKonsumsi.className = "text-red-400";
            }
        } else if (percentage >= 80) {
            progressBar.className =
                "h-full bg-gradient-to-r from-yellow-500 to-orange-500 transition-all duration-500";
            if (statusKonsumsi) {
                statusKonsumsi.textContent = "Mendekati Batas";
                statusKonsumsi.className = "text-yellow-400";
            }
        } else if (percentage >= 50) {
            progressBar.className =
                "h-full bg-gradient-to-r from-green-500 to-yellow-500 transition-all duration-500";
            if (statusKonsumsi) {
                statusKonsumsi.textContent = "Normal";
                statusKonsumsi.className = "text-green-400";
            }
        } else {
            progressBar.className =
                "h-full bg-gradient-to-r from-green-400 to-green-500 transition-all duration-500";
            if (statusKonsumsi) {
                statusKonsumsi.textContent = "Aman";
                statusKonsumsi.className = "text-green-400";
            }
        }
    }

    if (persentaseText)
        persentaseText.textContent = `${Math.round(percentage)}%`;
    if (persentaseKonsumsi)
        persentaseKonsumsi.textContent = `${Math.round(percentage)}%`;

    const now = Date.now();
    
    // Gunakan window.notificationLastTime jika ada, atau buat baru
    if (!window.notificationLastTime) {
        window.notificationLastTime = 0;
    }
    
    // Notifikasi untuk mode manual
    if (mode === "manual") {
        if (percentage >= 100) {
            // Melebihi 100% - notifikasi urgent
            if (now - window.notificationLastTime > 30000) { // 30 detik cooldown
                
                if (window.showNotification) {
                    window.showNotification(
                        `🚨 KONSUMSI MELEBIHI BATAS! ${window.formatNumber?.(energiHarian) || energiHarian} kWh dari ${batasKwh} kWh`,
                        "danger",
                        10000
                    );
                    window.notificationLastTime = now;
                } else {
                    alert(`🚨 KONSUMSI MELEBIHI BATAS! ${energiHarian} kWh dari ${batasKwh} kWh`);
                }
            }
        } else if (percentage >= 90) {
            // Mendekati batas (90-99%)
            if (now - window.notificationLastTime > 30000) {
                if (window.showNotification) {
                    window.showNotification(
                        `⚠️ Konsumsi mendekati batas: ${window.formatNumber?.(energiHarian) || energiHarian} kWh (${Math.round(percentage)}%)`,
                        "warning",
                        8000
                    );
                    window.notificationLastTime = now;
                }
            }
        }
    }
}

export function updateModeDisplay() {
    const modeDisplay = document.getElementById("mode-display");
    if (!modeDisplay) return;

    if (window.currentMode === "otomatis") {
        modeDisplay.innerHTML = `
            <div class="bg-indigo-500/20 p-2 rounded-lg">
                <i class="fas fa-robot text-indigo-400"></i>
            </div>
            <span class="font-medium">Mode Otomatis</span>
        `;
        modeDisplay.className =
            "px-4 py-2 rounded-lg flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30";
    } else {
        modeDisplay.innerHTML = `
            <div class="bg-yellow-500/20 p-2 rounded-lg">
                <i class="fas fa-hand-paper text-yellow-400"></i>
            </div>
            <span class="font-medium">Mode Manual</span>
        `;
        modeDisplay.className =
            "px-4 py-2 rounded-lg flex items-center gap-2 bg-yellow-500/10 border border-yellow-500/30";
    }
}

export function updateRelayDisplay(status) {
    const deviceToggle = document.getElementById("device-toggle");
    const deviceStatus = document.getElementById("device-status");
    const deviceIcon = document.getElementById("device-icon");
    const btnOn = document.getElementById("btn-on");
    const btnOff = document.getElementById("btn-off");

    if (!deviceToggle) return;

    if (status === 1) {
        // Relay ON
        deviceToggle.classList.remove("bg-slate-600");
        deviceToggle.classList.add("bg-blue-500");
        deviceToggle.querySelector("span").classList.remove("translate-x-1");
        deviceToggle.querySelector("span").classList.add("translate-x-11");
        deviceStatus.textContent = "Aktif";
        deviceStatus.className = "text-lg font-semibold text-green-400";
        deviceIcon.innerHTML =
            '<i class="fas fa-plug text-3xl text-green-400"></i>';
        if (btnOn) btnOn.disabled = true;
        if (btnOff) btnOff.disabled = false;
        deviceToggle.disabled = false;
    } else {
        // Relay OFF
        deviceToggle.classList.remove("bg-blue-500");
        deviceToggle.classList.add("bg-slate-600");
        deviceToggle.querySelector("span").classList.remove("translate-x-11");
        deviceToggle.querySelector("span").classList.add("translate-x-1");
        deviceStatus.textContent = "Mati";
        deviceStatus.className = "text-lg font-semibold text-red-400";
        deviceIcon.innerHTML =
            '<i class="fas fa-plug text-3xl text-red-400"></i>';
        if (btnOn) btnOn.disabled = false;
        if (btnOff) btnOff.disabled = true;
        deviceToggle.disabled = false;
    }
}