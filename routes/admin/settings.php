<?php

use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\EmailController;

// General
Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
Route::patch('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
// Emails
Route::delete('/emails', [EmailController::class, 'massDestroy'])->name('admin.emails.massDestroy');
Route::get('/emails/cancel/{email?}', [EmailController::class, 'cancel'])->name('admin.emails.cancel');
Route::put('/emails/checkin', [EmailController::class, 'massCheckIn'])->name('admin.emails.massCheckIn');
Route::resource('emails', EmailController::class, ['as' => 'admin'])->except(['show']);
