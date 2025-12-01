<?php
namespace Joomla\Component\Azuresso\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Site\Helper\AzureSso;

class LogoutController extends BaseController
{
    public function logout()
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $params = AzureSso::getConfig();

        // Log out locally
        $app->logout();

        // Attempt RP-initiated logout at Azure end_session_endpoint
        $provider = AzureSso::buildProviderUrl($params->get('tenant', 'common'));
        $endSession = rtrim($provider, '/') . '/oauth2/v2.0/logout';

        $postLogout = $params->get('logout_redirect');
        $url = $endSession;
        if ($postLogout) {
            $url .= '?post_logout_redirect_uri=' . urlencode($postLogout);
        }

        $app->redirect($url ?: 'index.php');
    }
}
