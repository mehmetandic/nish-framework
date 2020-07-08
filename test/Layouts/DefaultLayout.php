<?php
namespace Layouts;


use Nish\Controllers\Layout;

class DefaultLayout extends Layout
{

    public function layoutAction()
    {
        if ($this->getModule() != null) {
            $viewDir = $this->getModule()->getViewDir();
        } else {
            $viewDir = self::getAppRootDir();
        }
        $this->setViewFile($viewDir . 'Views/defaultLayout.phtml');
    }
}