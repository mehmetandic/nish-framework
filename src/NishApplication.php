<?php
namespace Nish;

use Nish\Controllers\Controller;
use Nish\Events\Events;
use Nish\Events\IEventManager;
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
            $params = null;

            /** BEGIN: Match Route **/
            // match current request url
            $match = $this->router->match(null, (isset($_REQUEST['method']) ? $_REQUEST['method'] : null));

            // call closure or throw 404 status
            if( is_array($match) && !empty($match['target'])) {
                $target = explode('#',$match['target']);
                $module = $target[0];
                $controller = $target[1];
                $action = $target[2];

                if (isset($match['params'])) $params = $match['params'];
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

            $response = $this->runAction($controller, $action, $params);

            $eventManager = self::getDefaultEventManager();

            if ($eventManager instanceof IEventManager) {
                echo $eventManager->trigger(Events::ON_BEFORE_SEND_RESPONSE, null, $response);
            } else {
                echo $response;
            }

        } catch (NotFoundActionException $e) {
            self::callNotFoundAction();
        } catch (\Exception $e) {
            self::runUnexpectedExceptionBehaviour($e);
        }

    }

    public function setDebugMode(bool $isDebugModeOn)
    {
        self::getGlobalSetting('debugMode', $isDebugModeOn);
    }

    protected function configure()
    {
        // set default environment
        if (!self::getEnvironment()) {
            $this->setEnvironment(self::ENV_DEV);
        }

        // set default log level
        if (!self::getLogLevel()) {
            if (self::isAppInDebugMode()) {
                self::setLogLevel(Logger::DEBUG);
            } else {
                self::setLogLevel(Logger::WARNING);
            }
        }

        // configure default logger
        if (!self::getDefaultLogger()) {
            $this->setDefaultLogger(function () {
                $logger = new Logger('defaultLogger');

                $streamHandler = new \Monolog\Handler\StreamHandler(__DIR__.'/logs/'.(NishDateTime::format(time(),'Y-m-d')).'.log', self::getLogLevel());

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
        if (!self::getDefaultEventManager()){
            self::setDefaultEventManager(function () {
                return new \Nish\Events\EventManager();
            });
        }

        //configure default request utility
        if (!self::getDefaultRequestUtil()) {
            self::setDefaultRequestUtil(function () {
                return \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            });
        }

        // configure default not found action
        if (!self::getNotFoundAction()) {
            $this->setNotFoundAction(function () {
                Response::sendResponse('<h1>404 - Not Found</h1>', Response::HTTP_NOT_FOUND);
            });
        }

        // configure default unexpected exception behaviour
        if (!self::getUnexpectedExceptionBehaviour()) {
            $this->setUnexpectedExceptionBehaviour(function (\Exception $e) {
                $logger = self::getDefaultLogger();

                if ($logger) {
                    $logger->error('Exception: '.$e->getMessage().', Trace: '.$e->getTraceAsString());
                }

                Response::sendResponse('<h1>500 - Interval Server Error</h1>', Response::HTTP_INTERNAL_SERVER_ERROR);
            });
        }

        //configure default session manager
        if (!self::getDefaultSessionManager()) {
            $this->setDefaultSessionManager(function () {
                $session = new \Symfony\Component\HttpFoundation\Session\Session(new \Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage(), new \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag());

                if (!$session->isStarted()) {
                    $session->start();
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

    private function runAction(string $controllerClass, string $actionMethod, ?array $params = null)
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

        call_user_func_array([$controller, $actionMethod], $params);

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
                    return $layoutView->render($layout->getViewFile());
                }
            } else {
                return $actionOutput;
            }
        } else {
            return $actionOutput;
        }
    }

}