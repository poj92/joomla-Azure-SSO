<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Administrator\Controller\AzuressoController;

// Diagnostic logging helper (temporary)
function com_azuresso_log_diag($label, $data)
{
    try {
        $logDir = JPATH_ADMINISTRATOR . '/components/com_azuresso/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/debug.log';
        $ts = date('Y-m-d H:i:s');
        $payload = "$ts | $label | " . print_r($data, true) . "\n";
        @file_put_contents($logFile, $payload, FILE_APPEND | LOCK_EX);
    } catch (\Throwable $e) {
        // best-effort only
    }
}

// Load vendor autoload if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// gather runtime info
$input = Factory::getApplication()->input;
$task = $input->getCmd('task', 'display');
$controllerName = 'Joomla\\Component\\Azuresso\\Administrator\\Controller\\AzuressoController';
com_azuresso_log_diag('admin-entry', [
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
    'task' => $task,
    'controllerClass' => $controllerName,
    'class_exists' => class_exists($controllerName),
]);

// Try to instantiate controller explicitly so Joomla's resolver isn't relied upon
try {
    if (!class_exists(\Joomla\Component\Azuresso\Administrator\Controller\AzuressoController::class)) {
        com_azuresso_log_diag('admin-error', 'AzuressoController class not found');
        // Try to load controller file explicitly as fallback
        $controllerFile = __DIR__ . '/src/Controller/AzuressoController.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            com_azuresso_log_diag('admin-fallback-load', $controllerFile);
        } else {
            com_azuresso_log_diag('admin-fallback-load-missing', $controllerFile);
        }
    }
    $controller = new AzuressoController();
    com_azuresso_log_diag('admin-controller-created', get_class($controller));
    $controller->execute($task);
    $controller->redirect();
} catch (\Throwable $e) {
    com_azuresso_log_diag('admin-exception', $e->getMessage());
    // Fallback to the default resolver (keeps backwards compatibility)
    try {
        $controller = BaseController::getInstance('Azuresso');
        com_azuresso_log_diag('admin-fallback-controller', is_object($controller) ? get_class($controller) : $controller);
        $controller->execute($task);
    } catch (\Throwable $inner) {
        com_azuresso_log_diag('admin-fallback-exception', $inner->getMessage());
    }
}
