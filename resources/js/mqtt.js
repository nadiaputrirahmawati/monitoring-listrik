// resources/js/modules/mqtt.js

export function initMQTT() {
    // Jika ingin menggunakan MQTT WebSocket untuk realtime
    // connectToMQTT();
}

function connectToMQTT() {
    console.log('📡 Connecting to MQTT...');
    
    // WebSocket connection untuk MQTT over WS
    const ws = new WebSocket('ws://localhost:9001');
    
    ws.onopen = function() {
        console.log('✅ MQTT Connected');
        // Subscribe ke topic
        ws.send(JSON.stringify({
            cmd: 'subscribe',
            topics: ['listrik/monitor']
        }));
    };
    
    ws.onmessage = function(event) {
        const data = JSON.parse(event.data);
        console.log('📨 MQTT Data:', data);
        
        // Update UI dengan data realtime
        updateDashboard(data);
    };
    
    ws.onerror = function(error) {
        console.error('❌ MQTT Error:', error);
    };
    
    ws.onclose = function() {
        console.log('🔌 MQTT Disconnected');
        // Reconnect setelah 5 detik
        setTimeout(connectToMQTT, 5000);
    };
}