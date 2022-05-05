<?php

use App\Http\Controllers\Admin\Blog\PostController as AdminPostController;
use App\Http\Controllers\Admin\Blog\CategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\Blog\SettingController as AdminBlogSettingController;

// Posts
Route::delete('/posts', [AdminPostController::class, 'massDestroy'])->name('admin.blog.posts.massDestroy');
Route::get('/posts/batch', [AdminPostController::class, 'batch'])->name('admin.blog.posts.batch');
Route::put('/posts/batch', [AdminPostController::class, 'massUpdate'])->name('admin.blog.posts.massUpdate');
Route::get('/posts/cancel/{post?}', [AdminPostController::class, 'cancel'])->name('admin.blog.posts.cancel');
Route::put('/posts/checkin', [AdminPostController::class, 'massCheckIn'])->name('admin.blog.posts.massCheckIn');
Route::put('/posts/publish', [AdminPostController::class, 'massPublish'])->name('admin.blog.posts.massPublish');
Route::put('/posts/unpublish', [AdminPostController::class, 'massUnpublish'])->name('admin.blog.posts.massUnpublish');
Route::get('/posts/{post}/edit/{tab?}', [AdminPostController::class, 'edit'])->name('admin.blog.posts.edit');
Route::resource('posts', AdminPostController::class, ['as' => 'admin.blog'])->except(['show', 'edit']);
// Categories
Route::delete('/categories', [AdminBlogCategoryController::class, 'massDestroy'])->name('admin.blog.categories.massDestroy');
Route::get('/categories/cancel/{category?}', [AdminBlogCategoryController::class, 'cancel'])->name('admin.blog.categories.cancel');
Route::put('/categories/checkin', [AdminBlogCategoryController::class, 'massCheckIn'])->name('admin.blog.categories.massCheckIn');
Route::put('/categories/publish', [AdminBlogCategoryController::class, 'massPublish'])->name('admin.blog.categories.massPublish');
Route::put('/categories/unpublish', [AdminBlogCategoryController::class, 'massUnpublish'])->name('admin.blog.categories.massUnpublish');
Route::get('/categories/{category}/up', [AdminBlogCategoryController::class, 'up'])->name('admin.blog.categories.up');
Route::get('/categories/{category}/down', [AdminBlogCategoryController::class, 'down'])->name('admin.blog.categories.down');
Route::get('/categories/{category}/edit/{tab?}', [AdminBlogCategoryController::class, 'edit'])->name('admin.blog.categories.edit');
Route::resource('categories', AdminBlogCategoryController::class, ['as' => 'admin.blog'])->except(['show', 'edit']);
// Settings
Route::get('/settings/{tab?}', [AdminBlogSettingController::class, 'index'])->name('admin.blog.settings.index');
Route::patch('/settings', [AdminBlogSettingController::class, 'update'])->name('admin.blog.settings.update');
