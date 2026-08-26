<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::post('/rooms', [RoomController::class, 'create'])->name('rooms.create');
Route::post('/rooms/join', [RoomController::class, 'join'])->name('rooms.join');
Route::post('/rooms/{code}/start', [RoomController::class, 'start'])->name('rooms.start');
Route::get('/rooms/{code}/play', [GameController::class, 'show'])->name('rooms.play');
Route::post('/rooms/{code}/select-word', [GameController::class, 'selectWord'])->name('rooms.select-word');
Route::get('/rooms/{code}', function ($code) {
    $game = \App\Models\Game::where('room_code', $code)->with('players')->firstOrFail();
    return view('lobby', ['game' => $game]);
})->name('rooms.show');

Route::view('/', 'home')->name('home');

