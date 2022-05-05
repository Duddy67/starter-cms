<?php

use App\Http\Controllers\Admin\Settings\GeneralController;
use App\Http\Controllers\Admin\Settings\EmailController;

// General
Route::get('/general', [GeneralController::class, 'index'])->name('admin.settings.general.index');
Route::patch('/general', [GeneralController::class, 'update'])->name('admin.settings.general.update');
// Emails
Route::delete('/emails', [EmailController::class, 'massDestroy'])->name('admin.settings.emails.massDestroy');
Route::get('/emails/cancel/{email?}', [EmailController::class, 'cancel'])->name('admin.settings.emails.cancel');
Route::put('/emails/checkin', [EmailController::class, 'massCheckIn'])->name('admin.settings.emails.massCheckIn');
Route::resource('emails', EmailController::class, ['as' => 'admin.settings'])->except(['show']);
