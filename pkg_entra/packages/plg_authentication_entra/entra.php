<?php
/**
 * Joomla! Authentication Plugin for Microsoft Entra (Azure AD) SSO
 */

use Joomla\CMS\Plugin\Plugin;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Authentication\Authentication;

class PlgAuthenticationEntra extends Plugin
{
    public function onUserAuthenticate($credentials, $options, &$response)
    {
        // This plugin is now primarily handled by the system plugin
        // which processes the Azure callback via onAfterRoute.
        // This method is kept for compatibility but the main logic
        // has been moved to plg_system_entra.
        
        // If credentials are provided with an email (from system plugin),
        // validate and return success
        if (isset($credentials['username']) && isset($credentials['email'])) {
            $userId = UserHelper::getUserId($credentials['username']);
            
            if ($userId) {
                $user = Factory::getUser($userId);
                $response->email = $user->email;
                $response->fullname = $user->name;
                $response->username = $user->username;
                $response->status = Authentication::STATUS_SUCCESS;
                $response->error_message = '';
            } else {
                $response->status = Authentication::STATUS_FAILURE;
                $response->error_message = 'User not found.';
            }
        }
    }
}
