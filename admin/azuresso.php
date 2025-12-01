<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Administrator\Controller\AzuressoController;

// Load vendor autoload if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$input = Factory::getApplication()->input;
$task = $input->getCmd('task', 'display');

// Try to instantiate controller explicitly so Joomla's resolver isn't relied upon
try {
    $controller = new AzuressoController();
    $controller->execute($task);
    $controller->redirect();
} catch (\Throwable $e) {
    // Fallback to the default resolver (keeps backwards compatibility)
    $controller = BaseController::getInstance('Azuresso');
    $controller->execute($task);
}
