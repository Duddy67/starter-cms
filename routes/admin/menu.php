<?php

use App\Http\Controllers\Admin\Menu\MenuController;
use App\Http\Controllers\Admin\Menu\MenuItemController;

// Menu 
Route::delete('/menus', [MenuController::class, 'massDestroy'])->name('admin.menu.menus.massDestroy');
Route::get('/menus/cancel/{menu?}', [MenuController::class, 'cancel'])->name('admin.menu.menus.cancel');
Route::put('/menus/checkin', [MenuController::class, 'massCheckIn'])->name('admin.menu.menus.massCheckIn');
Route::put('/menus/publish', [MenuController::class, 'massPublish'])->name('admin.menu.menus.massPublish');
Route::put('/menus/unpublish', [MenuController::class, 'massUnpublish'])->name('admin.menu.menus.massUnpublish');
Route::resource('menus', MenuController::class, ['as' => 'admin.menu'])->except(['show']);
// Menu Item
Route::delete('/{code}/menuitems', [MenuItemController::class, 'massDestroy'])->name('admin.menu.menuitems.massDestroy');
Route::get('/{code}/menuitems/cancel/{menuItem?}', [MenuItemController::class, 'cancel'])->name('admin.menu.menuitems.cancel');
Route::put('/{code}/menuitems/checkin', [MenuItemController::class, 'massCheckIn'])->name('admin.menu.menuitems.massCheckIn');
Route::put('/{code}/menuitems/publish', [MenuItemController::class, 'massPublish'])->name('admin.menu.menuitems.massPublish');
Route::put('/{code}/menuitems/unpublish', [MenuItemController::class, 'massUnpublish'])->name('admin.menu.menuitems.massUnpublish');
Route::get('/{code}/menuitems/{menuItem}/up', [MenuItemController::class, 'up'])->name('admin.menu.menuitems.up');
Route::get('/{code}/menuitems/{menuItem}/down', [MenuItemController::class, 'down'])->name('admin.menu.menuitems.down');
Route::get('/{code}/menuitems', [MenuItemController::class, 'index'])->name('admin.menu.menuitems.index');
Route::get('/{code}/menuitems/create', [MenuItemController::class, 'create'])->name('admin.menu.menuitems.create');
Route::get('/{code}/menuitems/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('admin.menu.menuitems.edit');
Route::post('/{code}/menuitems', [MenuItemController::class, 'store'])->name('admin.menu.menuitems.store');
Route::put('/{code}/menuitems/{menuItem}', [MenuItemController::class, 'update'])->name('admin.menu.menuitems.update');
Route::delete('/{code}/menuitems/{menuItem}', [MenuItemController::class, 'destroy'])->name('admin.menu.menuitems.destroy');
