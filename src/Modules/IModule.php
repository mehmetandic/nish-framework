<?php
namespace Nish\Modules;

interface IModule
{
    public function configure();
    public function setEnvironment(string $environmentName);
    public function setNotFoundAction(callable $action);
    public function setDefaultLogger(callable $func);
    public function setDefaultCacher(callable $func);
    public function getViewDir();
    public function setViewDir(string $viewDir);
    public function areViewsDisabled();
    public function disableViews();
    public function enableViews();
}