<?php
namespace Nish;


abstract class PrimitiveBeast
{
    public const ENV_DEV = 'dev';
    public const ENV_STAGE = 'stage';
    public const ENV_PROD = 'prod';

    private static $globalAppSettings = [];

    private static $globalDI = [];

    public static function setGlobalSetting(string $settingName, $value)
    {
        self::$globalAppSettings[$settingName] = $value;
    }

    public static function hasGlobalSetting(string $settingName)
    {
        return isset(self::$globalAppSettings[$settingName]);
    }

    public static function getGlobalSetting(string $settingName)
    {
        if (self::hasGlobalSetting($settingName)) {
            return self::$globalAppSettings[$settingName];
        } else {
            return null;
        }
    }

    /**
     * @return \Nish\Routers\Router|null
     */
    public static function getRouter()
    {
        return self::getGlobalSetting('appRouterObj');
    }

    public static function getAllGlobalSettings()
    {
        return self::$globalAppSettings;
    }

    public static function di(string $injectionName, callable $func = null)
    {
        if (isset($func)) {
            self::$globalDI[$injectionName] = [
                'func' => $func,
                'val' => null
            ];
        } elseif (isset(self::$globalDI[$injectionName])) {
            if (self::$globalDI[$injectionName]['val'] == null) {
                self::$globalDI[$injectionName]['val'] = call_user_func(self::$globalDI[$injectionName]['func']);
            }

            return self::$globalDI[$injectionName]['val'];
        }

        return null;
    }

    public static function getEnvironment()
    {
        return self::getGlobalSetting('env');
    }

    public static function isAppInDebugMode(): bool
    {
        return self::getGlobalSetting('debugMode') === true;
    }

    public static function setLogLevel(int $level)
    {
        self::setGlobalSetting('logLevel', $level);
    }

    public static function getLogLevel()
    {
        return self::getGlobalSetting('logLevel');
    }

    public static function getNotFoundAction()
    {
        return self::getGlobalSetting('notFoundAction');
    }

    public static function callNotFoundAction()
    {
        call_user_func(self::getNotFoundAction());
    }

    /**
     * @return \Nish\Utils\Loggers\Logger|null
     */
    public static function getDefaultLogger()
    {
        return self::di('defaultLogger');
    }

    public static function getDefaultCacher()
    {
        return self::di('defaultCacher');
    }

    public static function getDefaultTranslator()
    {
        return self::di('defaultTranslator');
    }

    public static function getAppRootDir()
    {
        return self::getGlobalSetting('appRootDir');
    }

    public static function setDefaultEventManager(callable $func)
    {
        self::di('defaultEventManager', $func);
    }

    /**
     * @return \Nish\Events\EventManager | null
     */
    public static function getDefaultEventManager()
    {
        return self::di('defaultEventManager');
    }

    public static function setDefaultRequestUtil(callable $func)
    {
        self::di('defaultRequestUtil', $func);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function getDefaultRequestUtil()
    {
        return self::di('defaultRequestUtil');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session|null
     */
    public static function getDefaultSessionManager()
    {
        return self::di('defaultSessionManager');
    }

    public function memoizedCall($methodName, array $args = null, int $expiresAfter = 3600)
    {
        if (empty($args)) $args = [];

        $serializedArgs = sha1(serialize($args));

        $cacher = self::getDefaultCacher();

        if (empty($cacher)) {
            $result = call_user_func_array([$this, $methodName], $args);
        } else {
            $obj = $this;

            $key = 'methods|' . str_replace(['/','\\'], '|', trim(get_class($obj), '/\\')).'|'.$serializedArgs;

            $result = $cacher->get($key, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($obj, $methodName, $args, $expiresAfter) {
                $item->expiresAfter($expiresAfter);

                return call_user_func_array([$obj, $methodName], $args);
            });
        }

        return $result;
    }
}