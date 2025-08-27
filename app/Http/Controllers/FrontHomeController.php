<?php

namespace App\Http\Controllers;

use App\Models\Banners;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FrontHomeController extends Controller
{
    public function index(): Response
    {
        $banners = Banners::where('deleted_at', null)->get();
        return Inertia::render('Welcome', [
            'banners' => $banners
        ]);

    }

}

  