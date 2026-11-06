// Keeps the ticket's door code fresh. The server signs each code, so this only
// fetches the current one and shows how long it has left.

(function () {
    const root = document.getElementById('livePass');

    if (!root) {
        return;
    }

    const box = document.getElementById('passCode');
    const used = document.getElementById('passUsed');
    const timer = document.getElementById('passTimer');
    const note = document.getElementById('passNote');
    const text = document.getElementById('passText');
    let expiresAt = 0;
    let period = 30;
    let refresh = null;

    function draw(value) {
        const code = qrcode(0, 'M');
        code.addData(value);
        code.make();
        box.innerHTML = code.createSvgTag({ scalable: true, margin: 0 });
        box.classList.remove('is-new');
        void box.offsetWidth;
        box.classList.add('is-new');
        text.textContent = value;
    }

    function load() {
        clearTimeout(refresh);

        fetch(root.dataset.url, { headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then((pass) => {
                draw(pass.code);
                period = pass.period;
                expiresAt = Date.now() + pass.seconds_left * 1000;
                note.textContent = 'This code changes every 30 seconds';

                if (pass.checked_in_at) {
                    root.classList.add('is-used');
                    used.hidden = false;
                    used.querySelector('span').textContent = `Checked in at ${pass.checked_in_at}`;
                }

                refresh = setTimeout(load, pass.seconds_left * 1000 + 150);
            })
            .catch(() => {
                note.textContent = 'Reconnecting…';
                refresh = setTimeout(load, 5000);
            });
    }

    function tick() {
        const left = Math.max(expiresAt - Date.now(), 0);
        timer.style.transform = `scaleX(${left / (period * 1000)})`;
        requestAnimationFrame(tick);
    }

    text.addEventListener('click', () => {
        navigator.clipboard?.writeText(text.textContent);
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && Date.now() >= expiresAt) {
            load();
        }
    });

    load();
    tick();
})();
