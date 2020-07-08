<?php
namespace Nish;


use Nish\Controllers\ILayout;

trait ModuleTrait
{
    /* @var string */
    private $viewDir;

    /* @var bool */
    protected $viewsAreDisabled = false;

    /* @var \Nish\Controllers\ILayout */
    private $layout = null;

    public function setEnvironment(string $environmentName)
    {
        self::setGlobalSetting('env', $environmentName);
    }

    public function setNotFoundAction(callable $action)
    {
        self::setGlobalSetting('notFoundAction', $action);
    }

    public function setDefaultLogger(callable $func)
    {
        self::di('defaultLogger', $func);
    }

    public function setDefaultCacher(callable $func)
    {
        self::di('defaultCacher', $func);
    }

    public function setDefaultTranslator(callable $func)
    {
        self::di('defaultTranslator', $func);
    }

    public function setDefaultSessionManager(callable $func)
    {
        self::di('defaultSessionManager', $func);
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
    public function areViewsDisabled(): bool
    {
        return $this->viewsAreDisabled;
    }

    public function disableViews(): void
    {
        $this->viewsAreDisabled = true;
    }

    public function enableViews(): void
    {
        $this->viewsAreDisabled = false;
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
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

}