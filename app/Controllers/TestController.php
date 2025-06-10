<?php

namespace App\Controllers;

class TestController extends BaseController
{
    public function index()
    {
        return "Test controller is working!";
    }
    
    public function simple()
    {
        $data = [
            'title' => 'Simple Test',
            'message' => 'This is a simple test page'
        ];
        
        return view('test/simple', $data);
    }
}
