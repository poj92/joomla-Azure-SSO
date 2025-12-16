<?php
/**
 * Microsoft Entra SSO - UI Plugin for Joomla
 * Displays login button on the login form
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

class PlgSystemPgsEntra extends CMSPlugin
{
    public function onAfterRender()
    {
        $app = Factory::getApplication();

        // Only on frontend
        if (!$app->isClient('site')) {
            return;
        }

        // Only on login page
        $view = $app->input->getCmd('view');
        if ($view !== 'login') {
            return;
        }

        // Get parameters
        $clientId = $this->params->get('client_id');
        $tenantId = $this->params->get('tenant_id');
        $redirectUri = $this->params->get('redirect_uri');
        $buttonLabel = $this->params->get('button_label', 'Login with Microsoft Entra');

        if (empty($clientId) || empty($tenantId) || empty($redirectUri)) {
            return;
        }

        // Generate state for CSRF protection
        $session = Factory::getSession();
        $state = bin2hex(random_bytes(16));
        $session->set('entra_state', $state);

        // Build Azure authorize URL
        $authUrl = 'https://login.microsoftonline.com/' . urlencode($tenantId) . '/oauth2/v2.0/authorize?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => 'openid profile email',
            'response_mode' => 'query',
            'state' => $state
        ]);

        // Build button HTML
        $buttonHtml = '
        <div style="margin: 20px 0; text-align: center;">
            <a href="' . htmlspecialchars($authUrl) . '" style="
                display: inline-block;
                padding: 12px 24px;
                background-color: #0078d4;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
                transition: background-color 0.3s ease;
                border: none;
                cursor: pointer;
            " 
            onmouseover="this.style.backgroundColor=\'#106ebe\'"
            onmouseout="this.style.backgroundColor=\'#0078d4\'"
            >' . htmlspecialchars($buttonLabel) . '</a>
        </div>
        ';

        // Insert button into page
        $body = $app->getBody();
        $body = preg_replace('/<\/form>/i', $buttonHtml . '</form>', $body, 1);
        $app->setBody($body);
    }
}
