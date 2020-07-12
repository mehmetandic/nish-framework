<?php
namespace Nish\Views;


use Nish\PrimitiveBeast;

class View extends PrimitiveBeast
{
    protected $logger;

    /* @var string */
    protected $environment;
    protected $cacher;

    /* @var \Nish\Routers\Router */
    protected $router;

    protected $translator;

    /* @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    protected $sessionManager;

    /* @var ViewBag */
    public $viewBag;

    /* @var bool */
    protected $rendered = false;


    public function __construct()
    {
        $this->logger = self::getDefaultLogger();
        $this->environment = self::getEnvironment();
        $this->cacher = self::getDefaultCacher();
        $this->router = self::getGlobalSetting('appRouterObj');
        $this->translator = self::getDefaultTranslator();
        $this->request = self::getDefaultRequestUtil();
        $this->sessionManager = self::getDefaultSessionManager();
    }

    /**
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->viewDir;
    }

    /**
     * @param ViewBag $viewBag
     */
    public function setViewBag(ViewBag $viewBag): void
    {
        $this->viewBag = $viewBag;
    }

    /**
     * @return bool
     */
    public function isRendered(): bool
    {
        return $this->rendered;
    }

    /**
     * @param bool $rendered
     */
    public function setRendered(bool $rendered): void
    {
        $this->rendered = $rendered;
    }

    /**
     * @param string $file
     * @return false|string
     */
    public function render(string $file)
    {
        ob_start();
        include($file);

        return ob_get_clean();
    }
}