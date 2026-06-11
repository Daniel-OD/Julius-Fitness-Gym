import jsQR from 'jsqr';

/**
 * Front-desk QR scanner: reads frames from the webcam, decodes member QR
 * codes with jsQR and posts them to the reception check-in endpoint.
 */
const root = document.getElementById('scanner-root');

if (root) {
    const video = document.getElementById('scanner-video');
    const canvas = document.getElementById('scanner-canvas');
    const overlay = document.getElementById('result-overlay');
    const statusEl = document.getElementById('scanner-status');
    const manualForm = document.getElementById('manual-form');
    const manualInput = document.getElementById('manual-code');

    const scanUrl = root.dataset.scanUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const DEDUPE_MS = 10000;
    const DECODE_INTERVAL_MS = 200;

    let lastPayload = null;
    let lastPayloadAt = 0;
    let lastDecodeAt = 0;
    let busy = false;

    const OVERLAY_STYLES = {
        green: 'bg-emerald-600/90',
        yellow: 'bg-amber-500/90',
        red: 'bg-red-600/90',
    };

    const OVERLAY_ICONS = { green: '✅', yellow: '⚠️', red: '⛔' };

    function showResult(data) {
        const color = OVERLAY_STYLES[data.color] ? data.color : 'red';

        overlay.classList.remove('hidden', ...Object.values(OVERLAY_STYLES));
        overlay.classList.add('flex', OVERLAY_STYLES[color]);

        document.getElementById('result-icon').textContent = OVERLAY_ICONS[color];
        document.getElementById('result-member').textContent = data.member ? data.member.name : '';
        document.getElementById('result-message').textContent = data.message || '';

        const plan = data.subscription && data.subscription.plan
            ? `${data.subscription.plan} · ${data.subscription.valid_until || ''}`
            : '';
        document.getElementById('result-plan').textContent = plan;

        const holdMs = color === 'green' ? 2500 : 3500;

        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            busy = false;
        }, holdMs);
    }

    async function submitCode(code) {
        if (busy) {
            return;
        }

        busy = true;

        try {
            const response = await fetch(scanUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ code }),
            });

            showResult(await response.json());
        } catch (error) {
            busy = false;
            statusEl.textContent = String(error);
        }
    }

    function scanFrame(timestamp) {
        if (!busy && video.readyState === video.HAVE_ENOUGH_DATA && timestamp - lastDecodeAt >= DECODE_INTERVAL_MS) {
            lastDecodeAt = timestamp;

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d', { willReadFrequently: true });
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const image = context.getImageData(0, 0, canvas.width, canvas.height);
            const result = jsQR(image.data, image.width, image.height, { inversionAttempts: 'dontInvert' });

            if (result && result.data) {
                const now = Date.now();
                const isDuplicate = result.data === lastPayload && now - lastPayloadAt < DEDUPE_MS;

                if (!isDuplicate) {
                    lastPayload = result.data;
                    lastPayloadAt = now;
                    submitCode(result.data);
                }
            }
        }

        requestAnimationFrame(scanFrame);
    }

    async function startCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();
            requestAnimationFrame(scanFrame);
        } catch (error) {
            statusEl.textContent = root.dataset.cameraError;
        }
    }

    manualForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const code = manualInput.value.trim();

        if (code !== '') {
            manualInput.value = '';
            submitCode(code);
        }
    });

    startCamera();
}
