export function initCanvas({ canvas, isDrawer, roomCode, csrfToken }) {
    const ctx = canvas.getContext('2d');
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    let drawing = false;
    let currentStroke = [];
    let lastSendTime = 0;
    let generation = 0;
    const SEND_INTERVAL_MS = 50;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY,
        };
    }

    function drawSegment(from, to, color = '#000000', width = 3) {
        ctx.strokeStyle = color;
        ctx.lineWidth = width;
        ctx.beginPath();
        ctx.moveTo(from.x, from.y);
        ctx.lineTo(to.x, to.y);
        ctx.stroke();
    }

    function sendStroke(points) {
        fetch(`/rooms/${roomCode}/draw`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ points, color: '#000000', width: 3, generation }),
        });
    }

    function flushStroke() {
        if (currentStroke.length > 1) {
            sendStroke(currentStroke);
        }
        currentStroke = [];
    }

    if (isDrawer) {
        canvas.style.cursor = 'crosshair';

        const start = (e) => {
            drawing = true;
            const pos = getPos(e);
            currentStroke = [pos];
        };

        const move = (e) => {
            if (!drawing) return;
            const pos = getPos(e);
            const last = currentStroke[currentStroke.length - 1];
            drawSegment(last, pos);
            currentStroke.push(pos);

            const now = Date.now();
            if (now - lastSendTime > SEND_INTERVAL_MS) {
                sendStroke(currentStroke);
                currentStroke = [currentStroke[currentStroke.length - 1]];
                lastSendTime = now;
            }
        };

        const end = () => {
            if (drawing) flushStroke();
            drawing = false;
        };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseleave', end);
        canvas.addEventListener('touchstart', start);
        canvas.addEventListener('touchmove', move);
        canvas.addEventListener('touchend', end);
    }

    window.Echo.channel('room.' + roomCode)
        .listen('StrokeDrawn', (e) => {
            if (e.generation !== generation) return;
            for (let i = 0; i < e.points.length - 1; i++) {
                drawSegment(e.points[i], e.points[i + 1], e.color, e.width);
            }
        })
        .listen('CanvasCleared', () => {
            generation++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

    return {
        ctx,
        clear: () => {
            generation++;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    };
}