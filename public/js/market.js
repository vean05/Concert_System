// Home hero queue and carousels.
(function () {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const queue = document.getElementById('heroQueue');
    const media = document.getElementById('heroMedia');
    const CROSSFADE_MS = 1000;
    const VISIBLE_IN_QUEUE = 3;

    function showBackground(item) {
        const previous = Array.from(media.children);
        const photo = new Image();
        photo.src = item.dataset.photo;
        photo.alt = '';
        photo.onload = function () { photo.classList.add('is-on'); };
        media.append(photo);

        if (item.dataset.video && !reducedMotion) {
            const video = document.createElement('video');
            video.src = item.dataset.video;
            video.muted = true;
            video.loop = true;
            video.playsInline = true;
            video.addEventListener('playing', function () { video.classList.add('is-on'); });
            media.append(video);
            video.play().catch(function () {
                // Autoplay refused: the photo stays on screen.
            });
        }

        setTimeout(function () {
            previous.forEach(function (element) { element.remove(); });
        }, CROSSFADE_MS);
    }

    function showItem(item) {
        const song = JSON.parse(item.dataset.song || 'null');
        const sound = document.getElementById('heroSound');

        document.getElementById('heroTitle').textContent = item.dataset.artist;
        document.getElementById('heroLead').textContent = item.dataset.lead;
        document.getElementById('heroMeta').textContent = item.dataset.meta;
        document.getElementById('heroPrice').textContent = item.dataset.price;
        document.getElementById('heroLink').href = item.dataset.url;

        sound.hidden = !song;
        if (song) {
            sound.dataset.preview = song.preview;
            sound.dataset.title = song.title;
            sound.dataset.artist = item.dataset.artist;
            sound.dataset.art = song.artwork;
            sound.dataset.link = song.link;
        }

        showBackground(item);
    }

    if (queue) {
        const items = Array.from(queue.children);
        let current = 0;

        // Keep the playing show first, followed by the next ones around the loop.
        function arrange() {
            items.forEach(function (item, index) {
                const offset = (index - current + items.length) % items.length;
                item.hidden = offset >= VISIBLE_IN_QUEUE;
                item.style.order = offset;
                item.classList.toggle('is-on', offset === 0);
            });
        }

        function select(index) {
            current = (index + items.length) % items.length;
            arrange();
            showItem(items[current]);
        }

        items.forEach(function (item, index) {
            item.addEventListener('click', function () { select(index); });
        });

        // The progress line sets the pace, so hovering the queue pauses it too.
        queue.addEventListener('animationend', function () { select(current + 1); });

        arrange();
        showItem(items[0]);
    }

    document.querySelectorAll('[data-rail]').forEach(function (rail) {
        const prev = document.querySelector('[data-rail-prev="' + rail.id + '"]');
        const next = document.querySelector('[data-rail-next="' + rail.id + '"]');

        const swiper = new Swiper(rail, {
            slidesPerView: 'auto',
            spaceBetween: 20,
            grabCursor: true,
            rewind: true,
            autoplay: reducedMotion ? false : { delay: 4500, disableOnInteraction: false, pauseOnMouseEnter: true },
        });

        if (prev && next) {
            prev.addEventListener('click', function () { swiper.slidePrev(); });
            next.addEventListener('click', function () { swiper.slideNext(); });
        }
    });
})();
