import './echo';

const appEl = document.getElementById('game-app');
const roomCode = appEl.dataset.roomCode;
const isDrawer = appEl.dataset.isDrawer === '1';
const statusText = document.getElementById('status-text');
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Room-wide events — everyone hears these
window.Echo.channel('room.' + roomCode)
    .listen('TurnAwaitingWord', (e) => {
        statusText.textContent = e.drawerName + ' is picking a word...';
    })
    .listen('RoundStarted', (e) => {
        statusText.textContent = e.drawerName + ' is drawing!';
        document.getElementById('word-choices')?.remove();
    });

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

// Private channel — only fires for whoever is actually the current drawer
if (isDrawer) {
    // Already-rendered choices come from Blade on page load;
    // this listener catches choices for FUTURE turns without a refresh.
}