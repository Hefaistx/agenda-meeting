<?php

use App\Http\Controllers\KalenderController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SimulasiController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KonfigurasiWaktuController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\TopikController;

Route::redirect('/', '/agenda');

Route::get('agenda/check-conflict', [MeetingController::class, 'checkConflict'])->name('agenda.check-conflict');
Route::resource('agenda', MeetingController::class);
Route::post('agenda/{agenda}/upload-nm', [MeetingController::class, 'uploadNm'])->name('agenda.upload-nm');
Route::post('agenda/{agenda}/reschedule', [MeetingController::class, 'reschedule'])->name('agenda.reschedule');
Route::patch('agenda/{agenda}/cancel', [MeetingController::class, 'cancel'])->name('agenda.cancel');

Route::resource('ruangan', RuanganController::class);
Route::resource('topik', TopikController::class);
Route::resource('konfigurasi-waktu', KonfigurasiWaktuController::class);

Route::get('kalender', [KalenderController::class, 'index'])->name('kalender.index');

Route::post('simulasi/login', [SimulasiController::class, 'login'])->name('simulasi.login');
