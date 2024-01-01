<?php

use App\Http\Controllers\{LoginController,
    LogoutController,
    RegisterController,
    TagController,
    OfficeController,
    OfficeImageController,
    UserController,
    UserReservationController,
    HostReservationController};


Route::get('/tags', TagController::class);

// Offices...
Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{office}', [OfficeController::class, 'show']);
Route::post('/offices', [OfficeController::class, 'create'])->middleware(['auth:sanctum', 'verified']);
Route::put('/offices/{office}', [OfficeController::class, 'update'])->middleware(['auth:sanctum', 'verified']);
Route::delete('/offices/{office}', [OfficeController::class, 'delete'])->middleware(['auth:sanctum', 'verified']);

// Office Photos...
Route::post('/offices/{office}/images', [OfficeImageController::class, 'store'])->middleware(['auth:sanctum', 'verified']);
Route::delete('/offices/{office}/images/{image:id}', [OfficeImageController::class, 'delete'])->middleware(['auth:sanctum', 'verified']);

// User Reservations...
Route::get('/reservations', [UserReservationController::class, 'index'])->middleware(['auth:sanctum', 'verified']);
Route::post('/reservations', [UserReservationController::class, 'create'])->middleware(['auth:sanctum', 'verified']);
Route::delete('/reservations/{reservation}', [UserReservationController::class, 'cancel'])->middleware(['auth:sanctum', 'verified']);

// Host Reservations...
Route::get('/host/reservations', [HostReservationController::class, 'index']);
