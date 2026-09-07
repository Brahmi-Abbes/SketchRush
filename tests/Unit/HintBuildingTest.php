<?php

use App\Services\GameStateService;

it('reveals no letters when reveal count is zero', function () {
    $service = new GameStateService();

    expect($service->buildHint('APPLE', 0))->toBe('_____');
});

it('reveals letters from the start progressively', function () {
    $service = new GameStateService();

    expect($service->buildHint('APPLE', 1))->toBe('A____');
    expect($service->buildHint('APPLE', 3))->toBe('APP__');
});

it('never reveals the very last letter, even with all clues used', function () {
    $service = new GameStateService();

    expect($service->buildHint('APPLE', 4))->toBe('APPL_');
    expect($service->buildHint('APPLE', 5))->toBe('APPL_'); // capped, not fully revealed
});

it('caps correctly on a short 2-letter word', function () {
    $service = new GameStateService();

    expect($service->buildHint('OX', 3))->toBe('O_'); // never fully revealed
});

it('always uppercases the revealed letters regardless of input case', function () {
    $service = new GameStateService();

    expect($service->buildHint('apple', 2))->toBe('AP___');
});