<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;
$task = $input->getCmd('task', 'display');

try {
    $controller = BaseController::getInstance('Azuresso', ['base_path' => JPATH_ADMINISTRATOR . '/components/com_azuresso']);
    $controller->execute($task);
    $controller->redirect();
} catch (\Throwable $e) {
    Factory::getApplication()->enqueueMessage('Component Error: ' . $e->getMessage(), 'error');
    echo $e->getMessage();
}
