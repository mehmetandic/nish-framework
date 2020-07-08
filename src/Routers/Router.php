<?php
namespace Nish\Routers;


use Nish\Exceptions\RouteMappingException;

class Router extends \AltoRouter
{
    public function map($method, $route, $target, $name = null)
    {
        if (is_array($target)) {
            if (count($target) == 3) {
                $module = $target[0];
                $controller = $target[1];
                $action = $target[2];
            } elseif (count($target) == 2) {
                $module = '';
                $controller = $target[0];
                $action = $target[1];
            } else {
                throw new RouteMappingException('Route target must have 2 or 3 elements.');
            }

            $target = $module.'#'.$controller.'#'.$action;
        }

        return parent::map($method, $route, $target,  $name);
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function route($route)
    {
        header('Location: '.$route);
        exit();
    }
}