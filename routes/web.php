<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SatCatalogController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuxiliarController;

Route::get('/', function () {
    return view('auth.login');
});

// Ruta secreta para importar catálogos sin usar la consola
Route::get('/importar-catalogo-secreto', function () {
    try {
        Artisan::call('import:sat-product-keys');
        return "¡Comando ejecutado! Resultado: " . Artisan::output();
    } catch (\Exception $e) {
        return "Hubo un error: " . $e->getMessage();
    }
});

Route::get('/dashboard', [CompanyController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// =========================================================================
// 👑 RUTAS EXCLUSIVAS DEL SUPERADMIN (FASE C) 👑
// =========================================================================
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->group(function () {
    
    // Esta es una ruta modo dios
    Route::get('/panel', [AdminController::class, 'index'])->name('admin.panel');

    // Las dos rutas nuevas para editar licencias:
    Route::get('/clientes/{user}/editar', [AdminController::class, 'edit'])->name('admin.edit');
    Route::put('/clientes/{user}', [AdminController::class, 'update'])->name('admin.update');

});

// =========================================================================
// 🏢 RUTAS GENERALES DEL ERP (DUEÑOS Y AUXILIARES)
// =========================================================================
Route::middleware('auth')->group(function () {
    // Rutas de Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nos aseguramos de que el recurso completo esté disponible
    Route::resource('companies', CompanyController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('products', ProductController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('students', StudentController::class);
    Route::resource('gastos', GastoController::class);
    // Rutas para gestionar Cajeras/Auxiliares (Solo dueños pueden entrar aquí)
    Route::middleware('role:owner')->group(function () {
        Route::resource('auxiliares', AuxiliarController::class)->except(['show']);
    });

    // Rutas de Configuración
    Route::get('/companies/{company}/csd', [CompanyController::class, 'showCsdForm'])->name('companies.csd.form');
    Route::post('/companies/{company}/csd', [CompanyController::class, 'storeCsd'])->name('companies.storeCsd');
    Route::post('/companies/{company}/fiel', [CompanyController::class, 'storeFiel'])->name('companies.fiel.store');
    Route::get('/companies/{company}/logo', [CompanyController::class, 'showLogoForm'])->name('companies.logo.form');
    Route::post('/companies/{company}/logo', [CompanyController::class, 'storeLogo'])->name('companies.logo.store');

    // Rutas de API internas
    Route::get('/api/sat-product-keys/search', [SatCatalogController::class, 'searchProductKeys'])->name('api.sat_product_keys.search');
    Route::get('/api/search-clients', [ClientController::class, 'search'])->name('api.clients.search');
    Route::get('/api/search-products', [ProductController::class, 'search'])->name('api.products.search');
    Route::post('/api/suggest-taxes', [InvoiceController::class, 'suggestTaxes'])->name('api.ai.suggest_taxes');
    Route::post('/webhooks/facturama', [App\Http\Controllers\WebhookController::class, 'handle']);
    
    // Rutas de Factura
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/xml', [InvoiceController::class, 'downloadXml'])->name('invoices.xml');
    Route::delete('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendByEmail'])->name('invoices.email');
    
    // Rutas de Pagos
    Route::get('/invoices/{invoice}/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/pdf', [\App\Http\Controllers\PaymentController::class, 'downloadPdf'])->name('payments.pdf');
    Route::get('/payments/{payment}/xml', [\App\Http\Controllers\PaymentController::class, 'downloadXml'])->name('payments.xml');
    Route::post('/payments/{payment}/email', [\App\Http\Controllers\PaymentController::class, 'sendEmail'])->name('payments.email');
    Route::post('/payments/{payment}/cancel', [\App\Http\Controllers\PaymentController::class, 'cancel'])->name('payments.cancel');
});

require __DIR__.'/auth.php';