<?php
/**
 * Google OAuth Configuration
 * Jowaki Electrical Services
 */

// Google OAuth Credentials (loaded from environment or left blank to avoid committing secrets)
// Set env vars: GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET on the server
$clientID     = getenv('GOOGLE_CLIENT_ID') ?: '';
$clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';

// Redirect URI - Change this when deploying to production
// Use environment override if set; otherwise provide a safe default placeholder
$redirectUri  = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/callback/google_auth.php';

// For production, uncomment and use this instead:
// $redirectUri = 'https://jowakielectricalsrvs.com/API/google_auth.php';

// Google OAuth endpoints
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/auth';
$googleTokenUrl = 'https://oauth2.googleapis.com/token';
$googleUserInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

// Scopes for user data
$scopes = [
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile'
];

// Function to get Google OAuth URL
function getGoogleAuthUrl($state = '') {
    global $clientID, $redirectUri, $googleAuthUrl, $scopes;
    
    $params = [
        'client_id' => $clientID,
        'redirect_uri' => $redirectUri,
        'scope' => implode(' ', $scopes),
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ];
    
    if (!empty($state)) {
        $params['state'] = $state;
    }
    
    return $googleAuthUrl . '?' . http_build_query($params);
}

// Function to exchange authorization code for access token
function getGoogleAccessToken($code) {
    global $clientID, $clientSecret, $redirectUri, $googleTokenUrl;
    
    $postData = [
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirectUri
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $googleTokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

// Function to get user info from Google
function getGoogleUserInfo($accessToken) {
    global $googleUserInfoUrl;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $googleUserInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return false;
}
?>
