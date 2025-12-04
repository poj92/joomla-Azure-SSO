<?php
namespace Joomla\Component\Azuresso\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class AzuressoController extends BaseController
{
    protected $default_view = 'azuresso';

    public function display($cachable = false, $urlparams = false)
    {
        // Ensure view and layout are set
        $this->input->set('view', $this->input->getCmd('view', $this->default_view));
        $this->input->set('layout', $this->input->getCmd('layout', 'default'));
        
        return parent::display($cachable, $urlparams);
    }
}
