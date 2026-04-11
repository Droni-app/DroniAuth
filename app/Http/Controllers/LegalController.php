<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LegalController extends Controller
{
    public function index()
    {
        return Inertia::render('Legal');
    }
}
