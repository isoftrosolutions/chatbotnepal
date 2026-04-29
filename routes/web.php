<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientLinkController;
use App\Http\Controllers\Admin\ConversationController as AdminConversationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmbedScriptController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UsageController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Client\ConversationController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\EmbedController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\UpdateRequestController;
use App\Http\Controllers\Client\VisitorsController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.dashboard');
    }

    $demoSiteId = User::where('role', 'client')
        ->where('chatbot_enabled', true)
        ->whereNotNull('site_id')
        ->value('site_id');

    return view('welcome', ['demoSiteId' => $demoSiteId]);
});

Route::get('/privacy-policy', fn() => view('privacy-policy'))->name('privacy-policy');
Route::get('/terms', fn() => view('terms'))->name('terms');

Route::get('/sitemap.xml', function () {
    $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" .
        '  <url><loc>https://chatbotnepal.com/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>' . "\n" .
        '</urlset>';
    return response($content, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('auth/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::get('login', [AuthenticatedSessionController::class, 'create']);
    Route::post('auth/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
    Route::get('password-reset-success', function (\Illuminate\Http\Request $request) {
        $redirect = $request->query('redirect', route('client.dashboard'));
        return view('auth.password-reset-success', ['redirectUrl' => $redirect]);
    })->name('password.success');
});

Route::middleware('auth')->group(function () {
    Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profile (accessible to all authenticated users — both admin and client)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/email/verify', [ProfileController::class, 'emailVerifyForm'])->name('profile.email.verify.form');
    Route::post('/profile/email/verify', [ProfileController::class, 'emailVerify'])->name('profile.email.verify');
    Route::get('/profile/password', [ProfileController::class, 'passwordEdit'])->name('profile.password.edit');
    Route::patch('/profile/password', [ProfileController::class, 'passwordUpdate'])->name('profile.password.update');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('admin.clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('admin.clients.store');
        Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('admin.clients.edit');
        Route::put('/clients/{id}', [ClientController::class, 'update'])->name('admin.clients.update');
        Route::delete('/clients/{id}', [ClientController::class, 'destroy'])->name('admin.clients.destroy');
        Route::post('/clients/{id}/toggle', [ClientController::class, 'toggle'])->name('admin.clients.toggle');

        Route::get('/embed-scripts', [EmbedScriptController::class, 'index'])->name('admin.embed-scripts');
        Route::get('/embed-scripts/{id}', [EmbedScriptController::class, 'show'])->name('admin.embed-scripts.show');

        Route::get('/knowledge-base', [KnowledgeBaseController::class, 'overview'])->name('admin.knowledge-base');

        Route::get('/clients/{clientId}/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('admin.clients.knowledge-base');
        Route::post('/clients/{clientId}/knowledge-base', [KnowledgeBaseController::class, 'store'])->name('admin.clients.knowledge-base.store');
        Route::put('/clients/{clientId}/knowledge-base/{kbId}', [KnowledgeBaseController::class, 'update'])->name('admin.clients.knowledge-base.update');
        Route::delete('/clients/{clientId}/knowledge-base/{kbId}', [KnowledgeBaseController::class, 'destroy'])->name('admin.clients.knowledge-base.destroy');
        Route::post('/clients/{clientId}/knowledge-base/{kbId}/toggle', [KnowledgeBaseController::class, 'toggleActive'])->name('admin.clients.knowledge-base.toggle');
        Route::post('/clients/{clientId}/knowledge-base/reorder', [KnowledgeBaseController::class, 'reorder'])->name('admin.clients.knowledge-base.reorder');

        Route::get('/clients/{clientId}/conversations', [AdminConversationController::class, 'index'])->name('admin.clients.conversations');
        Route::get('/clients/{clientId}/conversations/{conversationId}', [AdminConversationController::class, 'show'])->name('admin.clients.conversations.show');

        Route::get('/clients/{clientId}/usage', [UsageController::class, 'clientUsage'])->name('admin.clients.usage');

        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::post('/invoices', [AdminInvoiceController::class, 'create'])->name('admin.invoices.store');
        Route::post('/invoices/{id}/mark-paid', [AdminInvoiceController::class, 'markPaid'])->name('admin.invoices.mark-paid');
        Route::delete('/invoices/{id}', [AdminInvoiceController::class, 'destroy'])->name('admin.invoices.destroy');

        Route::get('/usage', [UsageController::class, 'index'])->name('admin.usage');
        Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });

    Route::prefix('client')->middleware('client')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('client.dashboard');

        Route::get('/conversations', [ConversationController::class, 'index'])->name('client.conversations');
        Route::get('/conversations/{id}', [ConversationController::class, 'show'])->name('client.conversations.show');

        Route::get('/visitors', [VisitorsController::class, 'index'])->name('client.visitors');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('client.invoices');
        Route::get('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('client.invoices.pay');
        Route::get('/invoices/{invoice}/callback', [InvoiceController::class, 'callback'])->name('client.invoices.callback');

        Route::get('/embed-code', [EmbedController::class, 'index'])->name('client.embed-code');
        Route::post('/embed-code', [EmbedController::class, 'updateConfig'])->name('client.embed-code.update');

        Route::get('/request-update', [UpdateRequestController::class, 'create'])->name('client.request-update.create');
        Route::post('/request-update', [UpdateRequestController::class, 'store'])->name('client.request-update.store');

    });

    // Links management (for clients to manage their own links)
    Route::resource('admin/links', ClientLinkController::class);
    Route::post('admin/links/{link}/toggle', [ClientLinkController::class, 'toggle']);
    Route::post('admin/links/reorder', [ClientLinkController::class, 'reorder']);

});
