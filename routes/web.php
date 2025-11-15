<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PekerjaanController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\FrontHomeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AnnotationController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\ProductPekerjaanController;
use App\Http\Controllers\ProjectProductController;
use App\Http\Controllers\RABController;
use App\Http\Controllers\RABExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectGambarController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;

Route::get('/', [FrontHomeController::class, 'Index'])->name('home');
Route::get('/blog', [FrontHomeController::class, 'Blog'])->name('blog');

Route::get('/allprojects', [FrontHomeController::class, 'Project'])->name('allproject');

// routes/web.php
Route::get('/rab/pdf', [RABController::class, 'generatePDF'])->name('rab.pdf');
Route::get('/rab/pdf2', [RABController::class, 'generatePDFID'])->name('rab.pdf2');
Route::get('/rab/excel', [RABExportController::class, 'export'])->name('rab.export.excel');
Route::get('/rab/excel2', [RABExportController::class, 'export2'])->name('rab.export.excel2');

Route::post('/project-gambar', [ProjectGambarController::class, 'store'])
    ->name('project-gambar.store');


Route::delete('/project-gambar/{id}', [ProjectGambarController::class, 'destroy'])
    ->name('project-gambar.destroy');

Route::put('/projectdetail/update-supplier', [ProjectController::class, 'updateSupplier'])
    ->name('projectdetail.updateSupplier');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('supplierdashboard', function () {
    return Inertia::render('DashboardSupplier');
})->middleware(['auth:supplier'])->name('supplier.dashboard');


Route::get('clientdashboard', function () {
    return Inertia::render('DashboardClient');
})->middleware(['auth:client'])->name('client.dashboard');

// this will create a group route. Those authorize to access them are authenticated and verified
// users.
Route::middleware(['auth:supplier'])
    ->prefix('supplier')
    ->as('supplier.')
    ->group(function () {

        Route::get('dashboard', fn() => Inertia::render('DashboardSupplier'))
            ->name('dashboard');

        Route::get('materials', [MaterialController::class, 'IndexSup'])
            ->name('materials.index');
        Route::get('materials/create', [MaterialController::class, 'CreateSup'])
            ->name('materials.create');
        Route::post('materials', [MaterialController::class, 'StoreSup'])
            ->name('materials.store');
        Route::get('materials/{material}/edit', [MaterialController::class, 'EditSup'])
            ->name('materials.edit');
        Route::put('materials/{material}', [MaterialController::class, 'UpdateSup'])
            ->name('materials.update');
        Route::delete('materials/{material}', [MaterialController::class, 'DestroySup'])
            ->name('materials.destroy');

        Route::post('suppliermaterials', [SupplierController::class, 'storeMaterialsSup'])->name('supplier.material.store');

        Route::get('{supplier}/material', [SupplierController::class, 'materialSup'])->name('supplier.material');
        Route::delete('suppliermaterial/{supplier}', [SupplierController::class, 'DestroyMaterialSup'])->name('supplier.material.destroy');
        Route::get('suppliermaterial/{supplier}', [SupplierController::class, 'materialjson'])->name('supplier.materialjson');



    });


Route::middleware(['auth:client'])
    ->prefix('client')
    ->as('client.')
    ->group(function () {

        Route::get('dashboard', fn() => Inertia::render('DashboardClient'))
            ->name('dashboard');

        // Route::get('projects', [MaterialController::class, 'Index'])
        //     ->name('materials.index');

        Route::get('projects', [ProjectController::class, 'IndexClient'])->name('projects.index');

    });


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('bom', [ProductController::class, 'Index'])->name('products.index');
    Route::get('bom/create', [ProductController::class, 'Create'])->name('products.create');
    Route::post('bom', [ProductController::class, 'Store'])->name('products.store');
    Route::get('bom/{product}/edit', [ProductController::class, 'Edit'])->name('products.edit');
    Route::put('bom/{product}', [ProductController::class, 'Update'])->name('products.update');
    Route::delete('bom/{product}', [ProductController::class, 'Destroy'])->name('products.destroy');
    Route::get('bom/{product}/material', [ProductController::class, 'Material'])->name('products.material');
    Route::delete('bommaterial/{id}', [ProductController::class, 'DestroyMaterial'])->name('productmaterial.destroy');
    Route::get('bom/{product}/services', [ProductController::class, 'Services'])->name('products.services');

    Route::post('bommaterial/create',[BomController::class, 'store'])->name('bommaterial.store');
    Route::post('bommaterialdelete',[BomController::class, 'Destroy'])->name('bommaterial.destroy');

    Route::post('productpekerjaan/create',[ProductPekerjaanController::class, 'store'])->name('productpekerjaan.store');
    Route::delete('productpekerjaan/{id}', [ProductPekerjaanController::class, 'destroy'])->name('productpekerjaan.destroy');
    Route::post('productpekerjaandetail/create',[ProductPekerjaanController::class, 'storeDetail'])->name('productpekerjaan.storedetail');
    Route::delete('productpekerjaandetail/{id}', [ProductPekerjaanController::class, 'destroyDetail'])->name('productpekerjaan.destroydetail');
    Route::get('productpekerjaan/{id}', [ProductPekerjaanController::class, 'ambil'])->name('productpekerjaan.ambil');

    Route::get('projectproduct/{id}/{type}', [ProjectProductController::class, 'ambil'])->name('projectproduct.ambil');

    Route::get('users', [UserController::class, 'Index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'Create'])->name('users.create');
    Route::post('users', [UserController::class, 'Store'])->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'Edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'Update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'Destroy'])->name('users.destroy');
    Route::get('users/{user}/profile', [UserController::class, 'Profile'])->name('users.profile');
    Route::put('users/{user}/profile', [UserController::class, 'UpdateProfile'])->name('users.updateProfile');
    Route::put('users/{user}/password', [UserController::class, 'UpdatePassword'])->name('users.updatePassword');


    Route::get('roles', [RoleController::class, 'Index'])->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'Create'])->name('roles.create');
    Route::post('roles', [RoleController::class, 'Store'])->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'Edit'])->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'Update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'Destroy'])->name('roles.destroy');

    Route::get('banners', [BannerController::class, 'Index'])->name('banners.index');
    Route::get('banners/create', [BannerController::class, 'Create'])->name('banners.create');
    Route::post('banners', [BannerController::class, 'Store'])->name('banners.store');
    Route::get('banners/{banner}/edit', [BannerController::class, 'Edit'])->name('banners.edit');
    Route::put('banners/{banner}', [BannerController::class, 'Update'])->name('banners.update');
    Route::delete('banners/{banner}', [BannerController::class, 'Destroy'])->name('banners.destroy');

    Route::get('blogs', [BlogController::class, 'Index'])->name('blogs.index');
    Route::get('blogs/create', [BlogController::class, 'Create'])->name('blogs.create');
    Route::post('blogs', [BlogController::class, 'Store'])->name('blogs.store');
    Route::get('blogs/{blog}/edit', [BlogController::class, 'Edit'])->name('blogs.edit');
    Route::put('blogs/{blog}', [BlogController::class, 'Update'])->name('blogs.update');
    Route::delete('blogs/{blog}', [BlogController::class, 'Destroy'])->name('blogs.destroy');

    Route::get('pekerjaan', [PekerjaanController::class, 'Index'])->name('pekerjaan.index');
    Route::get('pekerjaan/create', [PekerjaanController::class, 'Create'])->name('pekerjaan.create');
    Route::post('pekerjaan', [PekerjaanController::class, 'Store'])->name('pekerjaans.store');
    Route::get('pekerjaan/{pekerjaan}/edit', [PekerjaanController::class, 'Edit'])->name('pekerjaan.edit');
    Route::get('pekerjaan/{pekerjaan}/detail', [PekerjaanController::class, 'Detail'])->name('pekerjaan.detail');
    Route::put('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'Update'])->name('pekerjaans.update');
    Route::delete('pekerjaan/{pekerjaan}', [PekerjaanController::class, 'Destroy'])->name('pekerjaans.destroy');

    Route::post('pekerjaandetail', [PekerjaanController::class, 'StoreDetail'])->name('pekerjaan.details.store');
    Route::delete('pekerjaandetail/{pekerjaan}', [PekerjaanController::class, 'DestroyDetail'])->name('pekerjaan.details.destroy');
    Route::get('pekerjaan/{pekerjaan}/detailjson', [PekerjaanController::class, 'DetailJson'])->name('pekerjaan.indexjson');


    Route::get('clients', [ClientController::class, 'Index'])->name('clients.index');
    Route::get('clients/create', [ClientController::class, 'Create'])->name('clients.create');
    Route::post('clients', [ClientController::class, 'Store'])->name('clients.store');
    Route::get('clients/{client}/edit', [ClientController::class, 'Edit'])->name('clients.edit');
    Route::put('clients/{client}', [ClientController::class, 'Update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientController::class, 'Destroy'])->name('clients.destroy');


    
    Route::get('projects/create', [ProjectController::class, 'Create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'Store'])->name('projects.store');
    Route::get('projects/{project}/edit', [ProjectController::class, 'Edit'])->name('projects.edit');
    Route::get('projects/{project}/upload', [ProjectController::class, 'Upload'])->name('projects.upload');
    Route::put('projects/{project}', [ProjectController::class, 'Update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectController::class, 'Destroy'])->name('projects.destroy');

    Route::get('suppliers', [SupplierController::class, 'Index'])->name('suppliers.index');
    Route::get('suppliers/create', [SupplierController::class, 'Create'])->name('suppliers.create');
    Route::post('suppliers', [SupplierController::class, 'Store'])->name('suppliers.store');
    Route::post('suppliermaterials', [SupplierController::class, 'StoreMaterials'])->name('suppliers.materials.store');
    Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'Edit'])->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'Update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'Destroy'])->name('suppliers.destroy');
    
    
    Route::get('suppliers/{supplier}/material', [SupplierController::class, 'material'])->name('suppliers.material');
    Route::delete('suppliermaterials/{supplier}', [SupplierController::class, 'DestroyMaterial'])->name('suppliers.materials.destroy');
    Route::get('suppliersmaterial/{supplier}', [SupplierController::class, 'materialjson'])->name('suppliers.materialjson');

    Route::get('materials', [MaterialController::class, 'Index'])->name('materials.index');
    Route::get('materials/create', [MaterialController::class, 'Create'])->name('materials.create');
    Route::post('materials', [MaterialController::class, 'Store'])->name('materials.store');
    Route::get('materials/{material}/edit', [MaterialController::class, 'Edit'])->name('materials.edit');
    Route::put('materials/{material}', [MaterialController::class, 'Update'])->name('materials.update');
    Route::delete('materials/{material}', [MaterialController::class, 'Destroy'])->name('materials.destroy');

    Route::get('generaterab/{projectid}/{dana}', [AnggaranController::class, 'distribusiDanaUpdate'])->name('generate.index');

   

});



    Route::get('anggaran', [AnggaranController::class, 'Index'])->name('anggarans.index');

    Route::get('anggaranfront', [AnggaranController::class, 'IndexFront'])->name('anggarans.front');

    Route::post('anggaranpekerjaan', [AnggaranController::class, 'anggaranpekerjaan'])->name('anggaran.pekerjaan');
    Route::get('projectpekerjaan/{id}', [AnggaranController::class, 'projectpekerjaan'])->name('anggaran.projectpekerjaan');

    Route::get('projectrabawal/{id}', [AnggaranController::class, 'projectrabawal'])->name('anggaran.projectrabawal');
    Route::get('projectrabkedua/{id}', [AnggaranController::class, 'projectrabkedua'])->name('anggaran.projectrabkedua');
    Route::get('projectrabfinal/{id}', [AnggaranController::class, 'projectrabfinal'])->name('anggaran.projectrabfinal');
    Route::get('anggarandelete/{tambahan}/{id}', [AnggaranController::class, 'anggarandelete'])->name('anggaran.delete');
    Route::get('anggaranpekerjaandelete/{id}', [AnggaranController::class, 'anggaranpekerjaandelete'])->name('anggaran.pekerjaandelete');
    Route::post('anggarandetail', [AnggaranController::class, 'anggarandetail'])->name('anggaran.detail');


    Route::get('projects', [ProjectController::class, 'Index'])->name('projects.index');

    Route::post('/upload-image', [ImageController::class, 'store'])->name('upload.image');
    Route::post('/annotations', [AnnotationController::class, 'store']);

    Route::get('/projects/{id}/export-rab', [AnggaranController::class, 'exportRAB']);
    Route::get('/hasildashboard', [DashboardController::class, 'index']);
    Route::get('/hasildashboardchart', [DashboardController::class, 'grafik']);


    Route::get('/send-test-email', function () {
        Mail::to('trisinggih.jnet@gmail.com')->send(new TestEmail());
        return 'Email berhasil dikirim!';
    });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
