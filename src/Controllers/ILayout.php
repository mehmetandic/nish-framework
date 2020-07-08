<?php

namespace Nish\Controllers;


interface ILayout
{
    public function layoutAction();

    /**
     * @return \Nish\Views\View
     */
    public function getView();
    public function getViewBag();
    public function getViewFile();
    public function setViewFile(string $viewFile);
    public function getControllerOutput();
    public function setControllerOutput(string $controllerOutput);
    public function getModule();
    public function setModule($module);
}