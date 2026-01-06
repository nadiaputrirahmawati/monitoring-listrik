<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pemantauan Energi IoT</title>
    
    <!-- CSRF Token untuk Laravel -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen text-white">
    <!-- Header -->
    <header class="bg-slate-800/50 backdrop-blur-sm border-b border-slate-700 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-500 p-3 rounded-lg">
                        <i class="fas fa-bolt text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Sistem Pemantauan Daya dan Energi Listrik Cerdas</h1>
                        <p class="text-sm text-slate-400">Berbasis ESP32 dan MQTT untuk Efisiensi Energi</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Mode Display -->
                    <div id="mode-display" class="px-4 py-2 rounded-lg flex items-center gap-2">
                        <!-- Will be filled by JavaScript -->
                    </div>
                    <!-- Tombol Pengaturan -->
                    <button id="btn-settings" class="bg-slate-700 hover:bg-slate-600 p-2 rounded-lg transition-all">
                        <i class="fas fa-cog text-xl"></i>
                    </button>
                    <div class="bg-green-500/20 px-3 py-1 rounded-full flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-green-400">Terhubung</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Modal Pengaturan -->

    @include('Setting')

    <!-- Notifikasi Area -->
    <div id="notification-area" class="fixed top-24 right-4 z-40 space-y-3 max-w-sm">
        <!-- Notifikasi akan muncul di sini -->
    </div>

    <main class="container mx-auto px-4 py-6">
        <!-- Real-time Monitoring Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Tegangan Card -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700 hover:border-blue-500 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-500/20 p-3 rounded-lg">
                        <i class="fas fa-wave-square text-2xl text-yellow-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Real-time</span>
                </div>
                <h3 class="text-slate-400 text-sm font-medium mb-2">Tegangan</h3>
                <div class="flex items-baseline gap-2">
                    <span id="tegangan" class="text-4xl font-bold text-yellow-400">0</span>
                    <span class="text-lg text-slate-400">V</span>
                </div>
                <div class="mt-4 h-1 bg-slate-700 rounded-full overflow-hidden">
                    <div id="voltage-bar" class="h-full bg-gradient-to-r from-yellow-500 to-yellow-400 w-0"></div>
                </div>
            </div>

            <!-- Arus Card -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700 hover:border-blue-500 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-cyan-500/20 p-3 rounded-lg">
                        <i class="fas fa-tachometer-alt text-2xl text-cyan-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Real-time</span>
                </div>
                <h3 class="text-slate-400 text-sm font-medium mb-2">Arus</h3>
                <div class="flex items-baseline gap-2">
                    <span id="arus" class="text-4xl font-bold text-cyan-400">0</span>
                    <span class="text-lg text-slate-400">A</span>
                </div>
                <div class="mt-4 h-1 bg-slate-700 rounded-full overflow-hidden">
                    <div id="current-bar" class="h-full bg-gradient-to-r from-cyan-500 to-cyan-400 w-0"></div>
                </div>
            </div>

            <!-- Daya Card -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700 hover:border-blue-500 transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <i class="fas fa-plug text-2xl text-purple-400"></i>
                    </div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider">Real-time</span>
                </div>
                <h3 class="text-slate-400 text-sm font-medium mb-2">Daya (Watt)</h3>
                <div class="flex items-baseline gap-2">
                    <span id="watt" class="text-4xl font-bold text-purple-400">0</span>
                    <span class="text-lg text-slate-400">W</span>
                </div>
                <div class="mt-4 h-1 bg-slate-700 rounded-full overflow-hidden">
                    <div id="power-bar" class="h-full bg-gradient-to-r from-purple-500 to-purple-400 w-0"></div>
                </div>
            </div>
        </div>

        <!-- Energy & Cost Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Total Energi -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <i class="fas fa-chart-line text-2xl text-green-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Total Konsumsi Energi</h3>
                        <p class="text-sm text-slate-400">Akumulasi penggunaan listrik hari ini</p>
                    </div>
                </div>
                <div class="bg-slate-900/50 rounded-lg p-6 mb-4">
                    <div class="flex items-baseline gap-3">
                        <span id="energi-harian" class="text-5xl font-bold text-green-400">0</span>
                        <span class="text-2xl text-slate-400">kWh</span>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span id="batas-kwh-info" class="text-yellow-400">
                            Batas: <span id="batas-kwh" class="cursor-pointer hover:text-yellow-300">0</span> kWh
                        </span>
                        <span id="persentase-konsumsi" class="text-white font-medium">0%</span>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-slate-400 mb-2">
                        <span>0%</span>
                        <span id="persentase-text">0%</span>
                        <span>100%</span>
                    </div>
                    <div class="h-3 bg-slate-700 rounded-full overflow-hidden">
                        <div id="konsumsi-progress" class="h-full bg-gradient-to-r from-green-500 to-yellow-500 transition-all duration-500" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between mt-2 text-xs text-slate-500">
                        <span>Aman</span>
                        <span id="status-konsumsi" class="text-green-400">Normal</span>
                        <span>Bahaya</span>
                    </div>
                </div>
            </div>

            <!-- Biaya Listrik -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-orange-500/20 p-3 rounded-lg">
                        <i class="fas fa-wallet text-2xl text-orange-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Biaya Listrik</h3>
                        <p class="text-sm text-slate-400">Tarif: Rp <span id="tarif-per-kwh" class="cursor-pointer hover:text-orange-300">0</span> per kWh</p>
                    </div>
                </div>
                
                <!-- Biaya Hari Ini -->
                <div class="bg-slate-900/50 rounded-lg p-5 mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400">Biaya Hari Ini</span>
                        <i class="fas fa-calendar-day text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-lg text-slate-400">Rp</span>
                        <span id="biaya-harian" class="text-3xl font-bold text-orange-400">0</span>
                    </div>
                </div>

                <!-- Estimasi Bulanan -->
                <div class="bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-lg p-5 border border-orange-500/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400">Estimasi Bulan Ini</span>
                        <i class="fas fa-calendar-alt text-slate-500"></i>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-lg text-slate-400">Rp</span>
                        <span id="biaya-bulanan" class="text-3xl font-bold text-orange-400">0</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-orange-500/30 text-xs text-slate-400">
                        <div class="flex justify-between">
                            <span>Proyeksi berdasarkan 30 hari</span>
                            <span id="proyeksi-kwh" class="text-orange-400">0 kWh</span>
                        </div>
                    </div>
                </div>

                <!-- Info Daya Terpasang -->
                <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <div class="flex items-center gap-2 text-xs text-blue-300">
                        <i class="fas fa-plug"></i>
                        <span>Daya terpasang: <span id="daya-terpasang" class="font-bold">0W</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Mode Control -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-indigo-500/20 p-3 rounded-lg">
                        <i class="fas fa-cogs text-2xl text-indigo-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Mode Kontrol</h3>
                        <p class="text-sm text-slate-400">Atur sistem bekerja secara manual atau otomatis</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 rounded-lg p-6 border border-slate-700">
                    <div class="grid grid-cols-2 gap-4">
                        <button id="btn-auto" class="py-4 rounded-lg transition-all flex items-center justify-center gap-3 bg-indigo-500 hover:bg-indigo-600">
                            <i class="fas fa-robot text-xl"></i>
                            <div class="text-left">
                                <div class="font-semibold">Mode Otomatis</div>
                                <div class="text-xs opacity-80">Sistem mengontrol secara otomatis</div>
                            </div>
                        </button>
                        <button id="btn-manual" class="py-4 rounded-lg transition-all flex items-center justify-center gap-3 bg-slate-700 hover:bg-slate-600">
                            <i class="fas fa-hand-paper text-xl"></i>
                            <div class="text-left">
                                <div class="font-semibold">Mode Manual</div>
                                <div class="text-xs opacity-80">Kontrol perangkat manual</div>
                            </div>
                        </button>
                    </div>
                    
                    <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-info-circle text-blue-400"></i>
                            <div class="text-sm">
                                <span id="mode-info" class="font-medium">Mode Otomatis:</span>
                                <span id="mode-description" class="text-slate-300">Sistem akan mematikan perangkat secara otomatis ketika mencapai batas kWh harian</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Control -->
            <div class="bg-slate-800/50 backdrop-blur-sm rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <i class="fas fa-power-off text-2xl text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Kontrol Perangkat</h3>
                        <p class="text-sm text-slate-400" id="device-control-info">Nyalakan atau matikan perangkat secara manual</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 rounded-lg p-6 border border-slate-700">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div id="device-icon" class="bg-blue-500/20 p-4 rounded-xl">
                                <i class="fas fa-plug text-3xl text-blue-400"></i>
                            </div>
                            <div>
                                <h4 class="text-xl font-semibold">Perangkat Utama</h4>
                                <p class="text-sm text-slate-400">Relay ESP32</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-800/50 rounded-lg p-5 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm text-slate-400 block mb-1">Status Perangkat</span>
                                <span id="device-status" class="text-lg font-semibold">-</span>
                            </div>
                            <button id="device-toggle" class="relative inline-flex h-10 w-20 items-center rounded-full bg-slate-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="inline-block h-8 w-8 transform rounded-full bg-white shadow-lg transition-transform translate-x-1"></span>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button id="btn-on" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-power-off"></i>
                            <span>NYALAKAN</span>
                        </button>
                        <button id="btn-off" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-power-off"></i>
                            <span>MATIKAN</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-sm text-slate-500">
            <p>Dashboard IoT - Sistem Pemantauan Energi | Data diperbarui setiap 3 detik</p>
        </div>
    </main>
</body>
</html>