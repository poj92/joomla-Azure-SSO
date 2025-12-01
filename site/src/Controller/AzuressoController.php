<?php
namespace Joomla\Component\Azuresso\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class AzuressoController extends BaseController
{
    protected $default_view = 'azuresso';

    public function display($cachable = false, $urlparams = false)
    {
        parent::display($cachable, $urlparams);
    }
}
