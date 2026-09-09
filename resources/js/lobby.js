import './echo';

const roomCode = document.getElementById('lobby-app').dataset.roomCode;
const list = document.getElementById('players-list');
const startBtn = document.getElementById('start-btn');
const startHint = document.getElementById('start-hint');
const copyBtn = document.getElementById('copy-code-btn');
copyBtn?.addEventListener('click', () => {
    navigator.clipboard.writeText(copyBtn.dataset.code).then(() => {
        const label = document.getElementById('copy-code-label');
        const original = label.textContent;
        label.textContent = 'Copied!';
        setTimeout(() => { label.textContent = original; }, 1500);
    });
});

function renderPlayer(user) {
    const li = document.createElement('li');
    li.className = 'flex items-center gap-3 bg-ink px-3 py-2.5 rounded-lg';
    li.id = 'player-' + user.id;

    const avatar = document.createElement('span');
    avatar.className = 'w-8 h-8 rounded-full bg-teal flex items-center justify-center text-ink font-semibold text-sm shrink-0';
    avatar.textContent = user.name.charAt(0).toUpperCase();

    const name = document.createElement('span');
    name.textContent = user.name;

    li.appendChild(avatar);
    li.appendChild(name);
    list.appendChild(li);
}

function updateStartButton(count) {
    if (!startBtn) return; // not the host, button doesn't exist on this page
    if (count >= 2) {
        startBtn.disabled = false;
        startHint.textContent = '';
    } else {
        startBtn.disabled = true;
        startHint.textContent = 'Need at least 2 players to start';
    }
}

window.Echo.join('room.' + roomCode)
    .here((users) => {
        list.innerHTML = '';
        users.forEach(renderPlayer);
        updateStartButton(users.length);
    })
    .joining((user) => {
        renderPlayer(user);
        updateStartButton(list.children.length);
    })
    .leaving((user) => {
        document.getElementById('player-' + user.id)?.remove();
        updateStartButton(list.children.length);
    });

startBtn?.addEventListener('click', () => {
    fetch(`/rooms/${roomCode}/start`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    }).then(res => {
        if (!res.ok) console.error('Start failed:', res.status);
    });
});

window.Echo.channel('room.' + roomCode)
    .listen('GameStarted', () => {
        window.location.href = '/rooms/' + roomCode + '/play';
    });