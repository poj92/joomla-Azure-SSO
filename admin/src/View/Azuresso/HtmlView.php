<?php
namespace Joomla\Component\Azuresso\Administrator\View\Azuresso;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

class AzuressoView extends HtmlView
{
    public function display($tpl = null)
    {
        // Use the parent display method provided by Joomla's HtmlView implementation.
        // renderDisplay() is not a standard method and will cause a fatal error.
        return parent::display($tpl);
    }
}
