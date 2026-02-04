# Basketball Arcade API Documentation

## Endpoint
**URL:** `https://your-server-domain/api/match.php`
**Method:** POST
**Content-Type:** application/json

## Request Body
```json
{
  "player1_score": 15,
  "player2_score": 12,
  "game_duration": 120,
  "notes": "From Arduino" (optional)
}
```

## Success Response
```json
{
  "success": true,
  "message": "Game data saved successfully",
  "data": {
    "game_id": 23,
    "player1_score": 15,
    "player2_score": 12,
    "winner": "Player 1",
    "game_duration": 120
  },
  "timestamp": "2025-01-25 14:30:00"
}
```

## Error Response
```json
{
  "success": false,
  "message": "Error description",
  "data": null,
  "timestamp": "2025-01-25 14:30:00"
}
```

## Example Arduino Code (ESP8266/ESP32)
```cpp
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverUrl = "https://your-server-domain/api/match.php";

void sendGameData(int p1Score, int p2Score, int duration) {
  if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    WiFiClient client;
    
    http.begin(client, serverUrl);
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<200> doc;
    doc["player1_score"] = p1Score;
    doc["player2_score"] = p2Score;
doc["game_duration"] = duration;
doc["notes"] = "From Arduino";

String jsonData;
serializeJson(doc, jsonData);

int httpCode = http.POST(jsonData);

if(httpCode > 0) {
  String response = http.getString();
  Serial.println("Response: " + response);
}

http.end();

}
}

