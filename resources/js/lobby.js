import './echo';

const roomCode = document.getElementById('lobby-app').dataset.roomCode;
const list = document.getElementById('players-list');
const startBtn = document.getElementById('start-btn');
const startHint = document.getElementById('start-hint');

function renderPlayer(user) {
    const li = document.createElement('li');
    li.className = 'bg-gray-700 px-3 py-2 rounded';
    li.id = 'player-' + user.id;
    li.textContent = user.name;
    list.appendChild(li);
}

function updateStartButton(count) {
    if (!startBtn) return; // not the host, button doesn't exist on this page
    if (count >= 2) {
        startBtn.disabled = false;
        startHint.textContent = '';
    } else {
        startBtn.disabled = true;
        startHint.textContent = 'Need at least 2 players';
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
