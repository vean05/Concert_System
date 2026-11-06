// Live sales on the concert page. Every number comes from the orders and holds
// on the server, polled while the tab is open.

(function () {
    const root = document.getElementById('liveSales');

    if (!root) {
        return;
    }

    const POLL_MS = 15000;
    const seen = {};
    const number = new Intl.NumberFormat('en-US');

    function roll(id, value, suffix = '') {
        const element = document.getElementById(id);
        const from = seen[id] ?? value;
        const started = performance.now();
        seen[id] = value;

        if (from !== value) {
            element.classList.remove('is-changed');
            void element.offsetWidth;
            element.classList.add('is-changed');
        }

        function step(now) {
            const progress = Math.min((now - started) / 600, 1);
            const current = from + (value - from) * (1 - Math.pow(1 - progress, 3));
            element.textContent = number.format(suffix ? Math.round(current * 10) / 10 : Math.round(current)) + suffix;
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    }

    function show(pulse) {
        root.classList.add('is-live');
        roll('pulsePercent', pulse.sold_percent, '%');
        roll('pulseToday', pulse.sold_today);
        roll('pulseHold', pulse.on_hold);
        document.getElementById('pulseLast').textContent = pulse.last_sale ? `Last sale ${pulse.last_sale}` : '';

        pulse.tiers.forEach((tier) => {
            const row = document.querySelector(`[data-tier-id="${tier.id}"]`);
            if (row) {
                row.querySelector('[data-tier-left]').textContent = tier.remaining ? `${number.format(tier.remaining)} left` : 'Sold out';
                row.querySelector('.meter span').style.width = `${tier.sold_percent}%`;
            }
        });
    }

    function load() {
        fetch(root.dataset.url, { headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : null))
            .then((pulse) => pulse && show(pulse))
            .catch(() => {
                // The next poll tries again.
            });
    }

    // A page opened in a background tab still fills in once, then rests until it is seen.
    load();
    setInterval(() => {
        if (!document.hidden) {
            load();
        }
    }, POLL_MS);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            load();
        }
    });
})();
