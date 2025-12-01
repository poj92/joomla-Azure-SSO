<?php
namespace Joomla\Component\Azuresso\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class AzuressoController extends BaseController
{
    protected $default_view = 'azuresso';

    public function display($cachable = false, $urlparams = false)
    {
        // Try to explicitly create the admin view namespace so Joomla finds the correct view class
        try {
            $viewName = $this->input->getCmd('view', $this->default_view);

            // First try the Joomla resolver path
            $prefix = 'Joomla\\Component\\Azuresso\\Administrator\\View\\';
            $view = $this->getView($viewName, 'html', $prefix);
            if ($view) {
                return $view->display();
            }

            // If resolver fails, instantiate the expected view class directly
            $viewClass = 'Joomla\\Component\\Azuresso\\Administrator\\View\\Azuresso\\AzuressoView';
            if (!class_exists($viewClass)) {
                $viewFile = __DIR__ . '/../View/Azuresso/HtmlView.php';
                if (file_exists($viewFile)) {
                    require_once $viewFile;
                }
            }

            if (class_exists($viewClass)) {
                $v = new $viewClass();
                if (method_exists($v, 'display')) {
                    return $v->display();
                }
            }
        } catch (\Throwable $e) {
            // fallback to parent behaviour
        }

        return parent::display($cachable, $urlparams);
    }
}
