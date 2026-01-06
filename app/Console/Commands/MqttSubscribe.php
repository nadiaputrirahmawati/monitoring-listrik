<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;

class MqttSubscribe extends Command
{
    protected $signature = 'app:subscribe';
    protected $description = 'Subscribe to MQTT topics';

    public function handle()
    {
        $this->info('Menjalankan MQTT Subscribe...');
        $mqttService = new MqttService();
        $mqttService->subscribe();
    }
}