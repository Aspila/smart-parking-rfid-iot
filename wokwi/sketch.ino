/*
  Smart Parking RFID IoT - Wokwi + Ngrok
  Board  : ESP32 DevKitC V4
  Modul  : RFID MFRC522, Servo, LED, Buzzer, tombol simulasi
  Web    : api/scan.php dari aplikasi Smart Parking RFID IoT

  Cara cepat:
  1. Jalankan Laragon dan ngrok.
  2. Ganti API_URL dengan URL ngrok kamu.
  3. Run Wokwi.
  4. Klik RFID reader, pilih kartu, lalu Tap.
     Atau tekan tombol hijau untuk UID valid dan tombol merah untuk UID tidak terdaftar.
*/

#include <WiFi.h>
#include <WiFiClient.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <ESP32Servo.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// Pin RFID MFRC522 untuk ESP32 di Wokwi
#define SS_PIN       5
#define RST_PIN      21

// Output
#define SERVO_PIN    13
#define LED_GREEN    26
#define LED_RED      25
#define BUZZER_PIN   27

// Tombol simulasi cepat
#define BTN_VALID    32
#define BTN_INVALID  33

const char* WIFI_SSID = "Wokwi-GUEST";
const char* WIFI_PASSWORD = "";

// GANTI INI DENGAN URL NGROK KAMU.
// Jika Laragon pakai domain .test dan ngrok memakai --host-header, gunakan format:
// https://xxxx.ngrok-free.app/api/scan.php
//
// Jika web dibuka dari folder localhost, gunakan format:
// https://xxxx.ngrok-free.app/smart-parking-rfid-iot-v2/api/scan.php
String API_URL = "https://ISI_URL_NGROK_KAMU.ngrok-free.app/api/scan.php";

String GATE_ID = "1";
String AREA_ID = "1";

// UID demo dari database bawaan aplikasi web.
String DEMO_VALID_UID = "04A1B2C3D4";
String DEMO_INVALID_UID = "FFFFFFFF";

// Aktif agar UID kartu preset Wokwi otomatis diarahkan ke UID demo database.
// Blue Card  01020304  -> 04A1B2C3D4
// Green Card 11223344  -> 04D5E6F7A8
// Yellow     55667788  -> 04ABCDEF12
// Red        AABBCCDD  -> 04F1F2F3F4
bool USE_DEMO_UID_MAPPING = true;

MFRC522 rfid(SS_PIN, RST_PIN);
Servo gateServo;
unsigned long lastActionMs = 0;

void beep(int durationMs) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(durationMs);
  digitalWrite(BUZZER_PIN, LOW);
}

void setIndicator(bool accepted) {
  digitalWrite(LED_GREEN, accepted ? HIGH : LOW);
  digitalWrite(LED_RED, accepted ? LOW : HIGH);
  beep(accepted ? 120 : 500);
  delay(500);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);
}

void openGate(int delayMs) {
  Serial.println("Servo: OPEN");
  gateServo.write(90);
  delay(delayMs);
  gateServo.write(0);
  Serial.println("Servo: CLOSE");
}

String normalizeUid(String uid) {
  uid.replace(" ", "");
  uid.replace(":", "");
  uid.replace("-", "");
  uid.toUpperCase();
  return uid;
}

String mapDemoUid(String uid) {
  uid = normalizeUid(uid);

  if (!USE_DEMO_UID_MAPPING) return uid;

  if (uid == "01020304") return "04A1B2C3D4";
  if (uid == "11223344") return "04D5E6F7A8";
  if (uid == "55667788") return "04ABCDEF12";
  if (uid == "AABBCCDD") return "04F1F2F3F4";

  return uid;
}

String getUidFromRfid() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

void connectWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;

  Serial.print("Menghubungkan WiFi Wokwi");
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 40) {
    delay(250);
    Serial.print(".");
    retry++;
  }

  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    Serial.print("WiFi tersambung. IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("WiFi gagal tersambung.");
  }
}

String callApi(String uid) {
  uid = normalizeUid(uid);

  if (API_URL.indexOf("ISI_URL_NGROK_KAMU") >= 0) {
    return "{\"success\":false,\"status\":\"Konfigurasi\",\"message\":\"API_URL belum diganti dengan URL ngrok\",\"servo\":\"CLOSE\"}";
  }

  connectWiFi();
  if (WiFi.status() != WL_CONNECTED) {
    return "{\"success\":false,\"status\":\"WiFi Error\",\"message\":\"WiFi tidak tersambung\",\"servo\":\"CLOSE\"}";
  }

  String url = API_URL + "?uid=" + uid + "&gate_id=" + GATE_ID + "&area_id=" + AREA_ID;
  Serial.println("Request: " + url);

  HTTPClient http;
  bool beginOk = false;

  if (url.startsWith("https://")) {
    WiFiClientSecure client;
    client.setInsecure();
    beginOk = http.begin(client, url);
  } else {
    WiFiClient client;
    beginOk = http.begin(client, url);
  }

  if (!beginOk) {
    return "{\"success\":false,\"status\":\"HTTP Error\",\"message\":\"HTTP begin gagal\",\"servo\":\"CLOSE\"}";
  }

  // Header ini penting untuk melewati warning page ngrok free.
  http.addHeader("ngrok-skip-browser-warning", "true");
  http.addHeader("User-Agent", "ESP32-Wokwi-Smart-Parking");

  int httpCode = http.GET();
  String payload = "";

  Serial.print("HTTP Code: ");
  Serial.println(httpCode);

  if (httpCode > 0) {
    payload = http.getString();
  } else {
    payload = "{\"success\":false,\"status\":\"HTTP Error\",\"message\":\"Request gagal\",\"servo\":\"CLOSE\"}";
  }

  http.end();
  return payload;
}

void processUid(String rawUid, String source) {
  String realUid = normalizeUid(rawUid);
  String uid = mapDemoUid(realUid);

  Serial.println();
  Serial.println("==============================");
  Serial.println("Sumber scan : " + source);
  Serial.println("UID terbaca : " + realUid);
  Serial.println("UID dikirim : " + uid);

  String response = callApi(uid);
  Serial.println("Respons API:");
  Serial.println(response);

  StaticJsonDocument<1024> doc;
  DeserializationError error = deserializeJson(doc, response);

  if (error) {
    Serial.println("Respons API bukan JSON valid. Cek URL ngrok dan endpoint api/scan.php.");
    setIndicator(false);
    return;
  }

  bool success = doc["success"] | false;
  const char* status = doc["status"] | "-";
  const char* message = doc["message"] | "-";
  const char* servo = doc["servo"] | "CLOSE";
  int delayMs = doc["delay_ms"] | 3000;

  Serial.print("Status  : ");
  Serial.println(status);
  Serial.print("Pesan   : ");
  Serial.println(message);
  Serial.print("Servo   : ");
  Serial.println(servo);

  setIndicator(success);

  String servoCommand = String(servo);
  servoCommand.toUpperCase();

  if (success || servoCommand == "OPEN") {
    openGate(delayMs);
  } else {
    gateServo.write(0);
  }

  Serial.println("Cek dashboard web dan access_logs.php.");
  Serial.println("==============================");
}

void setup() {
  Serial.begin(115200);
  delay(300);

  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(BTN_VALID, INPUT_PULLUP);
  pinMode(BTN_INVALID, INPUT_PULLUP);

  gateServo.attach(SERVO_PIN);
  gateServo.write(0);

  SPI.begin(18, 19, 23, SS_PIN);
  rfid.PCD_Init();

  connectWiFi();

  Serial.println();
  Serial.println("Smart Parking RFID IoT siap.");
  Serial.println("Cara tes:");
  Serial.println("1. Ganti API_URL dengan URL ngrok kamu.");
  Serial.println("2. Klik RFID reader, pilih Blue Card, lalu Tap.");
  Serial.println("3. Atau tekan tombol hijau untuk UID valid.");
  Serial.println("4. Tombol merah untuk UID tidak terdaftar.");
  Serial.println("5. Bisa juga ketik UID di Serial Monitor, contoh: 04A1B2C3D4");
}

void loop() {
  if (digitalRead(BTN_VALID) == LOW && millis() - lastActionMs > 900) {
    lastActionMs = millis();
    processUid(DEMO_VALID_UID, "Tombol valid");
  }

  if (digitalRead(BTN_INVALID) == LOW && millis() - lastActionMs > 900) {
    lastActionMs = millis();
    processUid(DEMO_INVALID_UID, "Tombol tidak terdaftar");
  }

  if (Serial.available()) {
    String typedUid = Serial.readStringUntil('\n');
    typedUid.trim();
    if (typedUid.length() > 0) {
      processUid(typedUid, "Serial Monitor");
    }
  }

  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    String uid = getUidFromRfid();
    processUid(uid, "RFID MFRC522 Wokwi");
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    delay(1200);
  }
}
