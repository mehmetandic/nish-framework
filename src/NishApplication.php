<?php
namespace Nish;

use Nish\Controllers\Controller;
use Nish\Exceptions\Exception;
use Nish\Exceptions\NotFoundActionException;
use Nish\Modules\Module;
use Nish\Utils\DateTime\NishDateTime;
use Nish\Utils\Http\Response;
use Nish\Utils\Loggers\Logger;
use Nish\Modules\IModule;

class NishApplication extends PrimitiveBeast
{
    use ModuleTrait;

    /* @var \Nish\Routers\Router */
    protected $router;

    /* @var Module */
    private $module = null;

    /**
     * NishApplication constructor.
     * @param \Nish\Routers\Router $router
     */
    public function __construct($router)
    {
        $this->router = $router;
        self::setGlobalSetting('appRouterObj', $router);
    }

    public static function sayHelloWorld()
    {
        echo 'Hello World';
    }

    public function run()
    {
        try {
            /**
             * @var IModule
             */
            $module = null;
            $controller = null;
            $action = null;

            /** BEGIN: Match Route **/
            // match current request url
            $match = $this->router->match(null, (isset($_REQUEST['method']) ? $_REQUEST['method'] : null));

            // call closure or throw 404 status
            if( is_array($match) && !empty($match['target'])) {
                $target = explode('#',$match['target']);
                $module = $target[0];
                $controller = $target[1];
                $action = $target[2];
            }
            /** END: Match Route **/

            if (!empty($module)) {
                $this->module = new $module();

                if ($this->areViewsDisabled()) {
                    $this->module->disableViews(true);
                }

                if ($this->module->getLayout() == null) {
                    $this->module->setLayout($this->getLayout());
                }

                $this->module->configure();
            }

            $this->configure();

            if (empty($controller) || empty($action)) {
                throw new NotFoundActionException('Action or controller is null');
            }

            $this->runAction($controller, $action);

        } catch (NotFoundActionException | Exception $e) {
            $this->callNotFoundAction();
        }

    }

    public function setDebugMode(bool $isDebugModeOn)
    {
        self::getGlobalSetting('debugMode', $isDebugModeOn);
    }

    protected function configure()
    {
        // set default environment
        if (!$this->getEnvironment()) {
            $this->setEnvironment(self::ENV_DEV);
        }

        // set default log level
        if (!$this->getLogLevel()) {
            if ($this->isAppInDebugMode()) {
                $this->setLogLevel(Logger::DEBUG);
            } else {
                $this->setLogLevel(Logger::WARNING);
            }
        }

        // configure default logger
        if (!$this->getDefaultLogger()) {
            $this->setDefaultLogger(function () {
                $logger = new Logger('defaultLogger');

                $streamHandler = new \Monolog\Handler\StreamHandler(__DIR__.'/logs/'.(NishDateTime::format(time(),'Y-m-d')).'.log', $this->getLogLevel());

                $logger->pushHandler($streamHandler);

                return $logger;
            });
        }

        //set default response content type
        if (!Response::hasDefaultHeader('Content-Type')) {
            Response::addDefaultHeader('Content-Type', 'text/html');
        }

        //set default response charset
        if (empty(Response::getDefaultCharset())) {
            Response::setDefaultCharset('UTF-8');
        }

        //configure default event manager
        if (!$this->getDefaultEventManager()){
            $this->setDefaultEventManager(function () {
                return new \Nish\Events\EventManager();
            });
        }

        //configure default request utility
        if (!$this->getDefaultRequestUtil()) {
            $this->setDefaultRequestUtil(function () {
                return \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            });
        }

        // configure default not found action
        if (!$this->getNotFoundAction()) {
            $this->setNotFoundAction(function () {
                Response::sendResponse('<h1>404 Not Found</h1>', Response::HTTP_NOT_FOUND);
            });
        }

        //configure default session manager
        if (!$this->getDefaultSessionManager()) {
            $this->setDefaultSessionManager(function () {
                $session = new \Symfony\Component\HttpFoundation\Session\Session();

                if (!$session->isStarted()) {
                    $session->start(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(), new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag());
                }

                return $session;
            });
        }
    }

    public function getDefaultLogLineFormatter()
    {
        return new \Monolog\Formatter\LineFormatter("%datetime% :: %level_name% :: %message% :: %context% :: %extra%\n");
    }

    public function setAppRootDir(string $rootDir)
    {
        self::setGlobalSetting('appRootDir', $rootDir);
    }

    private function runAction(string $controllerClass, string $actionMethod)
    {
        $viewDir = null;

        if ($this->module != null) {
            $viewDir = $this->module->getViewDir();
        }

        if ($viewDir == null) {
            $viewDir = $this->getViewDir();
        }

        /* @var Controller $controller */
        $controller = new $controllerClass();

        $controller->setModule($this->module);

        $controller->setViewDir($viewDir);

        if ($this->module != null && $this->module->areViewsDisabled()) {
            $controller->disableView(true);
        }

        ob_start();

        call_user_func([$controller, $actionMethod]);

        // if view is not disabled and not rendered, render it
        if (!$controller->getView()->isRendered() && !$controller->isViewDisabled()) {
            $callerController = preg_replace('/Controller$/i', '', array_reverse(explode('\\',$controllerClass))[0]);
            $callerAction = preg_replace('/Action$/i', '', $actionMethod);

            $modulePart = '';

            if ($this->module != null && $this->module->getViewDir() == null) {
                $modulePart = array_reverse(explode('\\', get_class($this->module)))[0].'/';
            }

            $viewFile = $modulePart . $callerController . '/' .$callerAction . '.phtml';

            $controller->renderView(false, $viewFile, $viewDir);
        }

        $actionOutput = ob_get_clean();

        if (!$controller->isViewDisabled()) {
            $layout = $controller->getLayout();

            if ($layout == null && $this->module != null) {
                $layout = $this->module->getLayout();
            }

            if ($layout != null) {
                $layout->setControllerOutput($actionOutput);
                $layout->setModule($this->module);
                $layout->layoutAction();
                $layoutView = $layout->getView();

                if ($layoutView != null) {
                    $layoutView->setViewBag($layout->getViewBag());
                    $layoutView->controllerOutput = $layout->getControllerOutput();
                    echo $layoutView->render($layout->getViewFile());
                }
            } else {
                echo $actionOutput;
            }
        } else {
            echo $actionOutput;
        }
    }

}