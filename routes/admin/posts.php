<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\Post\CategoryController as AdminPostCategoryController;
use App\Http\Controllers\Admin\Post\SettingController as AdminPostSettingController;

// Categories
Route::delete('/posts/categories', [AdminPostCategoryController::class, 'massDestroy'])->name('admin.posts.categories.massDestroy');
Route::get('/posts/categories/cancel/{category?}', [AdminPostCategoryController::class, 'cancel'])->name('admin.posts.categories.cancel');
Route::put('/posts/categories/checkin', [AdminPostCategoryController::class, 'massCheckIn'])->name('admin.posts.categories.massCheckIn');
Route::put('/posts/categories/publish', [AdminPostCategoryController::class, 'massPublish'])->name('admin.posts.categories.massPublish');
Route::put('/posts/categories/unpublish', [AdminPostCategoryController::class, 'massUnpublish'])->name('admin.posts.categories.massUnpublish');
Route::get('/posts/categories/{category}/up', [AdminPostCategoryController::class, 'up'])->name('admin.posts.categories.up');
Route::get('/posts/categories/{category}/down', [AdminPostCategoryController::class, 'down'])->name('admin.posts.categories.down');
Route::get('/posts/categories/{category}/edit', [AdminPostCategoryController::class, 'edit'])->name('admin.posts.categories.edit');
Route::delete('/posts/categories/{category}/delete-image', [AdminPostCategoryController::class, 'deleteImage'])->name('admin.posts.categories.deleteImage');
Route::resource('posts/categories', AdminPostCategoryController::class, ['as' => 'admin.posts'])->except(['show', 'edit']);
// Settings
Route::get('/posts/settings', [AdminPostSettingController::class, 'index'])->name('admin.posts.settings.index');
Route::patch('/posts/settings', [AdminPostSettingController::class, 'update'])->name('admin.posts.settings.update');
// Posts
Route::delete('/posts', [AdminPostController::class, 'massDestroy'])->name('admin.posts.massDestroy');
Route::get('/posts/batch', [AdminPostController::class, 'batch'])->name('admin.posts.batch');
Route::put('/posts/batch', [AdminPostController::class, 'massUpdate'])->name('admin.posts.massUpdate');
Route::get('/posts/cancel/{post?}', [AdminPostController::class, 'cancel'])->name('admin.posts.cancel');
Route::put('/posts/checkin', [AdminPostController::class, 'massCheckIn'])->name('admin.posts.massCheckIn');
Route::put('/posts/publish', [AdminPostController::class, 'massPublish'])->name('admin.posts.massPublish');
Route::put('/posts/unpublish', [AdminPostController::class, 'massUnpublish'])->name('admin.posts.massUnpublish');
Route::get('/posts/{post}/up', [AdminPostController::class, 'up'])->name('admin.posts.up');
Route::get('/posts/{post}/down', [AdminPostController::class, 'down'])->name('admin.posts.down');
Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('admin.posts.edit');
Route::get('/posts/{post}/layout', [AdminPostController::class, 'layout'])->name('admin.posts.layout');
Route::delete('/posts/{post}/delete-layout-item', [AdminPostController::class, 'deleteLayoutItem'])->name('admin.posts.deleteLayoutItem');
Route::delete('/posts/{post}/delete-image', [AdminPostController::class, 'deleteImage'])->name('admin.posts.deleteImage');
Route::resource('posts', AdminPostController::class, ['as' => 'admin'])->except(['show', 'edit']);
