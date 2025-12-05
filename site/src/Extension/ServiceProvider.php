<?php
namespace Joomla\Component\Azuresso\Site;

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactory;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider as BaseProvider;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

class ServiceProvider extends BaseProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Azuresso'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Azuresso'));
        
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new Component($container->get(MVCFactory::class));
                return $component;
            }
        );
    }
}

class Component implements ComponentInterface
{
    private $mvcFactory;

    public function __construct(MVCFactory $mvcFactory)
    {
        $this->mvcFactory = $mvcFactory;
    }

    public function getMVCFactory()
    {
        return $this->mvcFactory;
    }
}
