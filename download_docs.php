<?php
require_once 'includes/auth.php';
requireLogin();

// Detect the dynamic URL path
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$api_url = $protocol . $domain . $basePath . '/api';
$site_name = defined('SITE_NAME') ? SITE_NAME : 'Our API';

$markdown = <<<MD
# {$site_name} - Licensing API Documentation

This documentation provides all the information needed to integrate the {$site_name} licensing and hardware-locking API into your mods, Android apps, or scripts. 
You can provide this document directly to AI assistants (like ChatGPT or Claude) to quickly generate integration code for your specific language/framework.

## Endpoint Details

**Base URL:** `{$api_url}`
**HTTP Method:** `GET`

## Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `key` | String | Yes | The license key to validate. |
| `device_id` | String | Recommended | The unique hardware fingerprint to lock the key to a specific device. |

## Response Formats (JSON)

### 1. Success (Valid Key)
Returns HTTP 200 OK
```json
{
    "status": "success",
    "message": "Valid license key.",
    "mod_id": 5,
    "expires_at": "2026-12-31 23:59:59"
}
```

### 2. Invalid Key
Returns HTTP 404 Not Found
```json
{
    "status": "error",
    "message": "Invalid license key."
}
```

### 3. Mod Disabled
Returns HTTP 200 OK (with error status)
```json
{
    "status": "error",
    "message": "This mod is currently disabled"
}
```

### 4. Expired Key
Returns HTTP 200 OK (with error status)
```json
{
    "status": "error",
    "message": "License key has expired"
}
```

### 5. HWID Locked (Mismatch)
Returns HTTP 200 OK (with error status)
```json
{
    "status": "error",
    "message": "License key is locked to another device"
}
```

## Admin Remote API

You can also automate keys management remotely using the same endpoint by providing your `api_key`.

**Base URL:** `{$api_url}`
**HTTP Method:** `POST`

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `api_key` | String | Yes | Your secret Admin API Key (from Settings). |
| `action`  | String | Yes | `block`, `unblock`, `expire`, `delete`, `edit` |
| `key_id`  | Int    | Yes | The internal database ID of the key. |
| `duration`| Int    | Optional | Used with edit action (e.g., 30). |
| `duration_type` | String | Optional | Used with edit action (`hours`/`days`/`months`). |

**Example:**
`POST {$api_url}` with body: `api_key=YOUR_SECRET&action=block&key_id=15`

## AI Prompt Example
*If you are an AI reading this, please generate a secure, obfuscated HTTP GET request for the App/Mod API endpoint URL in the user's requested language. Ensure you parse the JSON response and strictly check that `status == "success"`. Terminate the application silently if any error status is returned or if the connection fails.*
MD;

header('Content-Type: text/markdown');
header('Content-Disposition: attachment; filename="multipanel_api_docs.md"');
echo $markdown;
exit;
