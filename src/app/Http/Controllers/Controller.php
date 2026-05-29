<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Virtual PTSP - Base Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}