    <div id="settings-modal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center
            bg-transparent backdrop-blur-sm p-4">
        <div class="bg-slate-800 rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header Modal -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-500/20 p-2 rounded-lg">
                            <i class="fas fa-cog text-blue-400"></i>
                        </div>
                        <h2 class="text-xl font-bold">Pengaturan Sistem</h2>
                    </div>
                    <button id="close-modal" class="text-slate-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Form Pengaturan -->
                <div class="space-y-6">
                    <!-- Batas kWh Harian -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Batas Konsumsi Harian (kWh)</label>
                        <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-slate-400">Batas saat ini:</span>
                                <span id="current-batas" class="text-green-400 font-bold">0 kWh</span>
                            </div>
                            <div class="flex gap-2">
                                <input type="number" id="batas-kwh-input" step="0.1" min="0" 
                                       class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                                <button id="btn-save-batas" class="bg-blue-500 hover:bg-blue-600 px-4 py-3 rounded-lg font-medium">
                                    Simpan
                                </button>
                            </div>
                            <div class="mt-3 text-xs text-slate-400">
                                <i class="fas fa-info-circle mr-1"></i>
                                Sistem akan memberikan notifikasi ketika mendekati batas
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Daya & Tarif -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Pilih Daya Terpasang & Tarif</label>
                        <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-slate-400">Tarif saat ini:</span>
                                <span id="current-tarif" class="text-orange-400 font-bold">Rp 0/kWh</span>
                            </div>
                            
                            <!-- Pilihan Daya -->
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="450" data-tarif="415">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">450W</div>
                                        <div class="text-sm text-slate-300">Rp 415/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R1/TR 450 VA</div>
                                    </div>
                                </div>
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="900" data-tarif="1352">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">900W</div>
                                        <div class="text-sm text-slate-300">Rp 1.352/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R1/TR 900 VA</div>
                                    </div>
                                </div>
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="1300" data-tarif="1444.7">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">1.300W</div>
                                        <div class="text-sm text-slate-300">Rp 1.444,7/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R1/TR 1.300 VA</div>
                                    </div>
                                </div>
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="2200" data-tarif="1444.7">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">2.200W</div>
                                        <div class="text-sm text-slate-300">Rp 1.444,7/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R1/TR 2.200 VA</div>
                                    </div>
                                </div>
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="3500" data-tarif="1699.53">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">3.500W</div>
                                        <div class="text-sm text-slate-300">Rp 1.699,53/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R2/TR 3.500-5.500 VA</div>
                                    </div>
                                </div>
                                <div class="tarif-option bg-slate-900 p-4 rounded-lg border border-slate-700 cursor-pointer hover:border-blue-500 transition-all" data-daya="5500" data-tarif="1699.53">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-white">5.500W</div>
                                        <div class="text-sm text-slate-300">Rp 1.699,53/kWh</div>
                                        <div class="text-xs text-slate-400 mt-1">Golongan R2/TR 3.500-5.500 VA</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarif Custom -->
                            <div class="mt-4 pt-4 border-t border-slate-700">
                                <div class="text-sm font-medium mb-3">Atau masukkan tarif custom:</div>
                                <div class="flex gap-2">
                                    <input type="number" id="custom-tarif-input" step="1" min="0" placeholder="Tarif per kWh"
                                           class="flex-1 bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500">
                                    <button id="btn-save-custom" class="bg-purple-500 hover:bg-purple-600 px-4 py-3 rounded-lg font-medium">
                                        Custom
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Standar PLN -->
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                            <div class="text-sm">
                                <div class="font-medium text-blue-300 mb-1">Standar Tarif PLN (Januari 2025):</div>
                                <ul class="text-slate-300 space-y-1">
                                    <li>• 450 VA: Rp 415/kWh</li>
                                    <li>• 900 VA: Rp 1.352/kWh</li>
                                    <li>• 1.300 VA: Rp 1.444,7/kWh</li>
                                    <li>• 2.200 VA: Rp 1.444,7/kWh</li>
                                    <li>• 3.500-5.500 VA: Rp 1.699,53/kWh</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>