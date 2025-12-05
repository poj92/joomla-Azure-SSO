<?php
/**
 * Joomla! Authentication Plugin for Microsoft Entra (Azure AD) SSO
 */

use Joomla\CMS\Plugin\Plugin;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Application\SiteApplication;

class PlgAuthenticationEntra extends Plugin
{
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        // Basic check for SSO callback
        if (isset($_GET['code'])) {
            $clientId = $this->params->get('client_id');
            $clientSecret = $this->params->get('client_secret');
            $tenantId = $this->params->get('tenant_id');
            $redirectUri = $this->params->get('redirect_uri');
            $code = $_GET['code'];

            // Exchange code for token
            $tokenUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
            $data = [
                'client_id' => $clientId,
                'scope' => 'openid profile email',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'client_secret' => $clientSecret
            ];

            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $token = json_decode($result, true);

            if (isset($token['id_token'])) {
                // Decode ID token
                $idToken = explode('.', $token['id_token'])[1];
                $userInfo = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $idToken)), true);
                $email = $userInfo['email'] ?? $userInfo['preferred_username'] ?? '';

                if ($email) {
                    $user = Factory::getUser($email);
                    if ($user->id) {
                        $response->email = $user->email;
                        $response->fullname = $user->name;
                        $response->status = 'Success';
                        $response->error_message = '';
                    } else {
                        $response->status = 'Failure';
                        $response->error_message = 'User not found.';
                    }
                } else {
                    $response->status = 'Failure';
                    $response->error_message = 'Email not found in token.';
                }
            } else {
                $response->status = 'Failure';
                $response->error_message = 'Token exchange failed.';
            }
        }
    }
}
