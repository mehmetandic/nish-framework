<?php
namespace Nish\Controllers;

use Nish\Modules\IModule;
use Nish\PrimitiveBeast;
use Nish\Routers\Router;
use Nish\Views\View;
use Nish\Views\ViewBag;

abstract class Layout extends PrimitiveBeast implements ILayout
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

    /* @var IModule $module */
    private $module = null;

    /* @var View */
    protected $view;

    /* @var ViewBag */
    public $viewBag;

    /* @var string */
    protected $viewFile;

    /* @var string */
    private $controllerOutput = '';

    public function __construct()
    {
        $this->logger = self::getDefaultLogger();
        $this->environment = self::getEnvironment();
        $this->cacher = self::getDefaultCacher();
        $this->router = self::getGlobalSetting('appRouterObj');
        $this->translator = $this->getDefaultTranslator();
        $this->eventManager = $this->getDefaultEventManager();
        $this->request = $this->getDefaultRequestUtil();

        $this->view = new View();
        $this->viewBag = new ViewBag();
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return ViewBag | null
     */
    public function getViewBag()
    {
        return $this->viewBag;
    }

    /**
     * @return string
     */
    public function getViewFile()
    {
        return $this->viewFile;
    }

    /**
     * @param string $viewFile
     */
    public function setViewFile(string $viewFile): void
    {
        $this->viewFile = $viewFile;
    }

    /**
     * @return string
     */
    public function getControllerOutput()
    {
        return $this->controllerOutput;
    }

    /**
     * @param string $controllerOutput
     */
    public function setControllerOutput(string $controllerOutput): void
    {
        $this->controllerOutput = $controllerOutput;
    }

    /**
     * @return IModule | null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param IModule $module
     */
    public function setModule($module): void
    {
        $this->module = $module;
    }
}