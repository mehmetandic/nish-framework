<?php
namespace Nish\Modules;

use Nish\ModuleTrait;
use Nish\PrimitiveBeast;

abstract class Module extends PrimitiveBeast implements IModule
{
    use ModuleTrait;

    /**
     * @var \Nish\Routers\Router|null
     */
    protected $router;

    public function __construct()
    {
        $this->router = self::getRouter();
    }
}