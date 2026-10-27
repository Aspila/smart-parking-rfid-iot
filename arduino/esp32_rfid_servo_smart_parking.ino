/*
  Smart Parking RFID IoT
  Board: ESP32 atau ESP8266
  Modul: RFID RC522 + Servo
  Endpoint: http://IP_SERVER/smart-parking-rfid-iot-v7/api/scan.php

  Library yang dibutuhkan:
  1. MFRC522 by GithubCommunity
  2. ArduinoJson by Benoit Blanchon
  3. Servo library untuk ESP8266 atau ESP32Servo untuk ESP32
*/

#if defined(ESP32)
  #include <WiFi.h>
  #include <HTTPClient.h>
  #include <ESP32Servo.h>
#else
  #include <ESP8266WiFi.h>
  #include <ESP8266HTTPClient.h>
  #include <Servo.h>
#endif

#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

#define SS_PIN 5
#define RST_PIN 22
#define SERVO_PIN 13
#define BUZZER_PIN 27
#define LED_GREEN 26
#define LED_RED 25

const char* WIFI_SSID = "NAMA_WIFI";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";

// Ganti IP sesuai alamat komputer XAMPP.
// http://192.168.x.x/smart-parking-rfid-iot-v7-live-monitor/api/scan.php
String API_URL = "http://192.168.x.x/smart-parking-rfid-iot-v7-live-monitor/api/scan.php";
String GATE_ID = "1";
String AREA_ID = "1";

MFRC522 rfid(SS_PIN, RST_PIN);
Servo gateServo;

void beep(int durationMs) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(durationMs);
  digitalWrite(BUZZER_PIN, LOW);
}

void setIndicator(bool accepted) {
  digitalWrite(LED_GREEN, accepted ? HIGH : LOW);
  digitalWrite(LED_RED, accepted ? LOW : HIGH);
  beep(accepted ? 100 : 400);
  delay(600);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);
}

void openGate(int delayMs) {
  gateServo.write(90);
  delay(delayMs);
  gateServo.write(0);
}

String getUidString() {
  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(rfid.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

String callApi(String uid) {
  if(WiFi.status()!=WL_CONNECTED){

WiFi.begin(WIFI_SSID,WIFI_PASSWORD);

}

  String url = API_URL + "?uid=" + uid + "&gate_id=" + GATE_ID + "&area_id=" + AREA_ID;

#if defined(ESP32)
  HTTPClient http;
  http.begin(url);
  http.setTimeout(5000);
#else
  WiFiClient client;
  HTTPClient http;
  http.begin(client, url);
#endif

  int httpCode = http.GET();
  String payload = "";
  if (httpCode > 0) payload = http.getString();
  http.end();
  return payload;
}

void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);

  gateServo.attach(SERVO_PIN);
  gateServo.write(0);

  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Menghubungkan WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println();
  Serial.print("WiFi tersambung. IP: ");
  Serial.println(WiFi.localIP());
  Serial.println("Tempelkan kartu RFID...");
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent()) return;
  if (!rfid.PICC_ReadCardSerial()) return;

  String uid = getUidString();
  Serial.print("UID terbaca: ");
  Serial.println(uid);

  String response = callApi(uid);
  Serial.println(response);

  StaticJsonDocument<512> doc;
  DeserializationError error = deserializeJson(doc, response);
  if (error) {
    Serial.println("Respons API tidak valid");
    setIndicator(false);
  } else {
    bool success = doc["success"] | false;
    const char* message = doc["message"] | "Tidak ada pesan";
    int delayMs = doc["delay_ms"] | 3000;
    Serial.print("Status: ");
    Serial.println(success ? "DITERIMA" : "DITOLAK");
    Serial.print("Pesan: ");
    Serial.println(message);

    setIndicator(success);
    if (success) openGate(delayMs);
  }

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();
  delay(1200);
}
