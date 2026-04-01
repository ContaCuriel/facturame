<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SatCatalogController;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [CompanyController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Rutas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ --- LÍNEA CLAVE CORREGIDA --- ✅
    // Nos aseguramos de que el recurso completo esté disponible, incluyendo el método 'show' (GET).
    Route::resource('companies', CompanyController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('students', StudentController::class);

    // Rutas de Configuración
    Route::get('/companies/{company}/csd', [CompanyController::class, 'showCsdForm'])->name('companies.csd.form');
    Route::post('/companies/{company}/csd', [CompanyController::class, 'storeCsd'])->name('companies.csd.store');
    Route::get('/companies/{company}/logo', [CompanyController::class, 'showLogoForm'])->name('companies.logo.form');
    Route::post('/companies/{company}/logo', [CompanyController::class, 'storeLogo'])->name('companies.logo.store');

    // Rutas de API internas
    Route::get('/api/sat-product-keys/search', [SatCatalogController::class, 'searchProductKeys'])->name('api.sat_product_keys.search');
    Route::get('/api/search-clients', [ClientController::class, 'search'])->name('api.clients.search');
    Route::get('/api/search-products', [ProductController::class, 'search'])->name('api.products.search');
    Route::post('/api/suggest-taxes', [InvoiceController::class, 'suggestTaxes'])->name('api.ai.suggest_taxes');
    
    // Rutas de Factura
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/xml', [InvoiceController::class, 'downloadXml'])->name('invoices.xml');
    Route::delete('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendByEmail'])->name('invoices.email');
});

require __DIR__.'/auth.php';
