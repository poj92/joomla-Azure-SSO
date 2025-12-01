<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Azuresso\Site\Controller\AzuressoController;

// Diagnostic logging helper (temporary)
function com_azuresso_log_diag_site($label, $data)
{
    try {
        $logDir = JPATH_ROOT . '/components/com_azuresso/logs';
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

$input = Factory::getApplication()->input;
$task = $input->getCmd('task', 'display');
com_azuresso_log_diag_site('site-entry', [
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
    'task' => $task,
    'controller_exists' => class_exists(\Joomla\Component\Azuresso\Site\Controller\AzuressoController::class),
]);

// Try to instantiate controller explicitly (avoid resolver issues)
try {
    if (!class_exists(\Joomla\Component\Azuresso\Site\Controller\AzuressoController::class)) {
        com_azuresso_log_diag_site('site-error', 'AzuressoController class not found (site)');
    }
    $controller = new AzuressoController();
    com_azuresso_log_diag_site('site-controller-created', get_class($controller));
    $controller->execute($task);
    $controller->redirect();
} catch (\Throwable $e) {
    com_azuresso_log_diag_site('site-exception', $e->getMessage());
    // Fallback to default resolver
    try {
        $controller = BaseController::getInstance('Azuresso');
        com_azuresso_log_diag_site('site-fallback-controller', is_object($controller) ? get_class($controller) : $controller);
        $controller->execute($task);
        $controller->redirect();
    } catch (\Throwable $inner) {
        com_azuresso_log_diag_site('site-fallback-exception', $inner->getMessage());
    }
}
