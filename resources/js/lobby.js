import './echo';

const roomCode = document.getElementById('lobby-app').dataset.roomCode;
const list = document.getElementById('players-list');

function renderPlayer(user) {
    const li = document.createElement('li');
    li.className = 'bg-gray-700 px-3 py-2 rounded';
    li.id = 'player-' + user.id;
    li.textContent = user.name;
    list.appendChild(li);
}

window.Echo.join('room.' + roomCode)
    .here((users) => {
        list.innerHTML = '';
        users.forEach(renderPlayer);
    })
    .joining((user) => {
        renderPlayer(user);
    })
    .leaving((user) => {
        document.getElementById('player-' + user.id)?.remove();
    });