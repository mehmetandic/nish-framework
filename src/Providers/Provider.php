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

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    protected $sessionManager;

    protected $translator;

    /* @var \Nish\Events\EventManager */
    protected $eventManager;

    /* @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    public function __construct()
    {
        $this->logger = self::getDefaultLogger();
        $this->environment = self::getEnvironment();
        $this->cacher = self::getDefaultCacher();
        $this->router = self::getGlobalSetting('appRouterObj');
        $this->translator = self::getDefaultTranslator();
        $this->eventManager = self::getDefaultEventManager();
        $this->request = self::getDefaultRequestUtil();
        $this->sessionManager = self::getDefaultSessionManager();
    }
}