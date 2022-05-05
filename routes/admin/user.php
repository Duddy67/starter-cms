<?php

use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\User\RoleController;
use App\Http\Controllers\Admin\User\PermissionController;
use App\Http\Controllers\Admin\User\GroupController;

// User
Route::delete('/users', [UserController::class, 'massDestroy'])->name('admin.user.users.massDestroy');
Route::get('/users/batch', [UserController::class, 'batch'])->name('admin.user.users.batch');
Route::put('/users/batch', [UserController::class, 'massUpdate'])->name('admin.user.users.massUpdate');
Route::get('/users/cancel/{user?}', [UserController::class, 'cancel'])->name('admin.user.users.cancel');
Route::put('/users/checkin', [UserController::class, 'massCheckIn'])->name('admin.user.users.massCheckIn');
Route::resource('users', UserController::class, ['as' => 'admin.user'])->except(['show']);
// Groups
Route::delete('/groups', [GroupController::class, 'massDestroy'])->name('admin.user.groups.massDestroy');
Route::get('/groups/batch', [GroupController::class, 'batch'])->name('admin.user.groups.batch');
Route::put('/groups/batch', [GroupController::class, 'massUpdate'])->name('admin.user.groups.massUpdate');
Route::get('/groups/cancel/{group?}', [GroupController::class, 'cancel'])->name('admin.user.groups.cancel');
Route::put('/groups/checkin', [GroupController::class, 'massCheckIn'])->name('admin.user.groups.massCheckIn');
Route::resource('groups', GroupController::class, ['as' => 'admin.user'])->except(['show']);
// Roles
Route::delete('/roles', [RoleController::class, 'massDestroy'])->name('admin.user.roles.massDestroy');
Route::get('/roles/cancel/{role?}', [RoleController::class, 'cancel'])->name('admin.user.roles.cancel');
Route::put('/roles/checkin', [RoleController::class, 'massCheckIn'])->name('admin.user.roles.massCheckIn');
Route::resource('roles', RoleController::class, ['as' => 'admin.user'])->except(['show']);
// Permissions
Route::get('/permissions', [PermissionController::class, 'index'])->name('admin.user.permissions.index');
Route::patch('/permissions', [PermissionController::class, 'build'])->name('admin.user.permissions.build');
Route::put('/permissions', [PermissionController::class, 'rebuild'])->name('admin.user.permissions.rebuild');
