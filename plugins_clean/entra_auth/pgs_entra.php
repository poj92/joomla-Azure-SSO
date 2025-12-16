<?php
/**
 * Simple Microsoft Entra (Azure AD) SSO Authentication Plugin for Joomla
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Authentication\Authentication;

class PlgAuthenticationPgsEntra extends CMSPlugin
{
    /**
     * Handle Entra callback with authorization code
     */
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        $app = Factory::getApplication();
        $code = $app->input->get('code', '', 'string');
        $state = $app->input->get('state', '', 'string');

        // Only process if code is present
        if (empty($code)) {
            return;
        }

        // Verify state (CSRF protection)
        $session = Factory::getSession();
        $storedState = $session->get('entra_state');
        
        if (empty($storedState) || $storedState !== $state) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'Invalid state parameter. Please try again.';
            return;
        }

        // Get plugin parameters
        $clientId = $this->params->get('client_id');
        $clientSecret = $this->params->get('client_secret');
        $tenantId = $this->params->get('tenant_id');
        $redirectUri = $this->params->get('redirect_uri');

        if (empty($clientId) || empty($clientSecret) || empty($tenantId) || empty($redirectUri)) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'Plugin is not configured properly.';
            return;
        }

        // Exchange code for token
        $tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
        $postData = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
            'scope' => 'openid profile email'
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        $response_text = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'Failed to exchange authorization code.';
            return;
        }

        $token_data = json_decode($response_text, true);

        if (empty($token_data['id_token'])) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'No ID token received from Azure.';
            return;
        }

        // Decode JWT token (without signature verification for simplicity)
        $idToken = $this->decodeJwt($token_data['id_token']);

        if ($idToken === false) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'Failed to decode token.';
            return;
        }

        // Extract email
        $email = isset($idToken['email']) ? $idToken['email'] : (isset($idToken['preferred_username']) ? $idToken['preferred_username'] : '');

        if (empty($email)) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'Email not found in token.';
            return;
        }

        // Find user by email
        $userId = UserHelper::getUserIdByEmail($email);

        if (!$userId) {
            $response->status = Authentication::STATUS_FAILURE;
            $response->error_message = 'User account not found. Please contact administrator.';
            return;
        }

        // Load user and return success
        $user = Factory::getUser($userId);

        $response->username = $user->username;
        $response->email = $user->email;
        $response->fullname = $user->name;
        $response->status = Authentication::STATUS_SUCCESS;
        $response->error_message = '';
    }

    /**
     * Decode JWT token (basic, without signature verification)
     */
    private function decodeJwt($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        $payload = $parts[1];
        
        // Add padding if needed
        $padding = 4 - strlen($payload) % 4;
        if ($padding !== 4) {
            $payload .= str_repeat('=', $padding);
        }

        $decoded = base64_decode(strtr($payload, '-_', '+/'));

        return json_decode($decoded, true);
    }
}
