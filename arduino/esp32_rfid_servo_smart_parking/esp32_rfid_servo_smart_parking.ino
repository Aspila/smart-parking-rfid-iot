/*
  SMART PARKING RFID IoT
  Perangkat:
  - ESP32
  - RFID RC522
  - Motor servo

  Library:
  1. MFRC522 by GithubCommunity
  2. ESP32Servo
  3. ArduinoJson by Benoit Blanchon
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <ESP32Servo.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

// ==================================================
// KONFIGURASI WIFI
// ==================================================
const char* WIFI_SSID = "Kanja";
const char* WIFI_PASSWORD = "Kanja1098";

// Gunakan IP komputer XAMPP/Laragon, jangan localhost.
const char* API_URL =
  "http://192.168.1.3/smart-parking-rfid-iot-v7-live-monitor/api/scan.php";

const int GATE_ID = 1;
const int AREA_ID = 1;

// ==================================================
// PIN RFID RC522
// ==================================================
#define RFID_SS_PIN    5
#define RFID_RST_PIN   22
#define RFID_SCK_PIN   18
#define RFID_MISO_PIN  19
#define RFID_MOSI_PIN  23

// ==================================================
// PIN SERVO
// ==================================================
#define SERVO_PIN 13

const int SERVO_TUTUP = 0;
const int SERVO_BUKA = 90;
const int DEFAULT_DELAY_SERVO = 3000;

MFRC522 rfid(RFID_SS_PIN, RFID_RST_PIN);
Servo gateServo;

String uidTerakhir = "";
unsigned long waktuScanTerakhir = 0;

const unsigned long JEDA_KARTU_SAMA = 2500;

// ==================================================
// KONEKSI WIFI
// ==================================================
bool sambungkanWiFi() {
  if (WiFi.status() == WL_CONNECTED) {
    return true;
  }

  Serial.println();
  Serial.print("Menghubungkan ke WiFi ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.setAutoReconnect(true);
  WiFi.persistent(false);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long mulai = millis();

  while (WiFi.status() != WL_CONNECTED &&
         millis() - mulai < 20000) {
    Serial.print(".");
    delay(500);
  }

  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi gagal tersambung.");
    return false;
  }

  Serial.println("WiFi tersambung.");
  Serial.print("IP ESP32: ");
  Serial.println(WiFi.localIP());

  return true;
}

// ==================================================
// MEMBACA UID KARTU
// ==================================================
String bacaUID() {
  String uid = "";

  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) {
      uid += "0";
    }

    uid += String(rfid.uid.uidByte[i], HEX);
  }

  uid.toUpperCase();
  return uid;
}

// ==================================================
// MEMBUKA DAN MENUTUP SERVO
// ==================================================
void gerakkanPalang(unsigned long waktuBuka) {
  // Membatasi waktu buka dari 1 sampai 15 detik.
  waktuBuka = constrain(waktuBuka, 1000UL, 15000UL);

  if (!gateServo.attached()) {
    gateServo.setPeriodHertz(50);
    gateServo.attach(SERVO_PIN, 500, 2400);
  }

  Serial.println("Palang membuka...");

  gateServo.write(SERVO_BUKA);
  delay(waktuBuka);

  Serial.println("Palang menutup...");

  gateServo.write(SERVO_TUTUP);
  delay(700);

  // Mengurangi getaran servo setelah selesai bergerak.
  gateServo.detach();

  Serial.println("Palang sudah tertutup.");
}

// ==================================================
// MENGIRIM UID KE API
// ==================================================
bool kirimKeServer(
  const String& uid,
  String& respons,
  int& kodeHTTP
) {
  respons = "";
  kodeHTTP = -1;

  if (!sambungkanWiFi()) {
    return false;
  }

  String url = String(API_URL)
               + "?uid=" + uid
               + "&gate_id=" + String(GATE_ID)
               + "&area_id=" + String(AREA_ID);

  Serial.println();
  Serial.println("Mengirim data ke server:");
  Serial.println(url);

  WiFiClient client;
  HTTPClient http;

  if (!http.begin(client, url)) {
    Serial.println("Gagal membuka URL API.");
    return false;
  }

  http.setConnectTimeout(5000);
  http.setTimeout(8000);
  http.addHeader("Accept", "application/json");
  http.addHeader("Cache-Control", "no-cache");

  kodeHTTP = http.GET();

  if (kodeHTTP > 0) {
    respons = http.getString();
  } else {
    Serial.print("Kesalahan HTTP: ");
    Serial.println(HTTPClient::errorToString(kodeHTTP));
  }

  http.end();

  return kodeHTTP >= 200 && kodeHTTP < 300;
}

// ==================================================
// MEMPROSES RESPONS JSON
// ==================================================
void prosesResponsServer(const String& respons) {
#if ARDUINOJSON_VERSION_MAJOR >= 7
  JsonDocument doc;
#else
  DynamicJsonDocument doc(1024);
#endif

  DeserializationError error = deserializeJson(doc, respons);

  if (error) {
    Serial.print("Respons JSON tidak valid: ");
    Serial.println(error.c_str());

    Serial.println("Isi respons server:");
    Serial.println(respons);

    Serial.println("Servo tetap tertutup.");
    return;
  }

  bool success = doc["success"] | false;

  const char* status =
    doc["status"] | (success ? "Diterima" : "Ditolak");

  const char* message =
    doc["message"] | "Tidak ada pesan";

  const char* namaUser =
    doc["nama_user"] | "-";

  const char* jenisAkses =
    doc["jenis_akses"] | "-";

  const char* perintahServo =
    doc["servo"] | "CLOSE";

  unsigned long delayServo =
    doc["delay_ms"] | DEFAULT_DELAY_SERVO;

  Serial.println();
  Serial.println("================================");
  Serial.print("Status       : ");
  Serial.println(status);

  Serial.print("Pesan        : ");
  Serial.println(message);

  Serial.print("Nama pengguna: ");
  Serial.println(namaUser);

  Serial.print("Jenis akses  : ");
  Serial.println(jenisAkses);

  Serial.print("Servo        : ");
  Serial.println(perintahServo);
  Serial.println("================================");

  if (
    success &&
    String(perintahServo).equalsIgnoreCase("OPEN")
  ) {
    gerakkanPalang(delayServo);
  } else {
    Serial.println("Akses ditolak, palang tetap tertutup.");
  }
}

// ==================================================
// PEMERIKSAAN RFID
// ==================================================
void periksaRFID() {
  byte versi = rfid.PCD_ReadRegister(MFRC522::VersionReg);

  Serial.print("Versi firmware RC522: 0x");
  Serial.println(versi, HEX);

  if (versi == 0x00 || versi == 0xFF) {
    Serial.println("RC522 tidak terdeteksi.");
    Serial.println("Periksa sambungan kabel RFID.");
  } else {
    Serial.println("RC522 berhasil terdeteksi.");
  }
}

// ==================================================
// SETUP
// ==================================================
void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println();
  Serial.println("====================================");
  Serial.println(" SMART PARKING RFID ESP32");
  Serial.println(" ESP32 + RC522 + SERVO");
  Serial.println("====================================");

  // Posisi awal servo tertutup.
  gateServo.setPeriodHertz(50);
  gateServo.attach(SERVO_PIN, 500, 2400);
  gateServo.write(SERVO_TUTUP);
  delay(800);
  gateServo.detach();

  // Inisialisasi RFID.
  SPI.begin(
    RFID_SCK_PIN,
    RFID_MISO_PIN,
    RFID_MOSI_PIN,
    RFID_SS_PIN
  );

  rfid.PCD_Init();
  delay(100);

  periksaRFID();
  sambungkanWiFi();

  Serial.println();
  Serial.println("Tempelkan kartu RFID...");
}

// ==================================================
// LOOP
// ==================================================
void loop() {
  // Mencoba menyambungkan kembali WiFi setiap 10 detik.
  if (WiFi.status() != WL_CONNECTED) {
    static unsigned long percobaanTerakhir = 0;

    if (millis() - percobaanTerakhir >= 10000) {
      percobaanTerakhir = millis();
      sambungkanWiFi();
    }
  }

  if (!rfid.PICC_IsNewCardPresent()) {
    delay(20);
    return;
  }

  if (!rfid.PICC_ReadCardSerial()) {
    delay(20);
    return;
  }

  String uid = bacaUID();
  unsigned long waktuSekarang = millis();

  // Mencegah kartu sama tercatat berulang terlalu cepat.
  if (
    uid == uidTerakhir &&
    waktuSekarang - waktuScanTerakhir < JEDA_KARTU_SAMA
  ) {
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
    return;
  }

  uidTerakhir = uid;
  waktuScanTerakhir = waktuSekarang;

  Serial.println();
  Serial.print("UID terbaca: ");
  Serial.println(uid);

  String responsServer;
  int kodeHTTP;

  if (kirimKeServer(uid, responsServer, kodeHTTP)) {
    Serial.print("HTTP status: ");
    Serial.println(kodeHTTP);

    Serial.println("Respons server:");
    Serial.println(responsServer);

    prosesResponsServer(responsServer);
  } else {
    Serial.print("Gagal menghubungi API. HTTP status: ");
    Serial.println(kodeHTTP);

    Serial.println("Servo tetap tertutup.");
  }

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  Serial.println();
  Serial.println("Jauhkan kartu, lalu tempelkan kembali...");
  delay(500);
}