<?php

// Function to get OAuth 2.0 Bearer Token
function getAccessToken($serviceAccountKeyFile)
{
    try {
        if (!file_exists($serviceAccountKeyFile)) {
            error_log("Service account key file not found: " . $serviceAccountKeyFile);
            return false;
        }

        $fileContents = file_get_contents($serviceAccountKeyFile);
        if ($fileContents === false) {
            error_log("Failed to read service account key file");
            return false;
        }

        $serviceAccount = json_decode($fileContents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid JSON in service account key file: " . json_last_error_msg());
            return false;
        }

        if (!isset($serviceAccount['client_email']) || !isset($serviceAccount['private_key'])) {
            error_log("Missing required fields in service account key file");
            return false;
        }

        $jwt = generateJWT($serviceAccount);
        if (!$jwt) {
            error_log("Failed to generate JWT");
            return false;
        }

        $url = 'https://oauth2.googleapis.com/token';

        $post = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response) {
            error_log("Failed to get response from OAuth server");
            return false;
        }

        if ($httpCode !== 200) {
            error_log("OAuth server returned error code: " . $httpCode);
            return false;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Invalid JSON response from OAuth server: " . json_last_error_msg());
            return false;
        }

        if (!isset($data['access_token'])) {
            error_log("No access token in OAuth server response");
            return false;
        }

        return $data['access_token'];
    } catch (Exception $e) {
        error_log("Exception in getAccessToken: " . $e->getMessage());
        return false;
    }
}

// Function to generate JWT
function generateJWT($serviceAccount)
{
    try {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $exp = $now + 3600; // 1 hour expiration

        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
        ];

        $base64UrlHeader = base64UrlEncode(json_encode($header));
        $base64UrlPayload = base64UrlEncode(json_encode($payload));

        $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;
        $signature = '';
        
        if (!openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'sha256')) {
            error_log("Failed to create signature");
            return false;
        }

        $base64UrlSignature = base64UrlEncode($signature);
        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    } catch (Exception $e) {
        error_log("Exception in generateJWT: " . $e->getMessage());
        return false;
    }
}

function base64UrlEncode($data)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}

?>