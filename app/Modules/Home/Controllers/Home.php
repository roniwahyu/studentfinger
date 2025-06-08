<?php

namespace App\Modules\Home\Controllers;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}