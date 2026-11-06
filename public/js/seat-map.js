// 3D seat picker. Seat positions come from the server and the arena around them
// is built in arena-scene.js. Listings beside the map come from the same live seat
// state. Picking uses a ray-to-point test rather than mesh raycasting, which stays
// fast across thousands of seats.

import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/addons/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js';
import { OutputPass } from 'three/addons/postprocessing/OutputPass.js';
import {
    blockLabels, buildArena, fabricNormalMap, seatGeometries, seatMaterial,
} from './arena-scene.js';
import { venues } from './venues.js';
import { stadiums } from './stadiums.js';
import { LiveShow } from './live-show.js';

const MAX_SEATS = 8;
const POLL_MS = 12000;
const CLICK_SLOP_PX = 6;
const ANGLE_PENALTY = 26;
const SIDE_VIEW_DEGREES = 95;
const PARTIAL_VIEW_DEGREES = 128;
const LABEL_HIDE_DISTANCE = 34;
const CROWD_SHARE = 7;
const CROWD_SHARE_LARGE = 4;
const LARGE_VENUE_SEATS = 20000;
const CROWD_CLEAR_RADIUS = 11;
const NEAR_RADIUS = 22;
const NEAR_MAX_SEATS = 1500;
const NEAR_REFRESH_DISTANCE = 4;
const NEAR_MAX_HEIGHT = 40;
const THUMB_WIDTH = 208;
const THUMB_HEIGHT = 136;
const THUMB_GAP_MS = 180;
const FPS_SAMPLE_MS = 2500;
const LOW_FPS = 26;
const THROTTLED_FPS = 6;
const MIN_FOV = 18;
const MAX_FOV = 60;
const HOME_FOV = 44;
const FREE = 0;
const SOLD = 1;
const HELD = 2;

const COLORS = {
    background: '#050508',
    sold: new THREE.Color('#2b2b36'),
    held: new THREE.Color('#a58a3a'),
    selected: new THREE.Color('#ffffff'),
    dimmed: new THREE.Color('#1b1b22'),
};

const SCORE_LABELS = [
    [9, 'Amazing', '#0b7a4b'],
    [7.5, 'Great', '#2d7f2d'],
    [6, 'Good', '#8a6d00'],
    [0, 'Fair', '#a55a06'],
];

const SEAT_EYE_HEIGHT = 1.2;
const TOUR_MS = 30000;
const MAX_RIPPLES = 12;
const RIPPLE_SECONDS = 1.8;
const HEAT_COLOURS = ['#3a4dff', '#18c6b3', '#ffd23f', '#ff3d3d'].map((hex) => new THREE.Color(hex));

const root = document.getElementById('seatPicker');
const host = document.getElementById('seatCanvas');
const tooltip = document.getElementById('seatTooltip');
const notice = document.getElementById('seatNotice');
const loading = document.getElementById('seatLoading');
const seatViewBar = document.getElementById('seatViewBar');
const seatViewLabel = document.getElementById('seatViewLabel');
const seatViewPrice = document.getElementById('seatViewPrice');
const seatViewToggle = document.getElementById('seatViewToggle');
const listingList = document.getElementById('listings');
const qtySheet = document.getElementById('qtySheet');

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const money = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let data;
let seatCount = 0;
let seatState;
let seatScore;
let selected = [];
let activeTier = null;
let highlightedBlock = null;
let quantity = 2;
let sortBy = 'view';
let activeListing = null;
let view = null;
let viewedSeat = null;
let stagePoints;
let mapMode = 'tiers';
let priceLimit = Infinity;
let priceRange = [0, 0];
let scoreRange = [0, 0];

const indexById = new Map();
const rows = new Map();
const blocks = new Map();

fetch(root.dataset.dataUrl, { headers: { Accept: 'application/json' } })
    .then((response) => {
        if (!response.ok) {
            throw new Error('Seat data unavailable');
        }
        return response.json();
    })
    .then(start)
    .catch(() => {
        loading.textContent = 'The seat map could not be loaded. Refresh the page to try again.';
    });

function start(payload) {
    data = payload;

    const point = (values) => new THREE.Vector3(...values);
    stagePoints = {
        centre: point(data.layout.view.stage),
        front: point(data.layout.view.front),
        homeCamera: point(data.layout.view.home_camera),
        homeTarget: point(data.layout.view.home_target),
    };
    seatCount = data.seats.id.length;
    seatState = new Uint8Array(seatCount);

    for (let i = 0; i < seatCount; i++) {
        indexById.set(data.seats.id[i], i);

        const rowKey = `${data.seats.block[i]}|${data.seats.row[i]}`;
        if (!rows.has(rowKey)) {
            rows.set(rowKey, []);
        }
        rows.get(rowKey).push(i);

        const block = data.seats.block[i];
        if (!blocks.has(block)) {
            blocks.set(block, []);
        }
        blocks.get(block).push(i);
    }

    scoreSeats();

    const prices = data.tiers.map((tier) => tier.price);
    priceRange = [Math.min(...prices), Math.max(...prices)];
    scoreRange = [Math.min(...seatScore), Math.max(...seatScore)];

    view = createView();

    if (!view) {
        host.classList.add('is-unsupported');
        loading.textContent = 'Your browser cannot show the 3D view, but you can still choose from the list.';
    } else {
        loading.hidden = true;
    }

    applyStatus(data.status, true);
    renderTiers();
    renderSelection();
    wirePanel();
    renderListings();

    if (selected.length) {
        quantity = Math.max(selected.length, 1);
        renderListings();
    } else {
        openQuantitySheet();
    }

    setInterval(pollStatus, POLL_MS);
}

/* Seat state */

function applyStatus(status, restoreMine) {
    const before = restoreMine ? null : seatState.slice();

    seatState.fill(FREE);
    status.sold.forEach((id) => setSeatState(id, SOLD));
    status.held.forEach((id) => setSeatState(id, HELD));

    if (restoreMine) {
        selected = status.mine.map((id) => indexById.get(id)).filter((i) => i !== undefined);
    }

    const lost = selected.filter((i) => seatState[i] !== FREE);
    if (lost.length) {
        selected = selected.filter((i) => seatState[i] === FREE);
        renderSelection();
        showNotice(`${seatLabel(lost[0])} was just taken by another fan.`);
    }

    if (view) {
        view.paintAll();
    }

    if (before) {
        announceTaken(before, lost.length > 0);
    }

    if (listingList.childElementCount) {
        renderListings();
    }
}

/** Other fans choosing seats, shown on the map as it happens. */
function announceTaken(before, quiet) {
    const taken = [];

    for (let i = 0; i < seatCount; i++) {
        if (before[i] === FREE && seatState[i] !== FREE) {
            taken.push(i);
        }
    }

    if (!taken.length) {
        return;
    }

    if (view) {
        view.ripple(taken);
    }

    if (!quiet) {
        const sections = new Set(taken.map((index) => data.seats.block[index]));
        const place = sections.size === 1 ? ` in Section ${[...sections][0]}` : '';
        showNotice(`${taken.length} ${taken.length === 1 ? 'seat was' : 'seats were'} just taken${place}.`);
    }
}

function heat(target, amount) {
    const position = THREE.MathUtils.clamp(amount, 0, 1) * (HEAT_COLOURS.length - 1);
    const band = Math.min(Math.floor(position), HEAT_COLOURS.length - 2);

    return target.copy(HEAT_COLOURS[band]).lerp(HEAT_COLOURS[band + 1], position - band);
}

function share(value, [low, high]) {
    return high > low ? (value - low) / (high - low) : 1;
}

function setMapMode(mode) {
    mapMode = mode;

    document.querySelectorAll('#mapModes [data-mode]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.mode === mode));
    });

    const scale = document.getElementById('mapScale');
    scale.hidden = mode === 'tiers';

    if (mode === 'price') {
        document.getElementById('mapScaleLow').textContent = money.format(priceRange[0]);
        document.getElementById('mapScaleHigh').textContent = money.format(priceRange[1]);
    } else if (mode === 'view') {
        document.getElementById('mapScaleLow').textContent = 'Further';
        document.getElementById('mapScaleHigh').textContent = 'Best view';
    }

    if (view) {
        view.paintColours();
    }
}

function setSeatState(id, value) {
    const index = indexById.get(id);
    if (index !== undefined) {
        seatState[index] = value;
    }
}

function pollStatus() {
    if (document.hidden) {
        return;
    }

    fetch(root.dataset.statusUrl, { headers: { Accept: 'application/json' } })
        .then((response) => (response.ok ? response.json() : null))
        .then((status) => {
            if (status) {
                applyStatus(status, false);
            }
        })
        .catch(() => {
            // The next poll tries again; a missed update is not worth interrupting the buyer.
        });
}

function seatLabel(index) {
    const seats = data.seats;
    return `Section ${seats.block[index]}, Row ${seats.row[index]}, Seat ${seats.number[index]}`;
}

function tierOf(index) {
    return data.tiers[data.seats.tier[index]];
}

function toggleSeat(index) {
    const position = selected.indexOf(index);

    if (position >= 0) {
        selected.splice(position, 1);
    } else if (seatState[index] !== FREE) {
        showNotice('That seat is not available.');
        return;
    } else if (selected.length >= MAX_SEATS) {
        showNotice(`You can choose up to ${MAX_SEATS} seats at a time.`);
        return;
    } else {
        selected.push(index);
    }

    if (view) {
        view.paintSeat(index);
    }
    renderSelection();
}

/**
 * Clicking a seat in the arena takes it and jumps straight to its view.
 */
function openSeat(index) {
    if (seatState[index] === FREE && !selected.includes(index)) {
        toggleSeat(index);
    }

    view.enterSeatView(index);
}

/** The seat next to the one being viewed, or null at the end of the row. */
function neighbourSeat(step) {
    const row = rows.get(`${data.seats.block[viewedSeat]}|${data.seats.row[viewedSeat]}`);

    return row[row.indexOf(viewedSeat) + step] ?? null;
}

function renderSeatBar() {
    if (viewedSeat === null) {
        return;
    }

    const tier = tierOf(viewedSeat);
    const taken = { [SOLD]: 'Sold', [HELD]: 'Held by another fan' }[seatState[viewedSeat]];
    const mine = selected.includes(viewedSeat);

    seatViewLabel.textContent = `Section ${data.seats.block[viewedSeat]}, Row ${data.seats.row[viewedSeat]}, Seat ${data.seats.number[viewedSeat]}`;
    seatViewPrice.textContent = taken || `${tier.name} · ${money.format(tier.price)}`;
    seatViewToggle.textContent = mine ? 'Remove seat' : 'Add this seat';
    seatViewToggle.disabled = Boolean(taken);
    document.getElementById('seatViewPrev').disabled = neighbourSeat(-1) === null;
    document.getElementById('seatViewNext').disabled = neighbourSeat(1) === null;
}

function replaceSelection(indices) {
    const previous = selected;
    selected = indices;

    if (view) {
        previous.forEach(view.paintSeat);
        selected.forEach(view.paintSeat);
    }
    renderSelection();
}

/* View score */

function viewCost(index) {
    const seats = data.seats;
    const front = stagePoints.front;
    const distance = Math.hypot(seats.x[index] - front.x, seats.y[index] - front.y, seats.z[index] - front.z);

    return distance + viewAngle(index) * ANGLE_PENALTY;
}

/** Radians off the stage's centre line. Close seats level with the stage see it side-on. */
function viewAngle(index) {
    if (data.layout.view.in_the_round) {
        return 0;
    }

    return Math.atan2(Math.abs(data.seats.z[index]), data.seats.x[index] - stagePoints.front.x);
}

function scoreSeats() {
    const costs = new Float32Array(seatCount);
    let lowest = Infinity;
    let highest = -Infinity;

    for (let i = 0; i < seatCount; i++) {
        costs[i] = viewCost(i);
        lowest = Math.min(lowest, costs[i]);
        highest = Math.max(highest, costs[i]);
    }

    seatScore = new Float32Array(seatCount);
    for (let i = 0; i < seatCount; i++) {
        seatScore[i] = 10 - (6 * (costs[i] - lowest)) / (highest - lowest);
    }
}

function scoreLabel(score) {
    return SCORE_LABELS.find(([minimum]) => score >= minimum);
}

/* Listings */

/** The best run of `size` free seats side by side in each block. */
function bestRuns(size) {
    const best = new Map();

    rows.forEach((row) => {
        for (let start = 0; start + size <= row.length; start++) {
            const run = row.slice(start, start + size);

            if (run.some((index) => seatState[index] !== FREE)) {
                continue;
            }

            const middle = run[Math.floor(size / 2)];
            const block = data.seats.block[middle];
            const current = best.get(block);

            if (!current || seatScore[middle] > current.score) {
                best.set(block, { block, run, middle, score: seatScore[middle] });
            }
        }
    });

    return [...best.values()];
}

function buildListings() {
    const listings = bestRuns(quantity)
        .map((listing) => ({ ...listing, tier: data.seats.tier[listing.middle] }))
        .filter((listing) => activeTier === null || listing.tier === activeTier)
        .filter((listing) => data.tiers[listing.tier].price <= priceLimit);

    const lowest = Math.min(...listings.map((listing) => data.tiers[listing.tier].price));
    const topScore = Math.max(...listings.map((listing) => listing.score));

    let bestTagged = false;
    let cheapestTagged = false;

    listings.sort((a, b) => b.score - a.score).forEach((listing) => {
        const degrees = THREE.MathUtils.radToDeg(viewAngle(listing.middle));

        listing.tags = [];
        if (listing.score === topScore && !bestTagged) {
            bestTagged = true;
            listing.tags.push(['Best view']);
        } else if (data.tiers[listing.tier].price === lowest && !cheapestTagged) {
            cheapestTagged = true;
            listing.tags.push(['Lowest price']);
        }
        if (data.seats.row[listing.middle] === data.seats.row[blocks.get(listing.block)[0]]) {
            listing.tags.push(['Front row']);
        }
        if (degrees > PARTIAL_VIEW_DEGREES) {
            listing.tags.push(['Partial view', true]);
        } else if (degrees > SIDE_VIEW_DEGREES) {
            listing.tags.push(['Side view', true]);
        }
    });

    const byPrice = (a, b) => data.tiers[a.tier].price - data.tiers[b.tier].price || b.score - a.score;
    const byView = (a, b) => b.score - a.score;

    return listings.sort(sortBy === 'price' ? byPrice : byView);
}

function renderListings() {
    const listings = buildListings();

    document.getElementById('listingCount').textContent = `${listings.length} ${listings.length === 1 ? 'listing' : 'listings'}`;
    document.getElementById('listingSort').textContent = sortBy === 'price' ? 'Lowest price' : 'Best view';
    document.getElementById('quantityLabel').textContent = `${quantity} ${quantity === 1 ? 'ticket' : 'tickets'}`;

    listingList.textContent = '';

    if (!listings.length) {
        const empty = document.createElement('li');
        empty.className = 'listings__empty';
        empty.textContent = `No ${quantity} seats together here. Try another area or fewer tickets.`;
        listingList.append(empty);
        return;
    }

    listings.forEach((listing) => listingList.append(listingRow(listing)));
}

function listingRow(listing) {
    const { block, run, middle, score, tier: tierIndex, tags } = listing;
    const tier = data.tiers[tierIndex];
    const [, label, colour] = scoreLabel(score);
    const key = run.join('-');

    const item = document.createElement('li');
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `listing ${activeListing === key ? 'is-active' : ''}`;

    const thumb = document.createElement('span');
    thumb.className = 'listing__view';
    if (view) {
        view.thumbnail(thumb, middle);
    }

    const text = document.createElement('span');
    const title = document.createElement('strong');
    title.textContent = `Section ${block}`;
    const row = document.createElement('small');
    row.textContent = `Row ${data.seats.row[middle]} · ${run.length} ${run.length === 1 ? 'seat' : 'seats together'}`;

    const tagRow = document.createElement('span');
    tagRow.className = 'listing__tags';

    const badge = document.createElement('span');
    badge.className = 'view-score';
    badge.style.setProperty('--score', colour);
    const number = document.createElement('b');
    number.textContent = score.toFixed(1);
    badge.append(number, label);
    tagRow.append(badge);

    tags.forEach(([name, warning]) => {
        const tag = document.createElement('span');
        tag.className = `listing__tag ${warning ? 'is-warn' : ''}`;
        tag.textContent = name;
        tagRow.append(tag);
    });
    text.append(title, row, tagRow);

    const price = document.createElement('span');
    price.className = 'listing__price';
    const amount = document.createElement('strong');
    amount.textContent = money.format(tier.price);
    const each = document.createElement('small');
    each.textContent = 'each';
    price.append(amount, each);

    button.append(thumb, text, price);

    button.addEventListener('mouseenter', () => highlightBlock(block));
    button.addEventListener('mouseleave', () => highlightBlock(null));
    button.addEventListener('click', () => {
        activeListing = key;
        replaceSelection(run);
        listingList.querySelectorAll('.listing').forEach((other) => other.classList.toggle('is-active', other === button));

        if (view) {
            highlightBlock(null);
            view.enterSeatView(middle);
        }
    });

    item.append(button);
    return item;
}

function highlightBlock(block) {
    if (highlightedBlock !== block) {
        highlightedBlock = block;
        if (view) {
            view.paintAll();
        }
    }
}

/* How many tickets */

function openQuantitySheet() {
    const grid = document.getElementById('qtyGrid');
    grid.textContent = '';

    for (let size = 1; size <= MAX_SEATS; size++) {
        const runs = bestRuns(size);
        const cheapest = Math.min(...runs.map((run) => data.tiers[data.seats.tier[run.middle]].price));

        const button = document.createElement('button');
        button.type = 'button';
        button.disabled = !runs.length;
        button.textContent = size;

        const from = document.createElement('small');
        from.textContent = runs.length ? `From $${Math.round(cheapest)}` : 'None left';
        button.append(from);

        button.addEventListener('click', () => {
            quantity = size;
            qtySheet.hidden = true;
            renderListings();
        });
        grid.append(button);
    }

    qtySheet.hidden = false;
}

/* Side panel */

function renderTiers() {
    const list = document.getElementById('tierList');
    list.querySelectorAll('[data-tier]').forEach((pill) => pill.remove());

    data.tiers.forEach((tier, index) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'seat-pill';
        button.dataset.tier = index;
        button.setAttribute('aria-pressed', 'false');

        const swatch = document.createElement('i');
        swatch.style.background = tier.color;
        button.append(swatch, tier.name);

        button.addEventListener('click', () => {
            activeTier = activeTier === index ? null : index;
            list.querySelectorAll('[data-tier]').forEach((other) => {
                other.setAttribute('aria-pressed', String(Number(other.dataset.tier) === activeTier));
            });
            if (view) {
                view.paintAll();
            }
            renderListings();
        });
        list.append(button);
    });
}

function renderSelection() {
    const list = document.getElementById('selectionList');
    const total = document.getElementById('selectionTotal');
    const button = document.getElementById('continueButton');

    list.textContent = '';
    list.hidden = selected.length === 0;

    selected.forEach((index) => {
        const tier = tierOf(index);
        const item = document.createElement('li');
        item.className = 'seat-chip';

        const swatch = document.createElement('i');
        swatch.style.background = tier.color;

        const text = document.createElement('span');
        text.textContent = `Sec ${data.seats.block[index]} · Row ${data.seats.row[index]} · Seat ${data.seats.number[index]} · ${money.format(tier.price)}`;

        item.append(swatch, text);

        if (view) {
            const look = iconButton('fa-eye', `View from ${seatLabel(index)}`);
            look.addEventListener('click', () => view.enterSeatView(index));
            item.append(look);
        }

        const remove = iconButton('fa-xmark', `Remove ${seatLabel(index)}`);
        remove.addEventListener('click', () => toggleSeat(index));
        item.append(remove);

        list.append(item);
    });

    const sum = selected.reduce((running, index) => running + tierOf(index).price, 0);
    total.textContent = money.format(sum);
    button.disabled = selected.length === 0;
    button.textContent = selected.length
        ? `Continue with ${selected.length} ${selected.length === 1 ? 'seat' : 'seats'}`
        : 'Choose your seats';
    renderSeatBar();
}

function iconButton(icon, label) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'seat-chip__action';
    button.setAttribute('aria-label', label);
    button.title = label;

    const glyph = document.createElement('i');
    glyph.className = `fas ${icon}`;
    button.append(glyph);

    return button;
}

function wirePanel() {
    document.getElementById('continueButton').addEventListener('click', holdSeats);
    document.getElementById('quantityButton').addEventListener('click', openQuantitySheet);

    document.getElementById('listingSortButton').addEventListener('click', () => {
        sortBy = sortBy === 'view' ? 'price' : 'view';
        renderListings();
    });

    document.getElementById('homeButton').addEventListener('click', () => {
        if (view) {
            view.goHome();
        }
    });

    document.querySelectorAll('#mapModes [data-mode]').forEach((button) => {
        button.addEventListener('click', () => setMapMode(button.dataset.mode));
    });

    wirePriceLimit();

    document.getElementById('exitSeatView').addEventListener('click', () => {
        if (view) {
            view.exitSeatView();
        }
    });

    seatViewToggle.addEventListener('click', () => toggleSeat(viewedSeat));

    [['seatViewPrev', -1], ['seatViewNext', 1]].forEach(([id, step]) => {
        document.getElementById(id).addEventListener('click', () => {
            const seat = neighbourSeat(step);

            if (seat !== null) {
                view.enterSeatView(seat, 500);
            }
        });
    });
}

function wirePriceLimit() {
    const [low, high] = priceRange;

    if (low === high) {
        return;
    }

    const box = document.getElementById('priceBox');
    const slider = document.getElementById('priceLimit');
    const output = document.getElementById('priceOutput');
    let listingsTimer;

    box.hidden = false;
    slider.min = low;
    slider.max = high;
    slider.value = high;
    output.textContent = 'Any price';

    slider.addEventListener('input', () => {
        const value = Number(slider.value);

        priceLimit = value >= high ? Infinity : value;
        output.textContent = value >= high ? 'Any price' : `Up to ${money.format(value)}`;

        if (view) {
            view.paintColours();
        }

        clearTimeout(listingsTimer);
        listingsTimer = setTimeout(renderListings, 120);
    });
}

function holdSeats() {
    const button = document.getElementById('continueButton');
    button.disabled = true;
    button.textContent = 'Reserving your seats…';

    fetch(root.dataset.holdUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
        body: JSON.stringify({ seat_ids: selected.map((index) => data.seats.id[index]) }),
    })
        .then((response) => response.json().then((body) => ({ ok: response.ok, body })))
        .then(({ ok, body }) => {
            if (ok) {
                window.location.href = body.redirect;
                return;
            }

            showNotice(body.message || 'Those seats could not be reserved. Please try again.');
            renderSelection();
            pollStatus();
        })
        .catch(() => {
            showNotice('Those seats could not be reserved. Please try again.');
            renderSelection();
        });
}

let noticeTimer;

function showNotice(message) {
    notice.textContent = message;
    notice.hidden = false;
    clearTimeout(noticeTimer);
    noticeTimer = setTimeout(() => {
        notice.hidden = true;
    }, 5000);
}

/* 3D scene */

function createView() {
    let renderer;

    try {
        renderer = new THREE.WebGLRenderer({ antialias: false, powerPreference: 'high-performance' });
    } catch (error) {
        return null;
    }

    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(COLORS.background);
    scene.fog = new THREE.Fog(COLORS.background, 170, 340);

    const { homeCamera, homeTarget, centre: stageCentre } = stagePoints;
    const camera = new THREE.PerspectiveCamera(HOME_FOV, 1, 0.2, 900);
    camera.position.copy(homeCamera);
    if (!reducedMotion) {
        camera.position.multiply(new THREE.Vector3(1.27, 1.15, -1.13));
    }

    let pixelRatio = Math.min(window.devicePixelRatio, 1.75);
    renderer.setPixelRatio(pixelRatio);
    host.prepend(renderer.domElement);

    const composer = new EffectComposer(renderer, new THREE.WebGLRenderTarget(4, 4, { type: THREE.HalfFloatType, samples: 4 }));
    composer.addPass(new RenderPass(scene, camera));
    const bloom = new UnrealBloomPass(new THREE.Vector2(4, 4), 0.26, 0.55, 1.05);
    composer.addPass(bloom);
    composer.addPass(new OutputPass());

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.target.copy(homeTarget);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.screenSpacePanning = false;
    controls.minDistance = 7;
    controls.maxDistance = data.layout.view.max_distance;
    controls.maxPolarAngle = THREE.MathUtils.degToRad(84);
    controls.autoRotateSpeed = 0.35;
    controls.autoRotate = !reducedMotion;

    const arena = buildArena({ scene, renderer, data, blocks, rows, reducedMotion, venue: venues[data.layout.scene] ?? stadiums[data.layout.scene] });
    const labels = blockLabels(scene, blocks, data.seats);

    /* Seats: every seat is simple, and the ones around the camera are detailed. */

    const geometries = seatGeometries();
    const far = createSeatMesh(geometries.far, seatCount, { roughness: 0.82 });
    const near = createSeatMesh(geometries.near, NEAR_MAX_SEATS, {
        roughness: 0.78,
        normalMap: fabricNormalMap(),
        normalScale: new THREE.Vector2(0.35, 0.35),
    });
    near.count = 0;

    const nearFrame = new THREE.InstancedMesh(
        geometries.frame,
        new THREE.MeshStandardMaterial({ color: '#1a1b20', roughness: 0.45, metalness: 0.75 }),
        NEAR_MAX_SEATS
    );
    nearFrame.frustumCulled = false;
    nearFrame.count = 0;

    scene.add(far, near, nearFrame);

    const pins = createPins();
    scene.add(pins);

    const nearSlot = new Int32Array(seatCount).fill(-1);
    const nearCentre = new THREE.Vector3(1e6, 0, 0);
    let nearSeats = [];

    const hidden = new THREE.Matrix4().makeScale(0, 0, 0);
    const place = new THREE.Object3D();
    const color = new THREE.Color();

    function writeMatrix(index) {
        const seats = data.seats;
        const mine = selected.includes(index);
        const slot = nearSlot[index];

        place.position.set(seats.x[index], seats.y[index], seats.z[index]);
        place.rotation.set(0, seats.rotation[index], 0);
        place.scale.setScalar(mine && slot < 0 ? 1.3 : 1);
        place.updateMatrix();

        if (slot >= 0) {
            near.setMatrixAt(slot, place.matrix);
            nearFrame.setMatrixAt(slot, place.matrix);
            near.geometry.attributes.aKeep.setX(slot, mine ? 1 : 0);
            far.setMatrixAt(index, hidden);
        } else {
            far.setMatrixAt(index, place.matrix);
        }
        far.geometry.attributes.aKeep.setX(index, mine ? 1 : 0);
    }

    function seatColor(index) {
        if (selected.includes(index)) {
            return COLORS.selected;
        }
        if (seatState[index] === SOLD) {
            return COLORS.sold;
        }
        if (seatState[index] === HELD) {
            return COLORS.held;
        }
        if ((activeTier !== null && data.seats.tier[index] !== activeTier) || tierOf(index).price > priceLimit) {
            return COLORS.dimmed;
        }
        if (highlightedBlock !== null && data.seats.block[index] !== highlightedBlock) {
            return modeColor(index).multiplyScalar(0.16);
        }
        return modeColor(index);
    }

    function modeColor(index) {
        if (mapMode === 'price') {
            return heat(color, share(tierOf(index).price, priceRange));
        }
        if (mapMode === 'view') {
            return heat(color, share(seatScore[index], scoreRange));
        }
        return color.set(tierOf(index).color);
    }

    function writeColor(index) {
        const value = seatColor(index);
        far.setColorAt(index, value);

        if (nearSlot[index] >= 0) {
            near.setColorAt(nearSlot[index], value);
        }
    }

    function flagUpdates() {
        [far, near].forEach((mesh) => {
            mesh.instanceMatrix.needsUpdate = true;
            mesh.instanceColor.needsUpdate = true;
            mesh.geometry.attributes.aKeep.needsUpdate = true;
        });
        nearFrame.instanceMatrix.needsUpdate = true;
    }

    function paintSeat(index) {
        writeMatrix(index);
        writeColor(index);
        flagUpdates();
    }

    function paintAll() {
        for (let i = 0; i < seatCount; i++) {
            writeMatrix(i);
            writeColor(i);
        }

        flagUpdates();
        seatCrowd();
    }

    function paintColours() {
        for (let i = 0; i < seatCount; i++) {
            writeColor(i);
        }

        far.instanceColor.needsUpdate = true;
        near.instanceColor.needsUpdate = true;
    }

    /**
     * Real buyers, plus a stable fill of extra people so a quiet on-sale still
     * feels like a show. The fill stays away from the seat being viewed, so the
     * free seats around you read as free.
     */
    function seatCrowd() {
        const seats = data.seats;
        const share = seatCount > LARGE_VENUE_SEATS ? CROWD_SHARE_LARGE : CROWD_SHARE;
        const reach = CROWD_CLEAR_RADIUS * CROWD_CLEAR_RADIUS;
        const eye = viewedSeat;
        const people = [];

        for (let i = 0; i < seatCount; i++) {
            if (seatState[i] === SOLD) {
                people.push(i);
                continue;
            }
            if (selected.includes(i) || ((i * 2654435761) >>> 0) % 10 >= share) {
                continue;
            }
            if (eye !== null) {
                const dx = seats.x[i] - seats.x[eye];
                const dy = seats.y[i] - seats.y[eye];
                const dz = seats.z[i] - seats.z[eye];
                if (dx * dx + dy * dy + dz * dz < reach) {
                    continue;
                }
            }
            people.push(i);
        }

        arena.setPeople(people);
    }

    function refreshNearSeats() {
        const seats = data.seats;
        const reach = NEAR_RADIUS * NEAR_RADIUS;
        const previous = nearSeats;
        let found = [];

        nearCentre.copy(camera.position);

        if (camera.position.y < NEAR_MAX_HEIGHT) {
            for (let i = 0; i < seatCount; i++) {
                const dx = seats.x[i] - camera.position.x;
                const dy = seats.y[i] - camera.position.y;
                const dz = seats.z[i] - camera.position.z;
                const distance = dx * dx + dy * dy + dz * dz;

                if (distance < reach) {
                    found.push([i, distance]);
                }
            }
        }

        if (found.length > NEAR_MAX_SEATS) {
            found = found.sort((a, b) => a[1] - b[1]).slice(0, NEAR_MAX_SEATS);
        }

        previous.forEach((index) => {
            nearSlot[index] = -1;
        });

        nearSeats = found.map(([index]) => index);
        nearSeats.forEach((index, slot) => {
            nearSlot[index] = slot;
        });

        near.count = nearSeats.length;
        nearFrame.count = nearSeats.length;

        previous.forEach((index) => {
            writeMatrix(index);
            writeColor(index);
        });
        nearSeats.forEach((index) => {
            writeMatrix(index);
            writeColor(index);
        });
        flagUpdates();
    }

    /* Camera moves */

    let flight = null;
    let seatView = null;
    let savedCamera = null;

    function fly(position, target, duration = 1100) {
        flight = {
            fromPosition: camera.position.clone(),
            fromTarget: controls.target.clone(),
            position,
            target,
            startedAt: performance.now(),
            duration: reducedMotion ? 1 : duration,
            arc: camera.position.distanceTo(position) * 0.12,
        };
        controls.enabled = false;
        controls.autoRotate = false;
    }

    function updateFlight(now) {
        const progress = Math.min((now - flight.startedAt) / flight.duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);

        camera.position.lerpVectors(flight.fromPosition, flight.position, eased);
        camera.position.y += Math.sin(Math.PI * eased) * flight.arc;
        controls.target.lerpVectors(flight.fromTarget, flight.target, eased);
        camera.lookAt(controls.target);

        if (progress === 1) {
            flight = null;
            controls.enabled = !seatView;
        }
    }

    function seatPoint(index) {
        return new THREE.Vector3(data.seats.x[index], data.seats.y[index], data.seats.z[index]);
    }

    function setFov(value) {
        camera.fov = value;
        camera.updateProjectionMatrix();
    }

    function goHome() {
        stopTour();
        exitSeatView(false);
        setFov(HOME_FOV);
        fly(homeCamera.clone(), homeTarget.clone(), 1400);
    }

    function enterSeatView(index, duration = 1500) {
        stopTour();

        if (!seatView) {
            savedCamera = { position: camera.position.clone(), target: controls.target.clone() };
        }

        const eye = seatPoint(index).add(new THREE.Vector3(0, SEAT_EYE_HEIGHT, 0));
        seatView = { yaw: 0, pitch: 0 };
        fly(eye, stageCentre.clone(), duration);

        viewedSeat = index;
        seatCrowd();
        renderSeatBar();
        seatViewBar.hidden = false;
        tooltip.hidden = true;
        host.classList.add('is-seat-view');
        liveBar.hidden = false;
        liveCredit.hidden = !live.song;
        live.setSeat(seatSound(index));
    }

    function exitSeatView(flyBack = true) {
        if (!seatView) {
            return;
        }

        seatView = null;
        viewedSeat = null;
        seatCrowd();
        seatViewBar.hidden = true;
        host.classList.remove('is-seat-view');
        liveBar.hidden = true;
        liveCredit.hidden = true;
        endLiveShow();
        setFov(HOME_FOV);

        if (flyBack) {
            fly(savedCamera.position, savedCamera.target, 1200);
        } else {
            controls.enabled = true;
        }
    }

    /* The flying tour. Waypoints come from the seats, so it fits every venue. */

    let tour = null;

    function tourPath() {
        const seats = data.seats;
        const middle = stagePoints.centre;
        const ahead = stagePoints.front.clone().sub(middle).setY(0);

        if (ahead.lengthSq() < 1) {
            ahead.set(1, 0, 0);
        }
        ahead.normalize();

        const across = new THREE.Vector3(-ahead.z, 0, ahead.x);
        const furthest = { ahead: -1, left: -1, right: -1 };
        const distance = { ahead: -Infinity, left: -Infinity, right: -Infinity };
        let highest = 0;

        for (let i = 0; i < seatCount; i++) {
            highest = Math.max(highest, seats.y[i]);

            const x = seats.x[i] - middle.x;
            const z = seats.z[i] - middle.z;
            const along = x * ahead.x + z * ahead.z;
            const side = x * across.x + z * across.z;

            [['ahead', along], ['left', side], ['right', -side]].forEach(([name, value]) => {
                if (value > distance[name]) {
                    distance[name] = value;
                    furthest[name] = i;
                }
            });
        }

        const lookout = (index) => {
            const seat = seatPoint(index);

            return seat.lerp(new THREE.Vector3(middle.x, seat.y, middle.z), 0.14).setY(seat.y + 4);
        };

        return [
            homeCamera.clone(),
            homeCamera.clone().lerp(homeTarget, 0.55).setY(Math.max(30, highest + 7)),
            stagePoints.front.clone().addScaledVector(ahead, 11).addScaledVector(across, -12).setY(3.4),
            stagePoints.front.clone().addScaledVector(ahead, 4).addScaledVector(across, 10).setY(3),
            lookout(furthest.left),
            lookout(furthest.ahead),
            lookout(furthest.right),
            homeCamera.clone(),
        ];
    }

    function startTour() {
        exitSeatView(false);
        setFov(HOME_FOV);
        flight = null;
        controls.enabled = false;
        controls.autoRotate = false;
        tooltip.hidden = true;
        tour = { curve: new THREE.CatmullRomCurve3(tourPath(), false, 'centripetal'), startedAt: performance.now() };
        host.classList.add('is-touring');
        showNotice('Touring the venue. Click anywhere to stop.');
    }

    function stopTour() {
        if (!tour) {
            return false;
        }

        tour = null;
        host.classList.remove('is-touring');
        controls.enabled = true;

        return true;
    }

    function updateTour(now) {
        const progress = Math.min((now - tour.startedAt) / TOUR_MS, 1);
        const smoothed = THREE.MathUtils.smoothstep(progress, 0, 1);
        const onStage = THREE.MathUtils.smoothstep(Math.min(progress * 8, (1 - progress) * 8), 0, 1);

        tour.curve.getPointAt(smoothed, camera.position);
        look.lerpVectors(homeTarget, stageCentre, onStage);
        camera.lookAt(look);

        if (progress === 1) {
            stopTour();
            controls.target.copy(homeTarget);
            controls.autoRotate = true;
        }
    }

    /* The song in the seat view */

    const live = new LiveShow(data.songs ?? []);
    const liveBar = document.getElementById('liveBar');
    const liveCredit = document.getElementById('liveCredit');
    const livePlay = document.getElementById('livePlay');
    const livePhones = document.getElementById('livePhones');
    const liveLevels = [...document.querySelectorAll('#liveEq i')];

    function seatSound(index) {
        const stage = data.layout.view.in_the_round ? new THREE.Vector3() : stageCentre;

        return {
            distance: seatPoint(index).distanceTo(stage),
            pan: data.layout.view.in_the_round ? 0 : THREE.MathUtils.clamp((data.seats.z[index] - stageCentre.z) / 50, -0.6, 0.6),
        };
    }

    function endLiveShow() {
        live.pause();
        arena.setPhones(false);
        livePhones.setAttribute('aria-pressed', 'false');
    }

    function wireLiveShow() {
        const label = document.getElementById('liveSong');
        const icon = livePlay.querySelector('i');
        const next = document.getElementById('liveNext');

        if (!live.song) {
            livePlay.hidden = true;
            next.hidden = true;
            document.getElementById('liveEq').hidden = true;
        }

        if (reducedMotion) {
            ['livePhones', 'liveWave', 'liveConfetti'].forEach((id) => {
                document.getElementById(id).hidden = true;
            });
        }

        live.onChange = () => {
            label.textContent = live.playing ? live.song.title : 'Play a song';
            livePlay.setAttribute('aria-pressed', String(live.playing));
            icon.className = `fas ${live.playing ? 'fa-pause' : 'fa-play'}`;
        };
        live.onError = () => showNotice('That song could not be played.');

        livePlay.addEventListener('click', () => live.toggle());
        next.addEventListener('click', () => live.next());
        livePhones.addEventListener('click', () => {
            const on = livePhones.getAttribute('aria-pressed') !== 'true';
            livePhones.setAttribute('aria-pressed', String(on));
            arena.setPhones(on);
        });
        document.getElementById('liveWave').addEventListener('click', () => arena.wave());
        document.getElementById('liveConfetti').addEventListener('click', () => arena.confetti());
    }

    wireLiveShow();

    /* Rings on the map where other fans have just taken seats */

    const ripples = [];
    const rippleTexture = new THREE.CanvasTexture((() => {
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 128;
        const context = canvas.getContext('2d');
        context.strokeStyle = '#ffffff';
        context.lineWidth = 9;
        context.beginPath();
        context.arc(64, 64, 54, 0, Math.PI * 2);
        context.stroke();
        return canvas;
    })());

    for (let i = 0; i < MAX_RIPPLES; i++) {
        const sprite = new THREE.Sprite(new THREE.SpriteMaterial({
            map: rippleTexture,
            color: new THREE.Color(3, 1, 0.5),
            transparent: true,
            depthWrite: false,
            depthTest: false,
            blending: THREE.AdditiveBlending,
            fog: false,
        }));
        sprite.visible = false;
        scene.add(sprite);
        ripples.push({ sprite, age: Infinity });
    }

    function ripple(indices) {
        indices.slice(0, MAX_RIPPLES).forEach((index, slot) => {
            const item = ripples[slot];
            item.age = -slot * 0.08;
            item.sprite.position.copy(seatPoint(index)).setY(data.seats.y[index] + 1);
            item.sprite.visible = true;
        });
    }

    function updateRipples(delta) {
        ripples.forEach((item) => {
            if (!item.sprite.visible) {
                return;
            }

            item.age += delta;
            const progress = Math.max(item.age, 0) / RIPPLE_SECONDS;
            const size = 2 + progress * 13;

            item.sprite.scale.set(size, size, 1);
            item.sprite.material.opacity = item.age < 0 ? 0 : 1 - progress;
            item.sprite.visible = progress < 1;
        });
    }

    /* Pointer */

    const raycaster = new THREE.Raycaster();
    const pointer = new THREE.Vector2();
    let pressed = null;
    let hovered = -1;
    let pendingMove = null;

    function pick(event) {
        const bounds = renderer.domElement.getBoundingClientRect();
        pointer.set(
            ((event.clientX - bounds.left) / bounds.width) * 2 - 1,
            -((event.clientY - bounds.top) / bounds.height) * 2 + 1
        );
        raycaster.setFromCamera(pointer, camera);

        const { origin, direction } = raycaster.ray;
        const seats = data.seats;
        let best = -1;
        let bestOffset = Infinity;

        for (let i = 0; i < seatCount; i++) {
            const dx = seats.x[i] - origin.x;
            const dy = seats.y[i] + 0.5 - origin.y;
            const dz = seats.z[i] - origin.z;
            const along = dx * direction.x + dy * direction.y + dz * direction.z;

            if (along < 0.6) {
                continue;
            }

            const offset = dx * dx + dy * dy + dz * dz - along * along;
            const reach = Math.max(0.36, along * 0.0075);

            if (offset < reach * reach && offset < bestOffset) {
                bestOffset = offset;
                best = i;
            }
        }

        return best;
    }

    function showTooltip(index, event) {
        const bounds = host.getBoundingClientRect();
        const tier = tierOf(index);
        const taken = { [SOLD]: 'Sold', [HELD]: 'Held by another fan' }[seatState[index]];
        const [, label] = scoreLabel(seatScore[index]);

        tooltip.textContent = '';
        const title = document.createElement('strong');
        title.textContent = `Sec ${data.seats.block[index]} · Row ${data.seats.row[index]} · Seat ${data.seats.number[index]}`;
        const detail = document.createElement('span');
        detail.textContent = taken ? `${taken} · click for the view` : `${money.format(tier.price)} · ${label} view`;
        tooltip.append(title, detail);

        tooltip.style.transform = `translate(${event.clientX - bounds.left + 16}px, ${event.clientY - bounds.top + 16}px)`;
        tooltip.hidden = false;
    }

    function handleHover(event) {
        if (seatView || tour) {
            return;
        }

        hovered = pick(event);
        renderer.domElement.style.cursor = hovered >= 0 ? 'pointer' : '';

        if (hovered >= 0) {
            showTooltip(hovered, event);
        } else {
            tooltip.hidden = true;
        }
    }

    const canvas = renderer.domElement;

    canvas.addEventListener('pointerdown', (event) => {
        if (stopTour()) {
            goHome();
            return;
        }

        pressed = { x: event.clientX, y: event.clientY };
        controls.autoRotate = false;
    });

    canvas.addEventListener('pointermove', (event) => {
        if (seatView && pressed) {
            seatView.yaw += event.movementX * 0.004;
            seatView.pitch = THREE.MathUtils.clamp(seatView.pitch - event.movementY * 0.004, -0.5, 0.6);
            return;
        }

        if (event.pointerType === 'mouse' && !pressed) {
            pendingMove = event;
        }
    });

    canvas.addEventListener('pointerleave', () => {
        tooltip.hidden = true;
        pendingMove = null;
    });

    canvas.addEventListener('pointerup', (event) => {
        if (!pressed) {
            return;
        }

        const moved = Math.hypot(event.clientX - pressed.x, event.clientY - pressed.y);
        pressed = null;

        if (moved > CLICK_SLOP_PX) {
            return;
        }

        const index = pick(event);
        if (index < 0) {
            return;
        }

        if (seatView) {
            enterSeatView(index, 700);
        } else {
            tooltip.hidden = true;
            openSeat(index);
        }
    });

    // In a seat the wheel zooms the lens, since the camera itself cannot move.
    canvas.addEventListener('wheel', (event) => {
        if (stopTour()) {
            goHome();
        } else if (seatView) {
            event.preventDefault();
            setFov(THREE.MathUtils.clamp(camera.fov + event.deltaY * 0.02, MIN_FOV, MAX_FOV));
        }
    }, { passive: false, capture: true });

    function resize() {
        const { clientWidth, clientHeight } = host;
        renderer.setPixelRatio(pixelRatio);
        renderer.setSize(clientWidth, clientHeight);
        composer.setPixelRatio(pixelRatio);
        composer.setSize(clientWidth, clientHeight);
        camera.aspect = clientWidth / clientHeight;
        camera.updateProjectionMatrix();
    }

    new ResizeObserver(resize).observe(host);

    /* Views from each listing, rendered a few at a time */

    const thumbTarget = new THREE.WebGLRenderTarget(THUMB_WIDTH, THUMB_HEIGHT);
    const thumbCamera = new THREE.PerspectiveCamera(46, THUMB_WIDTH / THUMB_HEIGHT, 0.2, 900);
    const thumbPixels = new Uint8Array(THUMB_WIDTH * THUMB_HEIGHT * 4);
    const thumbCache = new Map();
    const thumbQueue = [];
    const gamma = Uint8ClampedArray.from({ length: 256 }, (_, value) => Math.pow(Math.min(value / 255 * 1.5, 1), 1 / 2.2) * 255);
    let nextThumbAt = 0;

    const thumbWatcher = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                thumbWatcher.unobserve(entry.target);
                thumbQueue.push(entry.target);
            }
        });
    }, { root: listingList, rootMargin: '120px 0px' });

    function thumbnail(element, seat) {
        element.dataset.seat = seat;

        if (thumbCache.has(seat)) {
            element.append(copyCanvas(thumbCache.get(seat)));
        } else {
            thumbWatcher.observe(element);
        }
    }

    function copyCanvas(source) {
        const copy = document.createElement('canvas');
        copy.width = THUMB_WIDTH;
        copy.height = THUMB_HEIGHT;
        copy.getContext('2d').drawImage(source, 0, 0);

        return copy;
    }

    function renderThumbnail() {
        let element = thumbQueue.shift();

        while (element && !element.isConnected) {
            element = thumbQueue.shift();
        }
        if (!element) {
            return;
        }

        const seat = Number(element.dataset.seat);

        if (!thumbCache.has(seat)) {
            thumbCamera.position.copy(seatPoint(seat)).setY(data.seats.y[seat] + SEAT_EYE_HEIGHT);
            thumbCamera.lookAt(stageCentre);
            thumbCamera.updateMatrixWorld();

            const show = arena.getShow();
            const pinCount = pins.count;
            pins.count = 0;
            labels.forEach((label) => {
                label.visible = false;
            });
            arena.setShow(1);
            arena.setRoof(0);

            renderer.setRenderTarget(thumbTarget);
            renderer.clear();
            renderer.render(scene, thumbCamera);
            renderer.readRenderTargetPixels(thumbTarget, 0, 0, THUMB_WIDTH, THUMB_HEIGHT, thumbPixels);
            renderer.setRenderTarget(null);

            arena.setShow(show);
            arena.setRoof(camera.position.y);
            pins.count = pinCount;

            const output = document.createElement('canvas');
            output.width = THUMB_WIDTH;
            output.height = THUMB_HEIGHT;
            const context = output.getContext('2d');
            const image = context.createImageData(THUMB_WIDTH, THUMB_HEIGHT);

            for (let y = 0; y < THUMB_HEIGHT; y++) {
                for (let x = 0; x < THUMB_WIDTH; x++) {
                    const from = ((THUMB_HEIGHT - 1 - y) * THUMB_WIDTH + x) * 4;
                    const to = (y * THUMB_WIDTH + x) * 4;
                    image.data[to] = gamma[thumbPixels[from]];
                    image.data[to + 1] = gamma[thumbPixels[from + 1]];
                    image.data[to + 2] = gamma[thumbPixels[from + 2]];
                    image.data[to + 3] = 255;
                }
            }

            context.putImageData(image, 0, 0);
            thumbCache.set(seat, output);
        }

        element.textContent = '';
        element.append(copyCanvas(thumbCache.get(seat)));
    }

    /* Keep it smooth on slower machines */

    let frames = 0;
    let fpsSince = performance.now();

    function adaptQuality(now) {
        frames++;

        if (now - fpsSince < FPS_SAMPLE_MS) {
            return;
        }

        const fps = (frames * 1000) / (now - fpsSince);
        frames = 0;
        fpsSince = now;

        // Below the floor the page is being throttled in the background, not struggling.
        if (fps >= LOW_FPS || fps < THROTTLED_FPS) {
            return;
        }

        if (pixelRatio > 1) {
            pixelRatio = 1;
            resize();
        } else if (bloom.enabled) {
            bloom.enabled = false;
        } else if (pixelRatio > 0.75) {
            pixelRatio = 0.75;
            resize();
        }
    }

    /* Frame loop */

    const clock = new THREE.Clock();
    const pinPlace = new THREE.Object3D();
    const look = new THREE.Vector3();

    function updatePins(time) {
        pins.count = seatView ? 0 : selected.length;

        selected.forEach((index, slot) => {
            pinPlace.position.copy(seatPoint(index));
            pinPlace.position.y += 2.1 + Math.sin(time * 3 + slot) * 0.14;
            pinPlace.updateMatrix();
            pins.setMatrixAt(slot, pinPlace.matrix);
        });
        pins.instanceMatrix.needsUpdate = true;
    }

    function frame(now) {
        requestAnimationFrame(frame);

        const delta = Math.min(clock.getDelta(), 0.1);
        const time = clock.elapsedTime;

        updatePins(reducedMotion ? 0 : time);

        if (pendingMove) {
            handleHover(pendingMove);
            pendingMove = null;
        }

        if (flight) {
            updateFlight(now);
        } else if (tour) {
            updateTour(now);
        } else if (seatView) {
            look.copy(stageCentre).sub(camera.position);
            look.applyAxisAngle(THREE.Object3D.DEFAULT_UP, -seatView.yaw);
            look.y += seatView.pitch * 30;
            look.add(camera.position);
            camera.lookAt(look);
        } else {
            controls.update();
        }

        if (!flight && camera.position.distanceTo(nearCentre) > NEAR_REFRESH_DISTANCE) {
            refreshNearSeats();
        }

        const music = live.update(delta);

        arena.setEnergy(music.bass, music.treble, music.kick);
        arena.update(time, camera, Boolean(seatView || tour), delta);
        bloom.strength = 0.26 + music.bass * 0.14;
        updateRipples(delta);
        liveLevels.forEach((bar, index) => {
            bar.style.transform = `scaleY(${0.1 + music.bars[index] * 0.9})`;
        });

        if (thumbQueue.length && now > nextThumbAt) {
            nextThumbAt = now + THUMB_GAP_MS;
            renderThumbnail();
        }

        labels.forEach((label) => {
            label.visible = !seatView && !tour && label.position.distanceTo(camera.position) > LABEL_HIDE_DISTANCE;
        });

        composer.render(delta);
        adaptQuality(now);
    }

    const tourButton = document.getElementById('tourButton');
    tourButton.hidden = reducedMotion;
    tourButton.addEventListener('click', () => {
        if (stopTour()) {
            goHome();
        } else {
            startTour();
        }
    });

    paintAll();
    resize();

    if (!reducedMotion) {
        fly(homeCamera.clone(), homeTarget.clone(), 2800);
        const spin = setInterval(() => {
            if (!flight) {
                controls.autoRotate = true;
                clearInterval(spin);
            }
        }, 200);
    }

    requestAnimationFrame(frame);

    return { paintSeat, paintAll, paintColours, ripple, goHome, enterSeatView, exitSeatView, thumbnail };
}

function createSeatMesh(geometry, count, parameters) {
    const shaped = geometry.clone();
    shaped.setAttribute('aKeep', new THREE.InstancedBufferAttribute(new Float32Array(count), 1));

    const mesh = new THREE.InstancedMesh(shaped, seatMaterial({ color: '#ffffff', ...parameters }), count);
    mesh.frustumCulled = false;
    mesh.setColorAt(0, new THREE.Color('#ffffff'));

    return mesh;
}

function createPins() {
    const geometry = new THREE.ConeGeometry(0.34, 0.8, 16);
    geometry.rotateX(Math.PI);

    const pins = new THREE.InstancedMesh(geometry, new THREE.MeshBasicMaterial({ color: new THREE.Color(1.6, 1.25, 0.2) }), MAX_SEATS);
    pins.frustumCulled = false;
    pins.count = 0;

    return pins;
}
