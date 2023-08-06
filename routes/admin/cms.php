<?php

use App\Http\Controllers\Admin\Cms\SettingController;
use App\Http\Controllers\Admin\Cms\EmailController;

//Settings 
Route::get('/cms/settings', [SettingController::class, 'index'])->name('admin.cms.settings.index');
Route::patch('/cms/settings', [SettingController::class, 'update'])->name('admin.cms.settings.update');
// Emails
Route::delete('/cms/emails', [EmailController::class, 'massDestroy'])->name('admin.cms.emails.massDestroy');
Route::get('/cms/emails/cancel/{email?}', [EmailController::class, 'cancel'])->name('admin.cms.emails.cancel');
Route::get('/cms/emails/test', [EmailController::class, 'test'])->name('admin.cms.emails.test');
Route::put('/cms/emails/checkin', [EmailController::class, 'massCheckIn'])->name('admin.cms.emails.massCheckIn');
Route::resource('cms/emails', EmailController::class, ['as' => 'admin.cms'])->except(['show']);
