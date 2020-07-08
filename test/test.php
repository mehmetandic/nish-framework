<?php
use Nish\NishApplication;
use Nish\Utils\Http\Response;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
spl_autoload_register(function($className)
{
    $namespace=str_replace("\\","/",__NAMESPACE__);
    $className=str_replace("\\","/",$className);
    $class = __DIR__.'/'.(empty($namespace)?"":$namespace."/")."{$className}.php";
    include_once($class);
});

\Nish\Utils\DateTime\NishDateTime::setTimezone(date_default_timezone_get());

$router = new \Nish\Routers\Router();
$router->setBasePath('/nish-framework/test');
$router->map('GET|POST', '/', [
    \Modules\FirstModule\FirstModule::class,
    \Modules\FirstModule\TestController::class,
    'testAction'
]);

$app = new NishApplication($router);
$app->setViewDir(__DIR__.'/Views/');
$app->setAppRootDir(__DIR__);
$app->setLayout(new \Layouts\DefaultLayout());
$app->setDebugMode(false);
$app->setEnvironment(NishApplication::ENV_DEV);
$app->setDefaultCacher(function () use ($app){
    try {
        //return new \Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter('test',0, __DIR__.'/cache/');
        $conn = RedisTagAwareAdapter::createConnection(
        // provide a string dsn
        'redis://localhost:6379',

        // associative array of configuration options
        [
            'compression' => false,
            'lazy' => false,
            'persistent' => 0,
            'persistent_id' => null,
            'tcp_keepalive' => 0,
            'timeout' => 30,
            'read_timeout' => 0,
            'retry_interval' => 0,
        ]);

        if ($conn->isConnected()) {
            return (new RedisTagAwareAdapter($conn,'testDefault', 3600));
        }

        return null;
    } catch (\Exception $e) {
        /* @var \Nish\Utils\Loggers\Logger */
        $logger = $app->getDefaultLogger();

        if (!empty($logger)) {
            $logger->alert($e->getMessage());
        }

        return null;
    }

});

Response::setDefaultCharset('UTF-8');
Response::addDefaultHeader('Content-Type', 'text/html');

// run app
$app->run();