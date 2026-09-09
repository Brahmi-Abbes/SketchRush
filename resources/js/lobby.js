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