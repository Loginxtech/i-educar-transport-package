<?php

use iEducar\Packages\Transport\Http\Controllers\AlunoTransporteController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth', 'ieducar.navigation', 'ieducar.footer']], function () {
    // Cadastro de alunos no transporte em lote.
    Route::get('/transporte/aluno/cadastro-em-lote', [AlunoTransporteController::class, 'create'])
        ->name('transporte.aluno-em-lote.create');

    Route::get('/transporte/aluno/cadastro-em-lote/alunos', [AlunoTransporteController::class, 'alunos'])
        ->name('transporte.aluno-em-lote.alunos');

    Route::post('/transporte/aluno/cadastro-em-lote', [AlunoTransporteController::class, 'store'])
        ->name('transporte.aluno-em-lote.store');
});
