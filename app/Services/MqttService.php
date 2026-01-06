<?php

namespace App\Services;

use App\Models\DataListrik;
use App\Models\PengaturanSistem;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MqttService
{
    private $mqttHost;
    private $mqttPort;

    public function __construct()
    {
        $this->mqttHost = env('MQTT_HOST', 'localhost');
        $this->mqttPort = env('MQTT_PORT', 1883);
    }

    public function subscribe()
    {
        echo "📡 MQTT SERVICE STARTED\n";

        $mqtt = new MqttClient($this->mqttHost, $this->mqttPort, 'laravel_listrik_' . uniqid());

        $settings = new ConnectionSettings()->setKeepAliveInterval(60)->setUsername(env('MQTT_USERNAME'))->setPassword(env('MQTT_PASSWORD'));

        $mqtt->connect($settings, true);

        // echo "✅ Connected to MQTT Broker\n";
        // echo "📥 Subscribe topic: listrik/monitor\n\n";

        $mqtt->subscribe('listrik/monitor', function ($topic, $message) {
            $this->handleMessage($topic, $message);
        });

        $mqtt->loop(true);
    }

    private function handleMessage($topic, $message)
    {
        // Gunakan Log::info() untuk debugging
        Log::info('MQTT Message Received', [
            'topic' => $topic,
            'message' => $message,
        ]);

        $data = json_decode(trim($message), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON Decode Error', [
                'error' => json_last_error_msg(),
                'message' => $message,
            ]);
            return;
        }

        try {
            $now   = Carbon::now();
            $today = Carbon::today();

            $tegangan = $data['tegangan'] ?? $data['voltage'] ?? 0;
            $arus     = $data['arus'] ?? $data['current'] ?? 0;
            $watt     = $data['watt'] ?? ($tegangan * $arus);

            $pengaturan = PengaturanSistem::latest()->first();
            $tarifPerKwh = $pengaturan?->tarif_per_kwh ?? 1444.7; // fallback PLN

            $record = DataListrik::whereDate('created_at', $today)->first();

            $lastUpdate   = $record?->updated_at ?? $now;
            $deltaSeconds = max($now->diffInSeconds($lastUpdate), 1);

            $satuJam = 60 * 60 * 1000;
            $kwhIncrement = ($watt * $deltaSeconds) / 3600000;

            $totalKwh = ($record->energi_kwh ?? 0) + $kwhIncrement;
            $biaya    = $totalKwh * $tarifPerKwh;

            if ($record) {
                $record->update([
                    'tegangan'   => $tegangan,
                    'arus'       => $arus,
                    'watt'       => $watt,
                    'energi_kwh' => $totalKwh,
                    'biaya'      => $biaya,
                ]);
            } else {
                DataListrik::create([
                    'tegangan'   => $tegangan,
                    'arus'       => $arus,
                    'watt'       => $watt,
                    'energi_kwh' => $kwhIncrement,
                    'biaya'      => $kwhIncrement * $tarifPerKwh,
                ]);
            }

            Log::info("BIAYA: $biaya");
        } catch (\Exception $e) {
            Log::error('Energi calculation error', [
                'error' => $e->getMessage(),
                'data'  => $data ?? []
            ]);
        }



        $this->cekRelay();
    }

    public function cekRelay()
    {
        $pengaturan = PengaturanSistem::first();

        if (!$pengaturan) {
            Log::warning('Pengaturan sistem belum ada');
            return;
        }

        $totalKwhHariIni = DataListrik::whereDate('created_at', now()->toDateString())->sum('energi_kwh');

        Log::info('Total kWh hari ini', ['kwh' => $totalKwhHariIni]);

        if ($pengaturan->mode === 'otomatis') {
            Log::info('Mode: OTOMATIS', ['batas_kwh' => $pengaturan->batas_kwh]);

            if ($totalKwhHariIni >= $pengaturan->batas_kwh) {
                Log::warning('Batas kWh tercapai! Mematikan relay...');
                $this->matikanRelay();
            }
        }
    }

    public function matikanRelay()
    {
        $pengaturan = PengaturanSistem::first();
        $pengaturan->update(['status_perangkat' => false]);

        Log::info('Relay dimatikan (auto mode)');
        $this->publishRelayCommand(false);
    }

    // Method untuk publish perintah relay - PERBAIKAN DI SINI
    public function publishRelayCommand($status)
    {
        try {
            $mqtt = new MqttClient($this->mqttHost, $this->mqttPort, 'laravel_kontrol_' . uniqid());

            $settings = new ConnectionSettings()->setUsername(env('MQTT_USERNAME'))->setPassword(env('MQTT_PASSWORD'));

            $mqtt->connect($settings, true);

            $command = $status ? 'ON' : 'OFF';
            $mqtt->publish('listrik/kontrolrelay', $command);

            Log::info('MQTT Publish Success', [
                'topic' => 'listrik/kontrolrelay',
                'command' => $command,
            ]);

            $mqtt->disconnect();

            return true;
        } catch (\Exception $e) {
            Log::error('MQTT Publish Error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    public function publish($topic, $message)
    {
        try {
            $mqtt = new MqttClient($this->mqttHost, $this->mqttPort, 'laravel_pub_' . uniqid());

            $settings = new ConnectionSettings()->setUsername(env('MQTT_USERNAME'))->setPassword(env('MQTT_PASSWORD'));

            $mqtt->connect($settings, true);
            $mqtt->publish($topic, $message);
            $mqtt->disconnect();

            return true;
        } catch (\Exception $e) {
            Log::error('MQTT Publish Error: ' . $e->getMessage());
            return false;
        }
    }
}
