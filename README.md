# SketchRush

A real-time multiplayer drawing and guessing party game — inspired by Skribbl.io, but with two original mechanics: **Clues** and **Guessing Streaks**.

Draw for your friends. Guess what they're drawing. Whoever's fastest wins.

## Demo

[▶ Watch the demo video](#) <!-- swap in your video link -->

## Original Mechanics

**Clues** — Each player gets a limited, lifetime pool of clue uses per game (not per round). Each clue reveals one more letter of the secret word than the last, but the very last letter is never revealed — no matter how many clues are spent. Using a clue caps that round's score.

**Guessing Streaks** — Consecutive correct guesses across rounds build a score multiplier. Any round without a correct guess resets it to zero. The drawer is exempt, since they never guess their own word.

## Core Gameplay

- Guest-only identity — no signup, just a name
- Create or join a room via a 6-character code
- Live lobby with real-time presence (join/leave tracking)
- Host-controlled game start, gated on 2+ players
- Turn rotation through a shuffled player order
- Drawer picks from 3 word choices spanning 3 different categories
- Live canvas drawing, synced across every connected browser
- Server-side guess validation — the secret word is never sent to the browser at all, not even the drawer's
- Passive auto-revealed letters over time, in addition to manual clues
- Server-authoritative round timer — ending a round never depends on any single browser staying open
- Speed-based scoring, combined with the clue penalty and streak multiplier
- Final leaderboard and "play again"

## Tech Stack

| Layer | Choice |
|---|---|
| Backend | Laravel 12, PHP 8.4 |
| Real-time | Laravel Reverb (WebSockets), private + presence channels |
| Live game state | Redis |
| Persistent data | MySQL |
| Frontend | Blade components, vanilla JavaScript (no framework), Tailwind CSS |
| Drawing | HTML5 Canvas API, throttled stroke broadcasting |
| Containerization | Docker Compose — 6 services (app, nginx, queue, reverb, mysql, redis) |
| Testing | Pest — unit tests for scoring/hint logic, feature tests for guess correctness and room creation |
| CI/CD | GitHub Actions — automated test run on every push, with a live Redis service container |

## Architecture Notes

**The server is the only source of truth for anything that matters.** The secret word, the round's end time, and whether a guess is correct are decided exclusively server-side. The browser only ever sees what it's explicitly allowed to see — word choices go out over a private, per-player WebSocket channel; the word itself never leaves Redis.

**Round timing uses server-scheduled delayed jobs, not client-side timers.** Each round dispatches a delayed job scheduled to fire at the exact moment the round should end, tagged with a token that increments every turn — so a stale job from an early-ended round can detect it's obsolete and safely do nothing when it eventually fires. The same token-scoping pattern is reused for canvas stroke ordering (rejecting out-of-order strokes after a clear) and for clue letter-reveal counts (making a reset unnecessary by giving every round its own uniquely-keyed counter).

**Redis and MySQL are split by lifetime.** Anything that only matters while a game is actively being played — turn order, the current word, live scores, presence — lives in Redis. Anything that needs to survive after the game ends — final scores, player records — is written to MySQL once, at game end.

## Running Locally

```bash
git clone https://github.com/Brahmi-Abbes/SketchRush.git
cd SketchRush
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Visit `http://localhost:8000`.

## Running Tests

```bash
docker compose exec app php artisan test
```

## Author

Built by [Me ✌](https://github.com/Brahmi-Abbes)