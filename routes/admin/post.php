<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\Post\CategoryController as AdminPostCategoryController;
use App\Http\Controllers\Admin\Post\SettingController as AdminPostSettingController;

// Posts
Route::delete('/posts', [AdminPostController::class, 'massDestroy'])->name('admin.posts.massDestroy');
Route::get('/posts/batch', [AdminPostController::class, 'batch'])->name('admin.posts.batch');
Route::put('/posts/batch', [AdminPostController::class, 'massUpdate'])->name('admin.posts.massUpdate');
Route::get('/posts/cancel/{post?}', [AdminPostController::class, 'cancel'])->name('admin.posts.cancel');
Route::put('/posts/checkin', [AdminPostController::class, 'massCheckIn'])->name('admin.posts.massCheckIn');
Route::put('/posts/publish', [AdminPostController::class, 'massPublish'])->name('admin.posts.massPublish');
Route::put('/posts/unpublish', [AdminPostController::class, 'massUnpublish'])->name('admin.posts.massUnpublish');
Route::get('/post/{post}/up', [AdminPostController::class, 'up'])->name('admin.posts.up');
Route::get('/post/{post}/down', [AdminPostController::class, 'down'])->name('admin.posts.down');
Route::get('/posts/{post}/edit', [AdminPostController::class, 'edit'])->name('admin.posts.edit');
Route::get('/posts/{post}/layout', [AdminPostController::class, 'layout'])->name('admin.posts.layout');
Route::delete('/posts/{post}/delete-layout-item', [AdminPostController::class, 'deleteLayoutItem'])->name('admin.posts.deleteLayoutItem');
Route::delete('/posts/{post}/delete-image', [AdminPostController::class, 'deleteImage'])->name('admin.posts.deleteImage');
Route::resource('posts', AdminPostController::class, ['as' => 'admin'])->except(['show', 'edit']);
// Categories
Route::delete('/post/categories', [AdminPostCategoryController::class, 'massDestroy'])->name('admin.post.categories.massDestroy');
Route::get('/post/categories/cancel/{category?}', [AdminPostCategoryController::class, 'cancel'])->name('admin.post.categories.cancel');
Route::put('/post/categories/checkin', [AdminPostCategoryController::class, 'massCheckIn'])->name('admin.post.categories.massCheckIn');
Route::put('/post/categories/publish', [AdminPostCategoryController::class, 'massPublish'])->name('admin.post.categories.massPublish');
Route::put('/post/categories/unpublish', [AdminPostCategoryController::class, 'massUnpublish'])->name('admin.post.categories.massUnpublish');
Route::get('/post/categories/{category}/up', [AdminPostCategoryController::class, 'up'])->name('admin.post.categories.up');
Route::get('/post/categories/{category}/down', [AdminPostCategoryController::class, 'down'])->name('admin.post.categories.down');
Route::get('/post/categories/{category}/edit', [AdminPostCategoryController::class, 'edit'])->name('admin.post.categories.edit');
Route::delete('/post/categories/{category}/delete-image', [AdminPostCategoryController::class, 'deleteImage'])->name('admin.post.categories.deleteImage');
Route::resource('post/categories', AdminPostCategoryController::class, ['as' => 'admin.post'])->except(['show', 'edit']);
// Settings
Route::get('/post/settings', [AdminPostSettingController::class, 'index'])->name('admin.post.settings.index');
Route::patch('/post/settings', [AdminPostSettingController::class, 'update'])->name('admin.post.settings.update');
