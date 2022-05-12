<?php

use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\Menu\ItemController;

// Menu 
Route::delete('/menus', [MenuController::class, 'massDestroy'])->name('admin.menus.massDestroy');
Route::get('/menus/cancel/{menu?}', [MenuController::class, 'cancel'])->name('admin.menus.cancel');
Route::put('/menus/checkin', [MenuController::class, 'massCheckIn'])->name('admin.menus.massCheckIn');
Route::put('/menus/publish', [MenuController::class, 'massPublish'])->name('admin.menus.massPublish');
Route::put('/menus/unpublish', [MenuController::class, 'massUnpublish'])->name('admin.menus.massUnpublish');
Route::resource('menus', MenuController::class, ['as' => 'admin'])->except(['show']);
// Menu Item
Route::delete('/{code}/menu/items', [ItemController::class, 'massDestroy'])->name('admin.menu.items.massDestroy');
Route::get('/{code}/menu/items/cancel/{item?}', [ItemController::class, 'cancel'])->name('admin.menu.items.cancel');
Route::put('/{code}/menu/items/checkin', [ItemController::class, 'massCheckIn'])->name('admin.menu.items.massCheckIn');
Route::put('/{code}/menu/items/publish', [ItemController::class, 'massPublish'])->name('admin.menu.items.massPublish');
Route::put('/{code}/menu/items/unpublish', [ItemController::class, 'massUnpublish'])->name('admin.menu.items.massUnpublish');
Route::get('/{code}/menu/items/{item}/up', [ItemController::class, 'up'])->name('admin.menu.items.up');
Route::get('/{code}/menu/items/{item}/down', [ItemController::class, 'down'])->name('admin.menu.items.down');
Route::get('/{code}/menu/items', [ItemController::class, 'index'])->name('admin.menu.items.index');
Route::get('/{code}/menu/items/create', [ItemController::class, 'create'])->name('admin.menu.items.create');
Route::get('/{code}/menu/items/{item}/edit', [ItemController::class, 'edit'])->name('admin.menu.items.edit');
Route::post('/{code}/menu/items', [ItemController::class, 'store'])->name('admin.menu.items.store');
Route::put('/{code}/menu/items/{item}', [ItemController::class, 'update'])->name('admin.menu.items.update');
Route::delete('/{code}/menu/items/{item}', [ItemController::class, 'destroy'])->name('admin.menu.items.destroy');
