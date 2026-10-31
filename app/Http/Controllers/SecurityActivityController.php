<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SecurityActivityController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('customer.security.index', $request->query());
    }
}
