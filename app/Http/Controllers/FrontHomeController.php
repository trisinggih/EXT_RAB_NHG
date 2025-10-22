<?php

namespace App\Http\Controllers;

use App\Models\Banners;
use App\Models\Services;
use App\Models\Konten;
use App\Models\SettingWeb;
use App\Models\Blogs;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FrontHomeController extends Controller
{
    public function index(): Response
    {
        $banners = Banners::where('deleted_at', null)->get();
        $services = Services::where('deleted_at', null)->get();
        $kontens = Konten::get();
        $settweb = SettingWeb::get();
        return Inertia::render('Welcome', [
            'banners' => $banners,
            'services' => $services,
            'kontens' => $kontens,
            'settingweb' => $settweb
        ]);
    }

    public function blog(): Response
    {
        $blogs = Blogs::orderBy('date_blog', 'desc')->get();
        $settweb = SettingWeb::get();
        return Inertia::render('Blog', [
            'blogs' => $blogs,
            'settingweb' => $settweb
        ]);
    }

    public function project(): Response
    {
        $projects = Project::join('client', 'project.client_id', '=', 'client.id')
        ->select('project.*', 'client.name as client_name') 
        ->orderBy('project.id', 'desc')
        ->get();
        $settweb = SettingWeb::get();
        return Inertia::render('Project', [
            'projects' => $projects,
            'settingweb' => $settweb
        ]);
    }


}

  