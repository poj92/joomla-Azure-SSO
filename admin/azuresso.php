<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Load vendor autoload if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

$input = Factory::getApplication()->input;
$task = $input->getCmd('task', 'display');

$controller = BaseController::getInstance('Azuresso');
$controller->execute($task);
