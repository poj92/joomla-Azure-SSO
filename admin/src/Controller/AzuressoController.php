<?php
namespace Joomla\Component\Azuresso\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class AzuressoController extends BaseController
{
    protected $default_view = 'azuresso';

    public function display($cachable = false, $urlparams = false)
    {
        return parent::display($cachable, $urlparams);
    }
}
