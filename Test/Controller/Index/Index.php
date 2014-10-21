<?php
namespace Test\Controller\Index;

use Jason\Middleware\Controller;
use Jason\Middleware\View;

class Index extends Controller
{
    public function welcome()
    {
        return View::init('welcome')->render();
    }
} 