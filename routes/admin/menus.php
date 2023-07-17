<?php

use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\Menu\ItemController;

// Menu Item
Route::delete('/menus/{code}/items', [ItemController::class, 'massDestroy'])->name('admin.menus.items.massDestroy');
Route::get('/menus/{code}/items/cancel/{item?}', [ItemController::class, 'cancel'])->name('admin.menus.items.cancel');
Route::put('/menus/{code}/items/checkin', [ItemController::class, 'massCheckIn'])->name('admin.menus.items.massCheckIn');
Route::put('/menus/{code}/items/publish', [ItemController::class, 'massPublish'])->name('admin.menus.items.massPublish');
Route::put('/menus/{code}/items/unpublish', [ItemController::class, 'massUnpublish'])->name('admin.menus.items.massUnpublish');
Route::get('/menus/{code}/items/{item}/up', [ItemController::class, 'up'])->name('admin.menus.items.up');
Route::get('/menus/{code}/items/{item}/down', [ItemController::class, 'down'])->name('admin.menus.items.down');
Route::get('/menus/{code}/items', [ItemController::class, 'index'])->name('admin.menus.items.index');
Route::get('/menus/{code}/items/create', [ItemController::class, 'create'])->name('admin.menus.items.create');
Route::get('/menus/{code}/items/{item}/edit', [ItemController::class, 'edit'])->name('admin.menus.items.edit');
Route::post('/menus/{code}/items', [ItemController::class, 'store'])->name('admin.menus.items.store');
Route::put('/menus/{code}/items/{item}', [ItemController::class, 'update'])->name('admin.menus.items.update');
Route::delete('/menus/{code}/items/{item}', [ItemController::class, 'destroy'])->name('admin.menus.items.destroy');
// Menu 
Route::delete('/menus', [MenuController::class, 'massDestroy'])->name('admin.menus.massDestroy');
Route::get('/menus/cancel/{menu?}', [MenuController::class, 'cancel'])->name('admin.menus.cancel');
Route::put('/menus/checkin', [MenuController::class, 'massCheckIn'])->name('admin.menus.massCheckIn');
Route::put('/menus/publish', [MenuController::class, 'massPublish'])->name('admin.menus.massPublish');
Route::put('/menus/unpublish', [MenuController::class, 'massUnpublish'])->name('admin.menus.massUnpublish');
Route::resource('menus', MenuController::class, ['as' => 'admin'])->except(['show']);
