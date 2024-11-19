<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::prefix('v1/')->group(function () {
    
    Route::post('/register',[UserAuthController::class,'createUser']);
    Route::post('/login',[UserAuthController::class,'loginUser']);

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::post('/create', [CategoryController::class, 'store']);
        Route::get('/show_list', [CategoryController::class, 'index']);
        Route::post('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/delete-category/{id}', [CategoryController::class, 'destroy']);
       
        //blog post
        Route::apiResource('blogs', BlogPostController::class);
        Route::post('/blog/{id}', [BlogPostController::class, 'update']);
        Route::put('blogs/status_role_change/{id}', [BlogPostController::class, 'toggleStatus']);

    });
   

    Route::middleware(['auth:sanctum'])->group(function(){
        Route::post('/logout',[UserAuthController::class,'logout'])
        ->name('logout');
    });

    //Blog view post
    Route::get('/show_blog', [BlogController::class, 'index']);
    Route::get('/single_blog_view/{id}', [BlogController::class, 'single_post_view']);
    Route::get('/filter_category/{id}', [BlogController::class, 'filter_by_category']);


});


//Route::get('/show_blog', [BlogController::class, 'index']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

