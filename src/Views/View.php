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

    /* @var ViewBag */
    public $viewBag;

    /* @var bool */
    protected $rendered = false;


    public function __construct()
    {
        $this->logger = $this->getDefaultLogger();
        $this->environment = $this->getEnvironment();
        $this->cacher = $this->getDefaultCacher();
        $this->router = self::getGlobalSetting('appRouterObj');
        $this->translator = $this->getDefaultTranslator();
        $this->request = $this->getDefaultRequestUtil();
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