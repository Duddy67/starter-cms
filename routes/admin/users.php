<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\User\RoleController;
use App\Http\Controllers\Admin\User\PermissionController;
use App\Http\Controllers\Admin\User\GroupController;

// Groups
Route::delete('/users/groups', [GroupController::class, 'massDestroy'])->name('admin.users.groups.massDestroy');
Route::get('/users/groups/batch', [GroupController::class, 'batch'])->name('admin.users.groups.batch');
Route::put('/users/groups/batch', [GroupController::class, 'massUpdate'])->name('admin.users.groups.massUpdate');
Route::get('/users/groups/cancel/{group?}', [GroupController::class, 'cancel'])->name('admin.users.groups.cancel');
Route::put('/users/groups/checkin', [GroupController::class, 'massCheckIn'])->name('admin.users.groups.massCheckIn');
Route::resource('users/groups', GroupController::class, ['as' => 'admin.users'])->except(['show']);
// Roles
Route::delete('/users/roles', [RoleController::class, 'massDestroy'])->name('admin.users.roles.massDestroy');
Route::get('/users/roles/cancel/{role?}', [RoleController::class, 'cancel'])->name('admin.users.roles.cancel');
Route::put('/users/roles/checkin', [RoleController::class, 'massCheckIn'])->name('admin.users.roles.massCheckIn');
Route::resource('users/roles', RoleController::class, ['as' => 'admin.users'])->except(['show']);
// Permissions
Route::get('/users/permissions', [PermissionController::class, 'index'])->name('admin.users.permissions.index');
Route::patch('/users/permissions', [PermissionController::class, 'build'])->name('admin.users.permissions.build');
Route::put('/users/permissions', [PermissionController::class, 'rebuild'])->name('admin.users.permissions.rebuild');
// User
Route::delete('/users', [UserController::class, 'massDestroy'])->name('admin.users.massDestroy');
Route::get('/users/batch', [UserController::class, 'batch'])->name('admin.users.batch');
Route::put('/users/batch', [UserController::class, 'massUpdate'])->name('admin.users.massUpdate');
Route::get('/users/cancel/{user?}', [UserController::class, 'cancel'])->name('admin.users.cancel');
Route::put('/users/checkin', [UserController::class, 'massCheckIn'])->name('admin.users.massCheckIn');
Route::delete('/users/{user}/delete-photo', [UserController::class, 'deletePhoto'])->name('admin.users.deletePhoto');
Route::resource('users', UserController::class, ['as' => 'admin'])->except(['show']);
