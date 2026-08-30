import './echo';
import { initCanvas } from './canvas';

const appEl = document.getElementById('game-app');
const roomCode = appEl.dataset.roomCode;
const isDrawer = appEl.dataset.isDrawer === '1';
const statusText = document.getElementById('status-text');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let timerInterval = null;

function startCountdown(endsAt) {
    clearInterval(timerInterval);
    const timerEl = document.getElementById('round-timer');
    timerInterval = setInterval(() => {
        const secondsLeft = Math.max(0, endsAt - Math.floor(Date.now() / 1000));
        timerEl.textContent = secondsLeft + 's';
        if (secondsLeft <= 0) clearInterval(timerInterval);
    }, 250);
}
// Room-wide events — everyone hears these
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
        appendMessage(`${label} The word was "${e.word}".`, 'text-yellow-400 font-bold');
        setTimeout(() => window.location.reload(), 4500);
    })
    .listen('GameEnded', () => {
        statusText.textContent = 'Game over!';
    })
    .listen('PlayerGuessedCorrectly', (e) => {
        appendMessage(`${e.playerName} guessed the word! (+${e.points})`, 'text-green-400 font-bold');
    });

function appendMessage(text, className = '') {
    const chatLog = document.getElementById('chat-log');
    const el = document.createElement('div');
    el.textContent = text;
    if (className) el.className = className;
    chatLog.appendChild(el);
    chatLog.scrollTop = chatLog.scrollHeight;
}

const guessForm = document.getElementById('guess-form');
if (guessForm) {
    guessForm.addEventListener('submit', (ev) => {
        ev.preventDefault();
        const input = document.getElementById('guess-input');
        const guess = input.value.trim();
        if (!guess) return;
        fetch(`/rooms/${roomCode}/guess`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ guess }),
        });
        input.value = '';
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