<?php
namespace Joomla\Component\Azuresso\Site\Helper;

defined('_JEXEC') or die;

class AzureSso
{
    public static function getConfig()
    {
        $component = \Joomla\CMS\Component\ComponentHelper::getComponent('com_azuresso');
        $params = $component->params;
        return $params;
    }

    public static function buildProviderUrl($tenant)
    {
        // Azure v2.0 issuer base
        return 'https://login.microsoftonline.com/' . $tenant . '/v2.0';
    }

    public static function getOidcClient($params)
    {
        // Load the bundled vendor autoload
        $vendorAutoload = JPATH_ROOT . '/components/com_azuresso/vendor/autoload.php';
        if (!file_exists($vendorAutoload)) {
            $vendorAutoload = __DIR__ . '/../../../vendor/autoload.php';
        }
        if (file_exists($vendorAutoload)) {
            require_once $vendorAutoload;
        }

        $provider = self::buildProviderUrl($params->get('tenant', 'common'));
        $clientId = $params->get('client_id');
        $clientSecret = $params->get('client_secret');
        $scopes = $params->get('scopes', 'openid profile email');

        // Use the jumbojett OpenID Connect client
        $oidc = new \OpenIDConnectClient($provider, $clientId, $clientSecret);
        $oidc->addScope($scopes);

        // We will rely on discovery (the library will fetch .well-known)
        return $oidc;
    }

    public static function findUserByEmail($email)
    {
        $db = \Joomla\CMS\Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('email') . ' = ' . $db->quote($email));
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result;
    }

    public static function createUser($name, $email, $username = null, $group = 'Registered')
    {
        if (!$username) {
            // derive username from email
            $username = preg_replace('/[^a-z0-9._-]/', '', strtolower(strstr($email, '@', true)));
            if (!$username) $username = 'user' . time();
        }

        $db = \Joomla\CMS\Factory::getDbo();
        $userTable = \Joomla\CMS\Table\User::getInstance();

        $data = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password_clear' => bin2hex(random_bytes(8)),
            'block' => 0,
            'activation' => ''
        ];

        // Bind and save
        if (!$userTable->bind($data)) {
            throw new \RuntimeException('Failed to bind user data: ' . implode(', ', $userTable->getErrors()));
        }

        if (!$userTable->save()) {
            throw new \RuntimeException('Failed to save user: ' . implode(', ', $userTable->getErrors()));
        }

        // Add to default group
        try {
            $userId = $userTable->id;
            $groupId = self::getGroupIdByName($group);
            if ($groupId) {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__user_usergroup_map'))
                    ->columns([$db->quoteName('user_id'), $db->quoteName('group_id')])
                    ->values($db->quote($userId) . ', ' . (int) $groupId);
                $db->setQuery($query);
                $db->execute();
            }
        } catch (\Exception $e) {
            // ignore group assignment failures but log in admin
            \Joomla\CMS\CMSApplication::getApplication()->enqueueMessage('User created but failed to set group: ' . $e->getMessage(), 'warning');
        }

        return $userTable;
    }

    public static function getGroupIdByName($name)
    {
        $db = \Joomla\CMS\Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . ' = ' . $db->quote($name));
        $db->setQuery($query);
        return (int) $db->loadResult();
    }
}
