<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Virtual PTSP - Welcome Controller
 * Built with ❤️ by zhayyn (+6281317361689)
 */
class WelcomeController extends Controller
{
    /**
     * Display landing page
     */
    public function index(): View
    {
        return view('welcome');
    }
}