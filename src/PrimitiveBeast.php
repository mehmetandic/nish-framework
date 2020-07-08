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

    public function getRouter()
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

    public function getEnvironment()
    {
        return self::getGlobalSetting('env');
    }

    public function isAppInDebugMode(): bool
    {
        return self::getGlobalSetting('debugMode') === true;
    }

    public function setLogLevel(int $level)
    {
        self::setGlobalSetting('logLevel', $level);
    }

    public function getLogLevel()
    {
        return self::getGlobalSetting('logLevel');
    }

    public function getNotFoundAction()
    {
        return self::getGlobalSetting('notFoundAction');
    }

    public function callNotFoundAction()
    {
        call_user_func($this->getNotFoundAction());
    }

    public function getDefaultLogger()
    {
        return self::di('defaultLogger');
    }

    public function getDefaultCacher()
    {
        return self::di('defaultCacher');
    }

    public function getDefaultTranslator()
    {
        return self::di('defaultTranslator');
    }

    public function getAppRootDir()
    {
        return self::getGlobalSetting('appRootDir');
    }

    public function setDefaultEventManager(callable $func)
    {
        self::di('defaultEventManager', $func);
    }

    /**
     * @return \Nish\Events\EventManager | null
     */
    public function getDefaultEventManager()
    {
        return self::di('defaultEventManager');
    }

    public function setDefaultRequestUtil(callable $func)
    {
        self::di('defaultRequestUtil', $func);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getDefaultRequestUtil()
    {
        return self::di('defaultRequestUtil');
    }

    public function getDefaultSessionManager()
    {
        return self::di('defaultSessionManager');
    }

    public function memoizedCall($methodName, array $args = null, int $expiresAfter = 3600)
    {
        if (empty($args)) $args = [];

        $serializedArgs = sha1(serialize($args));

        $cacher = $this->getDefaultCacher();

        if (empty($cacher)) {
            $result = call_user_func_array([$this, $methodName], $args);
        } else {
            $obj = $this;

            $key = 'methods|' . str_replace(['/','\\'], '|', trim(get_class($obj), '/\\')).'|'.$serializedArgs;

            $result = $this->cacher->get($key, function (\Symfony\Contracts\Cache\ItemInterface $item) use ($obj, $methodName, $args, $expiresAfter) {
                $item->expiresAfter($expiresAfter);

                return call_user_func_array([$obj, $methodName], $args);
            });
        }

        return $result;
    }
}