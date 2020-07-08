<?php
namespace Nish\Providers;

use Nish\PrimitiveBeast;

class Provider extends PrimitiveBeast implements IProvider
{

    protected $logger;

    /* @var string */
    protected $environment;
    protected $cacher;

    /* @var \Nish\Routers\Router */
    protected $router;

    protected $translator;

    /* @var \Nish\Events\EventManager */
    protected $eventManager;

    /* @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    public function __construct()
    {
        $this->logger = $this->getDefaultLogger();
        $this->environment = $this->getEnvironment();
        $this->cacher = $this->getDefaultCacher();
        $this->router = self::getGlobalSetting('appRouterObj');
        $this->translator = $this->getDefaultTranslator();
        $this->eventManager = $this->getDefaultEventManager();
        $this->request = $this->getDefaultRequestUtil();
    }
}