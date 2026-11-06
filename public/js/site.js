// Favourite hearts on concert cards and detail pages.
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-favourite-url]');
    if (!button) {
        return;
    }

    event.preventDefault();
    button.disabled = true;

    fetch(button.dataset.favouriteUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        })
        .then(function (data) {
            const active = data.status === 'favourited';
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active);
            button.setAttribute('aria-label', active ? 'Remove from favourites' : 'Add to favourites');
            button.querySelector('i').className = (active ? 'fas' : 'far') + ' fa-heart';
        })
        .catch(function () {
            alert('Could not update your favourites. Please try again.');
        })
        .finally(function () {
            button.disabled = false;
        });
});

// Shared confirm prompt for destructive forms.
document.addEventListener('submit', function (event) {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

// Countdowns: concert start times on detail pages, seat holds at checkout.
(function () {
    const timers = Array.from(document.querySelectorAll('[data-countdown]'));

    if (!timers.length) {
        return;
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function tick() {
        timers.forEach(function (timer) {
            const left = Math.max(new Date(timer.dataset.countdown).getTime() - Date.now(), 0);
            const seconds = Math.floor(left / 1000);

            if (timer.dataset.countdownStyle === 'clock') {
                timer.textContent = Math.floor(seconds / 60) + ':' + pad(seconds % 60);
                timer.closest('.hold-timer').classList.toggle('is-urgent', seconds < 60);
            } else {
                timer.querySelector('[data-unit=days]').textContent = Math.floor(seconds / 86400);
                timer.querySelector('[data-unit=hours]').textContent = pad(Math.floor(seconds / 3600) % 24);
                timer.querySelector('[data-unit=minutes]').textContent = pad(Math.floor(seconds / 60) % 60);
                timer.querySelector('[data-unit=seconds]').textContent = pad(seconds % 60);
            }

            if (left === 0 && timer.dataset.expiredUrl) {
                window.location.href = timer.dataset.expiredUrl;
            }
        });
    }

    tick();
    setInterval(tick, 1000);
})();

// Buy bar appears once the main button scrolls out of view.
(function () {
    const bar = document.getElementById('buyBar');
    const anchor = document.getElementById('buyAnchor');

    if (!bar || !anchor) {
        return;
    }

    new IntersectionObserver(function (entries) {
        bar.classList.toggle('is-visible', !entries[0].isIntersecting);
    }).observe(anchor);
})();

// Share button.
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-copy-link]');
    if (!button) {
        return;
    }

    navigator.clipboard.writeText(window.location.href).then(function () {
        const label = button.querySelector('span');
        const original = label.textContent;
        label.textContent = 'Link copied';
        setTimeout(function () {
            label.textContent = original;
        }, 1800);
    });
});

// Sections fade in as they scroll into view.
(function () {
    const sections = document.querySelectorAll('.reveal');

    if (!('IntersectionObserver' in window)) {
        sections.forEach(function (section) { section.classList.add('is-in'); });
        return;
    }

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px' });

    sections.forEach(function (section) { observer.observe(section); });
})();

// Cards play their video while the pointer is over them.
(function () {
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let active = null;

    if (!finePointer || reducedMotion) {
        return;
    }

    function stop() {
        if (active) {
            active.video.remove();
            active = null;
        }
    }

    document.addEventListener('mouseover', function (event) {
        const card = event.target.closest('[data-video]');

        if (!card) {
            stop();
            return;
        }

        if (active && active.card === card) {
            return;
        }

        stop();

        const video = document.createElement('video');
        video.src = card.dataset.video;
        video.muted = true;
        video.loop = true;
        video.playsInline = true;
        video.style.opacity = 0;
        video.addEventListener('playing', function () { video.style.opacity = 1; });
        card.querySelector('.concert-card__media').append(video);
        video.play().catch(stop);
        active = { card: card, video: video };
    });
})();

// Song previews share one player along the bottom of the page.
(function () {
    const player = document.getElementById('miniPlayer');
    const toggle = document.getElementById('miniToggle');
    const bar = document.getElementById('miniBar');
    const audio = new Audio();
    let current = null;

    function setPlaying(playing) {
        toggle.querySelector('i').className = 'fas ' + (playing ? 'fa-pause' : 'fa-play');
        toggle.setAttribute('aria-label', playing ? 'Pause' : 'Play');
        document.querySelectorAll('[data-preview]').forEach(function (button) {
            const row = button.closest('.track') || button;
            const isCurrent = current && button.dataset.preview === current.preview;
            row.classList.toggle('is-playing', Boolean(isCurrent && playing));
            const icon = button.querySelector('.fa-play, .fa-pause');
            if (icon) {
                icon.className = 'fas ' + (isCurrent && playing ? 'fa-pause' : 'fa-play');
            }
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-preview]');
        if (!button) {
            return;
        }

        if (current && current.preview === button.dataset.preview) {
            toggle.click();
            return;
        }

        current = Object.assign({}, button.dataset);
        audio.src = current.preview;
        audio.play();

        document.getElementById('miniArt').src = current.art;
        document.getElementById('miniTitle').textContent = current.title;
        document.getElementById('miniArtist').textContent = current.artist;
        document.getElementById('miniLink').href = current.link;
        player.hidden = false;
    });

    toggle.addEventListener('click', function () {
        audio.paused ? audio.play() : audio.pause();
    });

    document.getElementById('miniClose').addEventListener('click', function () {
        audio.pause();
        audio.removeAttribute('src');
        current = null;
        player.hidden = true;
        setPlaying(false);
    });

    audio.addEventListener('play', function () { setPlaying(true); });
    audio.addEventListener('pause', function () { setPlaying(false); });
    audio.addEventListener('timeupdate', function () {
        bar.style.width = (audio.currentTime / audio.duration * 100 || 0) + '%';
    });
})();

// Hero video on the concert page, with its sound button.
(function () {
    const video = document.getElementById('heroVideo');
    const sound = document.getElementById('heroVideoSound');

    if (!video || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    video.addEventListener('playing', function () {
        video.classList.add('is-on');
        sound.hidden = false;
    });
    video.play().catch(function () {
        // Autoplay refused: the photo stays on screen.
    });

    sound.addEventListener('click', function () {
        video.muted = !video.muted;
        sound.querySelector('i').className = 'fas ' + (video.muted ? 'fa-volume-xmark' : 'fa-volume-high');
        sound.querySelector('span').textContent = video.muted ? 'Sound off' : 'Sound on';
    });
})();

// Filter bars search as soon as a field changes.
document.addEventListener('change', function (event) {
    const form = event.target.closest('[data-autosubmit]');
    if (form) {
        form.submit();
    }
});

// Grid or list view for the concert list, remembered in this browser.
(function () {
    const grid = document.getElementById('concertGrid');
    const buttons = document.querySelectorAll('[data-view]');

    if (!grid) {
        return;
    }

    function show(view) {
        grid.classList.toggle('is-list', view === 'list');
        buttons.forEach(function (button) {
            button.setAttribute('aria-pressed', button.dataset.view === view);
        });
    }

    try {
        show(localStorage.getItem('concertView') || 'grid');
    } catch (error) {
        show('grid');
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            show(button.dataset.view);
            try {
                localStorage.setItem('concertView', button.dataset.view);
            } catch (error) {
                // Nothing to do: the choice just will not be remembered.
            }
        });
    });
})();
