<?php
/**
 * Joomla! System Plugin for Microsoft Entra (Azure AD) SSO
 * Displays login button on the login form
 */

use Joomla\CMS\Plugin\Plugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Plugin\PluginHelper;

class PlgSystemEntra extends Plugin
{
    public function onBeforeRender()
    {
        $app = Factory::getApplication();

        // Only process on frontend login form
        if (!$app->isClient('site')) {
            return;
        }

        $view = $app->input->getCmd('view');
        $task = $app->input->getCmd('task');

        // Check if we're on the login page
        if ($view === 'login' || ($task === 'login' && $view === 'user')) {
            // Get plugin parameters
            $clientId = $this->params->get('client_id');
            $tenantId = $this->params->get('tenant_id');
            $redirectUri = $this->params->get('redirect_uri');
            $buttonLabel = $this->params->get('button_label', 'Login with Microsoft Entra');

            if ($clientId && $tenantId && $redirectUri) {
                // Build the Azure login URL with state for CSRF protection
                $session = Factory::getSession();
                $state = bin2hex(random_bytes(16));
                $session->set('entra_state', $state);

                $azureLoginUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize?" . http_build_query([
                    'client_id' => $clientId,
                    'response_type' => 'code',
                    'redirect_uri' => $redirectUri,
                    'scope' => 'openid profile email',
                    'response_mode' => 'query',
                    'state' => $state
                ]);

                // Add CSS for the button
                $css = <<<CSS
                <style>
                    .entra-login-button {
                        display: inline-block;
                        width: 100%;
                        margin: 10px 0;
                        padding: 10px;
                        background-color: #0078d4;
                        color: white;
                        text-align: center;
                        border: none;
                        border-radius: 4px;
                        text-decoration: none;
                        font-weight: bold;
                        cursor: pointer;
                        transition: background-color 0.3s ease;
                    }
                    .entra-login-button:hover {
                        background-color: #106ebe;
                        color: white;
                        text-decoration: none;
                    }
                </style>
                CSS;

                // Create the button HTML
                $button = '<a href="' . htmlspecialchars($azureLoginUrl) . '" class="entra-login-button">' . htmlspecialchars($buttonLabel) . '</a>';

                // Inject into document head
                $document = Factory::getDocument();
                $document->addCustomTag($css);

                // Store button in session for access in layout
                $session->set('entra_login_button', $button);
                $session->set('entra_login_url', $azureLoginUrl);
            }
        }
    }

    public function onAfterRender()
    {
        $app = Factory::getApplication();

        // Only process on frontend
        if (!$app->isClient('site')) {
            return;
        }

        $view = $app->input->getCmd('view');
        $task = $app->input->getCmd('task');

        // Check if we're on the login page
        if ($view === 'login' || ($task === 'login' && $view === 'user')) {
            $session = Factory::getSession();
            $button = $session->get('entra_login_button');

            if ($button) {
                // Get the rendered page
                $body = Factory::getApplication()->getBody();

                // Insert the button after the login form or in a convenient location
                $pattern = '/<\/form>/i';
                $replacement = $button . '</form>';

                $newBody = preg_replace($pattern, $replacement, $body, 1);

                Factory::getApplication()->setBody($newBody);
            }
        }
    }

    /**
     * Handle Azure AD callback and perform login when code is present.
     */
    public function onAfterRoute()
    {
        $app = Factory::getApplication();

        // Frontend only
        if (!$app->isClient('site')) {
            return;
        }

        $code = $app->input->getString('code');
        $state = $app->input->getString('state');

        if (!$code) {
            return;
        }

        $session = Factory::getSession();
        $expectedState = $session->get('entra_state');

        // CSRF check
        if (!$expectedState || $expectedState !== $state) {
            return;
        }

        $clientId = $this->params->get('client_id');
        $clientSecret = $this->params->get('client_secret');
        $tenantId = $this->params->get('tenant_id');
        $redirectUri = $this->params->get('redirect_uri');

        if (!$clientId || !$clientSecret || !$tenantId || !$redirectUri) {
            return;
        }

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

        if (!isset($token['id_token'])) {
            return;
        }

        // Decode ID token (no signature validation here; rely on HTTPS + state)
        $idToken = explode('.', $token['id_token'])[1] ?? '';
        $userInfo = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $idToken)), true);
        $email = $userInfo['email'] ?? $userInfo['preferred_username'] ?? '';

        if (!$email) {
            return;
        }

        $userId = UserHelper::getUserIdByEmail($email);

        if (!$userId) {
            return;
        }

        $user = Factory::getUser($userId);

        // Build authentication response and log the user in
        $response = new AuthenticationResponse();
        $response->status = Authentication::STATUS_SUCCESS;
        $response->email = $user->email;
        $response->fullname = $user->name;
        $response->username = $user->username;
        $response->type = 'AzureAD';

        PluginHelper::importPlugin('user');
        $app->triggerEvent('onUserLogin', [(array) $response, ['action' => 'core.login.site', 'skip_password' => true]]);

        // Set the current identity and redirect to home
        $app->loadIdentity($user);
        $session->set('user', $user);
        $app->redirect(Uri::base());
    }
}
