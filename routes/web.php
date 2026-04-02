<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Financeiro
    Route::prefix('financeiro')->group(function () {
        Route::get('/contas', function () {
            return view('financeiro.contas');
        })->name('financeiro.contas');

        Route::get('/transacoes', function () {
            return view('financeiro.transacoes');
        })->name('financeiro.transacoes');

        Route::get('/categorias', function () {
            return view('financeiro.categorias');
        })->name('financeiro.categorias');

        Route::get('/cartoes', function () {
            return view('financeiro.cartoes');
        })->name('financeiro.cartoes');

        Route::get('/cartoes/{id}/fatura', function ($id) {
            return view('financeiro.fatura', ['id' => $id]);
        })->name('financeiro.fatura');

        Route::get('/investimentos', function () {
            return view('financeiro.investimentos');
        })->name('financeiro.investimentos');

        Route::get('/importar-ofx', function () {
            return view('financeiro.importar-ofx');
        })->name('financeiro.importar-ofx');
    });

    // Agenda
    Route::get('/agenda', function () {
        return view('agenda.calendario');
    })->name('agenda');

    // Tarefas
    Route::get('/tarefas', function () {
        return view('tarefas.index');
    })->name('tarefas');

    // CRM
    Route::prefix('crm')->group(function () {
        Route::get('/pipeline', function () {
            return view('crm.pipeline');
        })->name('crm.pipeline');

        Route::get('/contatos', function () {
            return view('crm.contatos');
        })->name('crm.contatos');

        Route::get('/negocios/{id}', function ($id) {
            return view('crm.negocio', ['id' => $id]);
        })->name('crm.negocio');

        Route::get('/produtos', function () {
            return view('crm.produtos');
        })->name('crm.produtos');
    });

    // WhatsApp
    Route::prefix('whatsapp')->group(function () {
        Route::get('/instancias', function () {
            return view('whatsapp.instancias');
        })->name('whatsapp.instancias');

        Route::get('/chat/{instanceId?}', function ($instanceId = null) {
            return view('whatsapp.chat', ['instanceId' => $instanceId]);
        })->name('whatsapp.chat');
    });

    // Configuracoes
    Route::get('/configuracoes', function () {
        return view('configuracoes');
    })->name('configuracoes');
});

// WhatsApp Webhook (public route - no auth)
Route::post('/api/whatsapp/webhook', [\App\Http\Controllers\WhatsAppWebhookController::class, 'handle'])
    ->name('whatsapp.webhook');

require __DIR__.'/auth.php';
