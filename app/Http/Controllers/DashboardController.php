<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\DataListrik;
use Illuminate\Http\Request;
use App\Models\PengaturanSistem;
use App\Http\Controllers\Controller;
use App\Services\MqttService;

class DashboardController extends Controller
{
    // halaman utama
    public function index()
    {
        return view('dashboard');
    }

    // API realtime (dipanggil JS)
    public function realtime()
    {
        $terakhir = DataListrik::latest()->first();

        $hariIni = DataListrik::whereDate('created_at', Carbon::today())
                    ->sum('energi_kwh');

        $setting = PengaturanSistem::first();

        // Jika belum ada setting, buat default
        if (!$setting) {
            $setting = PengaturanSistem::create([
                'mode' => 'otomatis',
                'batas_kwh' => 5.0,
                'tarif_per_kwh' => 1444.7,
                'status_perangkat' => false
            ]);
        }

        $biayaHariIni = $hariIni * $setting->tarif_per_kwh;

        // Cek notifikasi melebihi batas
        $notifikasi = null;
        if ($setting->mode === 'manual' && $hariIni >= $setting->batas_kwh) {
            $notifikasi = [
                'type' => 'danger',
                'message' => 'Konsumsi melebihi batas harian!',
                'detail' => "Konsumsi: {$hariIni} kWh | Batas: {$setting->batas_kwh} kWh"
            ];
        } elseif ($setting->mode === 'manual' && $hariIni >= ($setting->batas_kwh * 0.9)) {
            $notifikasi = [
                'type' => 'warning',
                'message' => 'Konsumsi mendekati batas!',
                'detail' => "Konsumsi: {$hariIni} kWh | Batas: {$setting->batas_kwh} kWh"
            ];
        }

        return response()->json([
            'tegangan' => $terakhir->tegangan ?? 0,
            'arus' => $terakhir->arus ?? 0,
            'watt' => $terakhir->watt ?? 0,
            'energi_harian' => $hariIni,
            'biaya_harian' => $biayaHariIni,
            'mode' => $setting->mode,
            'relay' => $setting->status_perangkat ? 1 : 0,
            'tarif' => $setting->tarif_per_kwh,
            'batas_kwh' => $setting->batas_kwh,
            'notifikasi' => $notifikasi
        ]);
    }

    // ubah mode otomatis / manual
    public function ubahMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:otomatis,manual'
        ]);

        $setting = PengaturanSistem::first();
        $setting->mode = $request->mode;
        $setting->save();
        
        return response()->json(['status'=>'ok']);
    }

    // Kontrol relay ON/OFF
    public function controlRelay(Request $request)
    {
        $request->validate([
            'action' => 'required|in:on,off'
        ]);

        $setting = PengaturanSistem::first();
        
        // Pastikan hanya bisa kontrol jika mode manual
        if ($setting->mode !== 'manual') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya bisa kontrol manual saat mode manual'
            ], 400);
        }
        
        $status = $request->action === 'on' ? true : false;
        $setting->status_perangkat = $status;
        $setting->save();
        
        // Kirim perintah ke MQTT
        $mqttService = new MqttService();
        $success = $mqttService->publishRelayCommand($status);
        
        return response()->json([
            'status' => 'ok',
            'relay_status' => $status,
            'mqtt_sent' => $success
        ]);
    }

    // update tarif & batas kwh
    public function updateSetting(Request $request)
    {
        $request->validate([
            'tarif_per_kwh' => 'required|numeric|min:0',
            'batas_kwh' => 'required|numeric|min:0'
        ]);

        $setting = PengaturanSistem::first();
        $setting->update([
            'tarif_per_kwh' => $request->tarif_per_kwh,
            'batas_kwh' => $request->batas_kwh
        ]);
        
        return response()->json(['status'=>'ok']);
    }
}