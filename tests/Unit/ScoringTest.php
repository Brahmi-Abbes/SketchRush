<?php

use App\Services\GameStateService;
use Illuminate\Support\Facades\Redis;

it('gives close to max points for an instant guess', function () {
    $service = new GameStateService();

    Redis::shouldReceive('get')
        ->andReturn((string) now()->addSeconds(80)->timestamp);

    Redis::shouldReceive('exists')
        ->andReturn(0);

    $points = $service->calculatePoints('ABC123', 5, streak: 0);

    expect($points)->toBeGreaterThanOrEqual(90);
});

it('caps points at 50 when a clue was used', function () {
    $service = new GameStateService();

    Redis::shouldReceive('get')
        ->andReturn((string) now()->addSeconds(80)->timestamp);

    Redis::shouldReceive('exists')
        ->once()
        ->andReturn(1);

    $points = $service->calculatePoints('ABC123', 5, streak: 0);

    expect($points)->toBeLessThanOrEqual(50);
});

it('does not apply a streak multiplier for a streak of exactly 1', function () {
    $service = new GameStateService();

    Redis::shouldReceive('get')
        ->andReturn((string) now()->addSeconds(80)->timestamp);

    Redis::shouldReceive('exists')
        ->andReturn(0);

    $pointsAtStreak1 = $service->calculatePoints('ABC123', 5, streak: 1);
    $pointsAtStreak0 = $service->calculatePoints('ABC123', 5, streak: 0);

    expect($pointsAtStreak1)->toBe($pointsAtStreak0);
});

it('applies a bigger multiplier for a streak of 5 than a streak of 2', function () {
    $service = new GameStateService();

    Redis::shouldReceive('get')
        ->andReturn((string) now()->addSeconds(80)->timestamp);

    Redis::shouldReceive('exists')
        ->andReturn(0);

    $pointsAtStreak2 = $service->calculatePoints('ABC123', 5, streak: 2);
    $pointsAtStreak5 = $service->calculatePoints('ABC123', 5, streak: 5);

    expect($pointsAtStreak5)->toBeGreaterThan($pointsAtStreak2);
});