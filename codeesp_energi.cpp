#include <Arduino.h>
#include <WiFi.h>
#include <PubSubClient.h>

// Wifi
const char* ssid     = "NADIA";
const char* password = "12345678";

// Mqtt Broker
const char* mqtt_server   = "broker.rezweb.my.id";
const int   mqtt_port     = 7964;

const char* topic_pub = "listrik/monitor";
const char* topic_sub = "listrik/kontrolrelay";

WiFiClient espClient;
PubSubClient client(espClient);

#define ZMPT_PIN   34
#define ZMCT_PIN   35
#define RELAY_PIN  27

#define ADC_RESOLUTION 4095.0
#define ADC_VREF 3.3

// Kalibrasi
float voltageCalibration = 714.0;
float currentCalibrationLow  = 0.45;
float currentCalibrationHigh = 0.55;

#define CURRENT_DEADZONE 0.03
#define POWER_MIN        5.0

unsigned long lastRead  = 0;
unsigned long lastPrint = 0;
const unsigned long intervalRead  = 100;
const unsigned long intervalPrint = 3000;

float IrmsPrev = 0;
float alpha = 0.2;

float readRMS(int pin, int samples);
float dynamicCalibration(float IrmsRaw);
void reconnectMQTT();
void callback(char* topic, byte* payload, unsigned int length);

void setup() {
  Serial.begin(115200);
  analogReadResolution(12);

  Serial.print("Subscribe topic: ");
  Serial.println(topic_sub);

  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, HIGH);   // NC terhubung → BEBAN ON (default)

  Serial.print("Connecting WiFi");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);

  Serial.println("ESP32 MONITOR + RELAY NC READY");
}

void loop() {
  if (!client.connected()) reconnectMQTT();
  client.loop();

  unsigned long now = millis();

  if (now - lastRead >= intervalRead) {
    lastRead = now;

    /* ===== TEGANGAN ===== */
    float Vrms = readRMS(ZMPT_PIN, 500) * voltageCalibration;

    /* ===== ARUS ===== */
    float IrmsRaw = readRMS(ZMCT_PIN, 500);
    float IrmsCal = dynamicCalibration(IrmsRaw);
    float Irms = alpha * IrmsCal + (1 - alpha) * IrmsPrev;
    IrmsPrev = Irms;
    if (Irms < CURRENT_DEADZONE) Irms = 0;

    float Power = Vrms * Irms;
    if (Power < POWER_MIN) Power = 0;

    if (now - lastPrint >= intervalPrint) {
      lastPrint = now;

      Serial.print("V: "); Serial.print(Vrms, 1);
      Serial.print(" V | I: "); Serial.print(Irms, 3);
      Serial.print(" A | P: "); Serial.print(Power, 1);
      Serial.println(" W");

      char payload[128];
      snprintf(payload, sizeof(payload),
        "{\"tegangan\":%.1f,\"arus\":%.3f,\"watt\":%.1f}",
        Vrms, Irms, Power
      );

      client.publish(topic_pub, payload);
    }
  }
}

void callback(char* topic, byte* payload, unsigned int length) {
  String msg;
  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  msg.trim();

  Serial.print("MQTT [");
  Serial.print(topic);
  Serial.print("] ");
  Serial.println(msg);

  if (String(topic) == topic_sub) {

    if (msg.equalsIgnoreCase("ON")) {
      digitalWrite(RELAY_PIN, LOW);   // NC terhubung → BEBAN ON
      Serial.println("RELAY ON (NC)");
    }
    else if (msg.equalsIgnoreCase("OFF")) {
      digitalWrite(RELAY_PIN, HIGH);    // NC terputus → BEBAN OFF
      Serial.println("RELAY OFF (NC)");
    }
  }
}

// Mqtt Connect
void reconnectMQTT() {
  while (!client.connected()) {
    String clientId = "ESP32-" + String((uint32_t)ESP.getEfuseMac(), HEX);

    Serial.print("Connecting MQTT...");
    if (client.connect(clientId.c_str(), mqtt_user, mqtt_password)) {
      Serial.println("Connected");
      client.subscribe(topic_sub);
      Serial.println("MQTT SUBSCRIBED");
    } else {
      Serial.print("Failed rc=");
      Serial.println(client.state());
      delay(3000);
    }
  }
}
float readRMS(int pin, int samples) {
  float offset = 0, sum = 0;

  for (int i = 0; i < samples; i++) offset += analogRead(pin);
  offset /= samples;

  for (int i = 0; i < samples; i++) {
    float v = (analogRead(pin) - offset) * ADC_VREF / ADC_RESOLUTION;
    sum += v * v;
  }
  return sqrt(sum / samples);
}

float dynamicCalibration(float IrmsRaw) {
  if (IrmsRaw < 0.5)
    return IrmsRaw * currentCalibrationLow;
  else
    return IrmsRaw * currentCalibrationHigh;
}