<?php
namespace Nish\Controllers;


use Nish\Modules\IModule;
use Nish\PrimitiveBeast;
use Nish\Views\View;
use Nish\Views\ViewBag;

class Controller extends PrimitiveBeast implements IController
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

    /* @var \Symfony\Component\HttpFoundation\Session\Session */
    protected $sessionManager;

    /* @var IModule $module */
    private $module = null;

    /* @var View */
    protected $view;

    /* @var ViewBag */
    public $viewBag;

    /* @var string */
    protected $viewDir;

    /* @var bool */
    protected $viewIsDisabled = false;

    /* @var ILayout */
    private $layout = null;

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

        $this->view = new View();
        $this->viewBag = new ViewBag();
    }

    /**
     * @return View
     */
    public function getView(): View
    {
        return $this->view;
    }

    public function renderView($returnResult = false, $viewFile = null, $viewDir = null)
    {
        if ($viewDir != null) {
            $this->setViewDir($viewDir);
        }

        if ($viewFile == null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $callerController = preg_replace('/Controller$/i', '', array_reverse(explode('\\',$backtrace[1]['class']))[0]);
            $callerAction = preg_replace('/Action$/i', '', $backtrace[1]['function']);

            $modulePart = '';
            $module = $this->getModule();

            if ($module != null) {
                if ($module->getViewDir() == null) {
                    $modulePart = array_reverse(explode('\\', get_class($module)))[0].'/';
                }
            }

            $viewFile = $modulePart . $callerController . '/' .$callerAction . '.phtml';
        }

        $this->view->setViewBag($this->viewBag);

        $this->view->setRendered(true);

        $output = $this->view->render($this->getViewDir().$viewFile);

        if ($returnResult) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * @return string|null
     */
    public function getViewDir()
    {
        return $this->viewDir;
    }

    /**
     * @param string $viewDir
     */
    public function setViewDir(string $viewDir): void
    {
        $this->viewDir = $viewDir;
    }

    /**
     * @return bool
     */
    public function isViewDisabled(): bool
    {
        return $this->viewIsDisabled;
    }

    public function disableView(): void
    {
        $this->viewIsDisabled = true;
    }

    public function enableView(): void
    {
        $this->viewIsDisabled =false;
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

    /**
     * @return ILayout | null
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param ILayout $layout
     */
    public function setLayout(ILayout $layout): void
    {
        $this->layout = $layout;
    }

}