<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\User\RoleController;
use App\Http\Controllers\Admin\User\PermissionController;
use App\Http\Controllers\Admin\User\GroupController;

// User
Route::delete('/users', [UserController::class, 'massDestroy'])->name('admin.users.massDestroy');
Route::get('/users/batch', [UserController::class, 'batch'])->name('admin.users.batch');
Route::put('/users/batch', [UserController::class, 'massUpdate'])->name('admin.users.massUpdate');
Route::get('/users/cancel/{user?}', [UserController::class, 'cancel'])->name('admin.users.cancel');
Route::put('/users/checkin', [UserController::class, 'massCheckIn'])->name('admin.users.massCheckIn');
Route::delete('/users/{user}/delete-photo', [UserController::class, 'deletePhoto'])->name('admin.users.deletePhoto');
Route::resource('users', UserController::class, ['as' => 'admin'])->except(['show']);
// Groups
Route::delete('/user/groups', [GroupController::class, 'massDestroy'])->name('admin.user.groups.massDestroy');
Route::get('/user/groups/batch', [GroupController::class, 'batch'])->name('admin.user.groups.batch');
Route::put('/user/groups/batch', [GroupController::class, 'massUpdate'])->name('admin.user.groups.massUpdate');
Route::get('/user/groups/cancel/{group?}', [GroupController::class, 'cancel'])->name('admin.user.groups.cancel');
Route::put('/user/groups/checkin', [GroupController::class, 'massCheckIn'])->name('admin.user.groups.massCheckIn');
Route::resource('user/groups', GroupController::class, ['as' => 'admin.user'])->except(['show']);
// Roles
Route::delete('/user/roles', [RoleController::class, 'massDestroy'])->name('admin.user.roles.massDestroy');
Route::get('/user/roles/cancel/{role?}', [RoleController::class, 'cancel'])->name('admin.user.roles.cancel');
Route::put('/user/roles/checkin', [RoleController::class, 'massCheckIn'])->name('admin.user.roles.massCheckIn');
Route::resource('user/roles', RoleController::class, ['as' => 'admin.user'])->except(['show']);
// Permissions
Route::get('/user/permissions', [PermissionController::class, 'index'])->name('admin.user.permissions.index');
Route::patch('/user/permissions', [PermissionController::class, 'build'])->name('admin.user.permissions.build');
Route::put('/user/permissions', [PermissionController::class, 'rebuild'])->name('admin.user.permissions.rebuild');
