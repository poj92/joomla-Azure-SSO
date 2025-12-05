<?php
/**
 * Joomla! System Plugin for Microsoft Entra (Azure AD) SSO
 * Displays login button on the login form
 */

use Joomla\CMS\Plugin\Plugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

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
                // Build the Azure login URL
                $azureLoginUrl = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/authorize?" . http_build_query([
                    'client_id' => $clientId,
                    'response_type' => 'code',
                    'redirect_uri' => $redirectUri,
                    'scope' => 'openid profile email',
                    'response_mode' => 'query'
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
                $session = Factory::getSession();
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
}
