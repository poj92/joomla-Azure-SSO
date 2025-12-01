<?php
namespace Joomla\Component\Azuresso\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Site\Helper\AzureSso;

class LoginController extends BaseController
{
    public function login()
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $params = AzureSso::getConfig();

        $oidc = AzureSso::getOidcClient($params);

        // set redirect URI to the component callback route
        $callbackUrl = \Joomla\CMS\Uri\Uri::root(true) . '/index.php?option=com_azuresso&task=callback.handle';
        $oidc->setRedirectURL($callbackUrl);

        // optional: set response_type to id_token+code for hybrid
        // $oidc->setResponseTypes(['code']);

        // Start authentication - redirects user to Azure
        try {
            $oidc->authenticate();
            // If authenticate returns (rare) get claims
            $claims = $oidc->getVerifiedClaims();
            $app->enqueueMessage('Authentication completed (unexpected direct return).', 'message');
            // Redirect to home
            $app->redirect('index.php');
        } catch (\Exception $e) {
            $app->enqueueMessage('Authentication start failed: ' . $e->getMessage(), 'error');
            $app->redirect('index.php');
        }
    }
}
