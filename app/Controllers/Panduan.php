<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Panduan extends BaseController
{
    public function index()
    {
        return view('panduan/index');
    }
}