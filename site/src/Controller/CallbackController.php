<?php
namespace Joomla\Component\Azuresso\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Site\Helper\AzureSso;

class CallbackController extends BaseController
{
    public function handle()
    {
        $app = \Joomla\CMS\Factory::getApplication();
        $params = AzureSso::getConfig();

        $oidc = AzureSso::getOidcClient($params);

        $callbackUrl = \Joomla\CMS\Uri\Uri::root(true) . '/index.php?option=com_azuresso&task=callback.handle';
        $oidc->setRedirectURL($callbackUrl);

        try {
            // complete authentication and obtain claims
            $oidc->authenticate();
            $claims = $oidc->getVerifiedClaims();

            $email = $claims['email'] ?? ($claims['preferred_username'] ?? null);
            $name = $claims['name'] ?? ($claims['given_name'] ?? $email);

            if (!$email) {
                throw new \RuntimeException('No email claim returned by provider.');
            }

            // find existing Joomla user
            $existing = AzureSso::findUserByEmail($email);

            if ($existing) {
                // log user in
                $credentials = [
                    'username' => $existing->username,
                    'password' => null,
                ];

                // Force-login the user by setting the session (requires Joomla user library)
                $user = \Joomla\CMS\User\User::getInstance((int) $existing->id);
                if ($user) {
                    \Joomla\CMS\Factory::getApplication()->login(['username' => $existing->username, 'password' => ''], []);
                }

                $app->enqueueMessage('Signed in as ' . $existing->username, 'message');
                $app->redirect('index.php');
                return;
            }

            // Not existing
            $auto = (bool) $params->get('auto_provision', 1);
            if (!$auto) {
                $app->enqueueMessage('User not found in Joomla and auto-provisioning is disabled.', 'error');
                $app->redirect('index.php');
                return;
            }

            // Create the user
            $defaultGroup = $params->get('default_user_group', 'Registered');
            $userTable = AzureSso::createUser($name, $email, null, $defaultGroup);

            // log in the newly created user
            $newUser = \Joomla\CMS\User\User::getInstance((int) $userTable->id);
            if ($newUser) {
                \Joomla\CMS\Factory::getApplication()->login(['username' => $userTable->username, 'password' => ''], []);
            }

            $app->enqueueMessage('User created and signed in: ' . $userTable->username, 'message');
            $app->redirect('index.php');

        } catch (\Exception $e) {
            $app->enqueueMessage('Authentication failed: ' . $e->getMessage(), 'error');
            $app->redirect('index.php');
        }
    }
}
