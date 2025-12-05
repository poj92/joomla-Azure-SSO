<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Administrator\Controller\AzuressoController;

$app = Factory::getApplication();
$input = $app->input;
$task = $input->getCmd('task', 'display');

try {
    $controller = new AzuressoController();
    $controller->execute($task);
    $controller->redirect();
} catch (\Throwable $e) {
    $app->enqueueMessage('Component Error: ' . $e->getMessage(), 'error');
    echo 'Error: ' . $e->getMessage();
}
