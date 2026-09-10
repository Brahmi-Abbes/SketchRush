import './echo';
import { initCanvas } from './canvas';

const appEl = document.getElementById('game-app');
const playerId = appEl.dataset.playerId;
const roomCode = appEl.dataset.roomCode;
const isDrawer = appEl.dataset.isDrawer === '1';
const statusText = document.getElementById('status-text');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let timerInterval = null;

function startCountdown(endsAt) {
    clearInterval(timerInterval);
    const timerEl = document.getElementById('round-timer');
    const ring = document.getElementById('timer-ring');
    const circumference = 150.8;
    const totalSeconds = Math.max(1, endsAt - Math.floor(Date.now() / 1000));
    timerInterval = setInterval(() => {
        const secondsLeft = Math.max(0, endsAt - Math.floor(Date.now() / 1000));
        timerEl.textContent = secondsLeft;
        ring.style.strokeDashoffset = String(circumference * (1 - secondsLeft / totalSeconds));
        if (secondsLeft <= 0) clearInterval(timerInterval);
    }, 250);
}

window.Echo.channel('room.' + roomCode)
    .listen('TurnAwaitingWord', (e) => {
        statusText.textContent = e.drawerName + ' is picking a word...';
    })
    .listen('RoundStarted', (e) => {
        statusText.textContent = e.drawerName + ' is drawing!';
        document.getElementById('word-choices')?.remove();
        startCountdown(e.endsAt);
    })
    .listen('GuessSubmitted', (e) => {
        appendMessage(`${e.playerName}: ${e.guess}`);
    })
    .listen('RoundEnded', (e) => {
        clearInterval(timerInterval);
        document.getElementById('round-timer').textContent = '';
        const label = e.reason === 'all_guessed' ? 'Everyone guessed it!' : "Time's up!";
        appendMessage(`${label} The word was "${e.word}".`, 'text-gold font-bold');
        setTimeout(() => window.location.reload(), 4500);
    })
    .listen('GameEnded', () => {
        statusText.textContent = 'Game over!';
    })
    .listen('PlayerGuessedCorrectly', (e) => {
        const streakText = e.streak > 1 ? ` 🔥${e.streak}` : '';
        appendMessage(`${e.playerName} guessed the word! (+${e.points})${streakText}`, 'text-gold font-bold');

        const scoreEl = document.getElementById('score-' + e.playerId);
        if (scoreEl) {
            scoreEl.textContent = parseInt(scoreEl.textContent, 10) + e.points;
        }

        if (e.playerId === Number(playerId)) {
            const badge = document.getElementById('streak-badge');
            const countEl = document.getElementById('streak-count');
            countEl.textContent = e.streak;
            badge.classList.toggle('hidden', e.streak <= 1);
        }
    })
    .listen('LetterAutoRevealed', (e) => {
        appendMessage(`New clue: ${e.hint}`, 'text-teal font-bold');
    });

function appendMessage(text, className = '') {
    const chatLog = document.getElementById('chat-log');
    const el = document.createElement('div');
    el.textContent = text;
    if (className) el.className = className;
    chatLog.appendChild(el);
    chatLog.scrollTop = chatLog.scrollHeight;
}

function showError(message) {
    statusText.textContent = message;
    statusText.classList.add('text-red-400');
    setTimeout(() => statusText.classList.remove('text-red-400'), 2000);
}

const guessForm = document.getElementById('guess-form');
if (guessForm) {
    guessForm.addEventListener('submit', (ev) => {
        ev.preventDefault();
        const input = document.getElementById('guess-input');
        const guess = input.value.trim();
        if (!guess || input.disabled) return;

        input.disabled = true;
        fetch(`/rooms/${roomCode}/guess`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ guess }),
        }).then(res => {
            if (!res.ok && res.status !== 204) showError('Something went wrong');
        }).finally(() => {
            input.disabled = false;
            input.value = '';
            input.focus();
        });
    });
}

// Word choice buttons (only exist in the DOM if this player is the drawer)
document.querySelectorAll('.word-choice-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        fetch(`/rooms/${roomCode}/select-word`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ word: btn.dataset.word }),
        });
    });
});

// Canvas setup
const canvasEl = document.getElementById('drawing-canvas');
const canvasApi = initCanvas({
    canvas: canvasEl,
    isDrawer,
    roomCode,
    csrfToken,
});

if (isDrawer) {
    document.getElementById('drawer-controls').classList.remove('hidden');
    document.getElementById('clear-canvas-btn').addEventListener('click', () => {
        fetch(`/rooms/${roomCode}/clear-canvas`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        });
    });
}

document.getElementById('clue-btn')?.addEventListener('click', function handleClue() {
    if (this.disabled) return;
    this.disabled = true;
    fetch(`/rooms/${roomCode}/clue`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    }).finally(() => { this.disabled = false; });
});

window.Echo.private('player.' + playerId)
    .listen('ClueRevealed', (e) => {
        appendMessage(`Clue: ${e.hint}`, 'text-teal font-bold');
        document.getElementById('clues-remaining').textContent = e.cluesRemaining;
        if (e.cluesRemaining <= 0) {
            document.getElementById('clue-btn').disabled = true;
        }
    });