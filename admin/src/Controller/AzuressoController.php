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
            $prefix = 'Joomla\\Component\\Azuresso\\Administrator\\View\\';
            $view = $this->getView($viewName, 'html', $prefix);
            if ($view) {
                // Render the view directly
                return $view->display();
            }
        } catch (\Throwable $e) {
            // fallback to parent behaviour
        }

        return parent::display($cachable, $urlparams);
    }
}
