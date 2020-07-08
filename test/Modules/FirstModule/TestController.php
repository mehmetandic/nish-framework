<?php

namespace Modules\FirstModule;


use Nish\Controllers\Controller;
use Nish\Utils\Http\Response;

class TestController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function testAction()
    {
        $this->viewBag->a = 111;
        //$this->renderView();
    }
}