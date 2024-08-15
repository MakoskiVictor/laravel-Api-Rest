<?php

use App\Http\Controllers\studentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::get('/students', [studentController::class, 'index']);

Route::get('/students/{id}', function ($id) {
    return "student with id = $id";
});

Route::post('/students', [studentController::class, 'store']);

Route::put('/students/{id}', function ($id) {
    return "Student with id = $id updated";
});

Route::delete('/students/{id}', function ($id) {
    return "student $id deleted";
});
