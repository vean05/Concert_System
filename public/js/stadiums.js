// Six more buildings: a monolith arena, a neon arena, a cube arena in the round, an
// open-air stadium, a stadium under a glass canopy, and a stadium under a great
// arch. Like venues.js, each one builds into the kit from buildArena and returns
// its moving lights, its mood and anything that animates.

import * as THREE from 'three';
import {
    Parts, TAU, addAisles, addDrumKit, canvasTexture, instanced, ledBand, placed, ring,
    softDotTexture, strut, superellipsePoint, videoMaterial,
} from './arena-scene.js';
import {
    addCarpets, addEndCaps, addLineArray, addTerraces, addTrussLines, barStripMaterial,
    connect, disc, frontRail, micStand, ribbon, seeded, sweepOf, wedge,
} from './venues.js';

const { lerp, smoothstep } = THREE.MathUtils;

const last = (list) => list[list.length - 1];

/* Shared pieces */

/** Floor, terraces, aisles and rails, in the order every venue here needs them. */
function seatingBowl({ data, blocks, rows, materials, bowl: group }, { floor, strip }) {
    const layout = data.layout;
    const tiers = layout.bowls;
    const cove = barStripMaterial(new THREE.Color(...strip));

    group.add(disc(floor[0], floor[1], layout.corner_power, materials.floor, -0.02));
    addCarpets(group, { blocks, data }, materials.carpet);
    addTerraces(group, layout, materials.terrace);
    addAisles(group, layout, rows, data.seats, materials);

    tiers.forEach((tier, index) => {
        if (sweepOf(layout, tier) < Math.PI) {
            addEndCaps(group, layout, tier, materials.mask, 0);
        }
        frontRail(group, layout, tier, materials.glass, materials.steel);
        if (tiers[index + 1]) {
            connect(group, layout, tier, tiers[index + 1], materials.walkway, cove);
        }
    });

    return { cove, band: ribbon(group, layout, last(tiers), data.stageName) };
}

function addLand(group, material, x = 0, size = 1100) {
    const land = new THREE.Mesh(new THREE.PlaneGeometry(size, size).rotateX(-Math.PI / 2), material);
    land.position.set(x, -0.1, 0);
    group.add(land);
}

function addDeck({ data, parts, stage }, { x, width, depth, height }) {
    parts.box('deck', width, 0.12, depth, x, height - 0.06, 0);
    parts.box('mask', width, height - 0.12, depth, x, (height - 0.12) / 2, 0);
    parts.box('riserEdge', 0.05, 0.05, depth, x + width / 2, height, 0);

    const name = ledBand(data.stageName);
    name.repeat.set(depth / 18, 1);

    const front = new THREE.Mesh(new THREE.PlaneGeometry(depth, 1.05), new THREE.MeshBasicMaterial({ map: name, color: new THREE.Color(1.3, 1.3, 1.3) }));
    front.rotation.y = Math.PI / 2;
    front.position.set(x + width / 2 + 0.02, height / 2 - 0.08, 0);
    stage.add(front);

    return name;
}

function addRunway({ parts }, from, to, width, height) {
    const length = to - from;
    const middle = (from + to) / 2;

    parts.box('deck', length, 0.1, width, middle, height - 0.05, 0);
    parts.box('mask', length, height - 0.1, width, middle, (height - 0.1) / 2, 0);
    [-1, 1].forEach((side) => parts.box('riserEdge', length, 0.05, 0.05, middle, height, (side * width) / 2));
}

function addRoundStage({ parts, stage, uniforms }, x, radius, height) {
    parts.add('mask', new THREE.CylinderGeometry(radius, radius, height, 56).translate(x, height / 2, 0));
    parts.add('riserEdge', new THREE.TorusGeometry(radius, 0.05, 8, 72).rotateX(Math.PI / 2).translate(x, height + 0.02, 0));

    const top = new THREE.Mesh(new THREE.CircleGeometry(radius - 0.05, 56).rotateX(-Math.PI / 2), videoMaterial(uniforms, { grid: [260, 260], crop: 1, boost: 0.8 }));
    top.position.set(x, height + 0.02, 0);
    stage.add(top);
}

function addBand(parts, x, floor, spread) {
    parts.box('riser', 3.4, 0.7, 7.5, x, floor + 0.35, 0);
    parts.box('riserEdge', 0.05, 0.05, 7.5, x + 1.7, floor + 0.7, 0);
    addDrumKit(parts, x, floor + 0.7, 0);
    [-spread, 0, spread].forEach((z) => {
        micStand(parts, x + 6, floor, z);
        wedge(parts, x + 7.2, floor, z + 0.8);
    });
}

function addScreen({ stage, uniforms }, width, height, x, y, z, yaw, options = {}) {
    // The clips are 16:9: crop them to the panel's shape rather than stretching them.
    const geometry = new THREE.PlaneGeometry(width, height);
    const shape = width / height / (16 / 9);
    const uv = geometry.attributes.uv;

    if (shape < 1) {
        for (let i = 0; i < uv.count; i++) {
            uv.setX(i, 0.5 + (uv.getX(i) - 0.5) * shape);
        }
    }

    const screen = new THREE.Mesh(
        geometry,
        videoMaterial(uniforms, { grid: [Math.round(width * 30), Math.round(height * 30)], crop: Math.min(1, 1 / shape), ...options })
    );
    screen.rotation.y = yaw;
    screen.position.set(x, y, z);
    stage.add(screen);

    return screen;
}

function addBackdrop({ stage, materials }, x, width, height) {
    const geometry = new THREE.PlaneGeometry(width, height);
    const uv = geometry.attributes.uv;

    for (let i = 0; i < uv.count; i++) {
        uv.setX(i, uv.getX(i) * (width / 2));
    }

    const backdrop = new THREE.Mesh(geometry, materials.drape);
    backdrop.rotation.y = Math.PI / 2;
    backdrop.position.set(x, height / 2, 0);
    stage.add(backdrop);
}

/** The wall behind the top row, and a shallow domed roof with lit seams. */
function addRoofShell({ roof, standard, materials, data }, { radiusX, radiusZ, wallFrom, top, rise, glow }) {
    const power = data.layout.corner_power;
    const radii = (scale) => [radiusX * scale, radiusZ * scale];
    const height = (scale) => top + rise * (1 - scale * scale);

    const wall = standard({
        map: canvasTexture(128, 256, (context, width, tall) => {
            context.fillStyle = '#0d0d12';
            context.fillRect(0, 0, width, tall);
            context.fillStyle = '#1b1b24';
            context.fillRect(width * 0.12, 0, width * 0.76, tall);
        }),
        roughness: 0.85,
    });
    roof.add(new THREE.Mesh(ring({ profile: [[1, wallFrom], [1, top]], radii, power, segments: 160, repeatU: 80 }), wall));

    const profile = [];
    for (let i = 0; i <= 12; i++) {
        const scale = Math.max(1 - i / 12, 0.002);
        profile.push([scale, height(scale)]);
    }

    const panel = (lit) => (context, width, tall) => {
        context.fillStyle = lit ? '#000' : '#0f0f15';
        context.fillRect(0, 0, width, tall);
        context.fillStyle = lit ? '#fff' : '#23232d';
        [0.3, 0.62, 0.84].forEach((row) => context.fillRect(0, row * tall, width, 3));
        context.fillStyle = lit ? '#000' : '#191922';
        for (let i = 0; i < 10; i++) {
            context.fillRect((i / 10) * width, 0, 3, tall);
        }
    };
    const skin = standard({
        map: canvasTexture(1024, 256, panel(false)),
        emissiveMap: canvasTexture(1024, 256, panel(true)),
        emissive: glow,
        emissiveIntensity: 1.2,
    });
    roof.add(new THREE.Mesh(ring({ profile, radii, power, segments: 96, repeatU: 3, smooth: true }), skin));

    const beams = [];
    for (let i = 0; i < 24; i++) {
        const angle = (i / 24) * TAU;
        let previous = null;

        for (let step = 0; step <= 7; step++) {
            const scale = 1 - step * 0.12;
            const [x, z] = superellipsePoint(angle, radiusX * scale, radiusZ * scale, power);
            const current = new THREE.Vector3(x, height(scale) - 0.8, z);

            if (previous) {
                beams.push(strut(previous, current, 1));
            }
            previous = current;
        }
    }
    roof.add(instanced(new THREE.BoxGeometry(0.4, 1, 0.8), materials.darkMetal, beams));

    return skin;
}

function addOuterWall(group, layout, material, radiusX, radiusZ, top) {
    group.add(new THREE.Mesh(ring({
        profile: [[1, 0], [1, top]],
        radii: (scale) => [radiusX * scale, radiusZ * scale],
        power: layout.corner_power,
        segments: 200,
        repeatU: 120,
    }), material));
}

function addNightSky(stage, { seed = 7, low = '#16203f' } = {}) {
    const random = seeded(seed);
    const sky = new THREE.Mesh(new THREE.SphereGeometry(600, 32, 18), new THREE.ShaderMaterial({
        uniforms: {
            uTop: { value: new THREE.Color('#02030a') },
            uMiddle: { value: new THREE.Color('#080c20') },
            uLow: { value: new THREE.Color(low) },
        },
        side: THREE.BackSide,
        depthWrite: false,
        fog: false,
        vertexShader: 'varying vec3 vDirection; void main() { vDirection = position; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }',
        fragmentShader: `
            varying vec3 vDirection;
            uniform vec3 uTop, uMiddle, uLow;
            void main() {
                float height = max(normalize(vDirection).y, 0.0);
                vec3 colour = mix(uLow, uMiddle, smoothstep(0.0, 0.18, height));
                gl_FragColor = vec4(mix(colour, uTop, smoothstep(0.12, 0.6, height)), 1.0);
            }`,
    }));
    sky.frustumCulled = false;
    stage.add(sky);

    const count = 1500;
    const positions = new Float32Array(count * 3);

    for (let i = 0; i < count; i++) {
        const around = random() * TAU;
        const up = Math.pow(random(), 0.7) * 0.95 + 0.05;
        const flat = Math.sqrt(1 - up * up);
        positions.set([Math.cos(around) * flat * 580, up * 580, Math.sin(around) * flat * 580], i * 3);
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    stage.add(new THREE.Points(geometry, new THREE.PointsMaterial({
        map: softDotTexture(),
        size: 2.4,
        sizeAttenuation: false,
        transparent: true,
        depthWrite: false,
        fog: false,
        blending: THREE.AdditiveBlending,
        color: new THREE.Color(1.5, 1.5, 1.7),
    })));
}

function pitchTexture() {
    const texture = canvasTexture(1024, 512, (context, width, height) => {
        for (let i = 0; i < 12; i++) {
            context.fillStyle = i % 2 ? '#1c4a2a' : '#235633';
            context.fillRect((i / 12) * width, 0, width / 12 + 1, height);
        }
    });
    texture.repeat.set(0.012, 0.016);

    return texture;
}

/* Crypto.com Arena: a bare stage, one tall slanted screen and a long catwalk */

function monolith(kit) {
    const { materials, parts, roof, stage, movingLights } = kit;
    const DECK = { x: -31.5, width: 11, depth: 26, height: 1.6 };

    materials.carpet.color.set('#1f1419');
    materials.walkway.color.set('#241a20');
    materials.floor.color.set('#0a0608');

    const { cove, band } = seatingBowl(kit, { floor: [46, 33], strip: [1.8, 0.25, 0.3] });
    const name = addDeck(kit, DECK);

    addRunway(kit, DECK.x + DECK.width / 2, 8, 3.2, 1.2);
    addRoundStage(kit, 12, 4.4, 1.2);
    addBackdrop(kit, -39.5, 66, 30);

    const slab = addScreen(kit, 11, 25, -36, 14.8, 0, Math.PI / 2, { boost: 1.2 });
    slab.rotateX(0.09);
    [-1, 1].forEach((side) => {
        addScreen(kit, 4.6, 15, -36.5, 9.8, side * 10.5, Math.PI / 2 - side * 0.24).rotateX(0.09);
    });
    parts.box('darkMetal', 0.5, 2.2, 13, -36.6, 1.2, 0);

    /* Curtains of light strips either side, rippling with the bass */

    const stripPlaces = [];
    const stripSpots = [];

    [-1, 1].forEach((side) => {
        for (let i = 0; i < 22; i++) {
            const z = side * (14.5 + i * 0.75);
            stripPlaces.push(placed(-37.5 + i * 0.1, 12.5, z, 0, 0.09, 24, 0.09));
            stripSpots.push(i);
        }
    });

    const strips = instanced(new THREE.BoxGeometry(1, 1, 1), new THREE.MeshBasicMaterial({ color: '#ffffff' }), stripPlaces);
    const stripColour = new THREE.Color();
    stage.add(strips);

    addBand(parts, -33, DECK.height, 5.5);
    micStand(parts, 12, 1.2, 0);
    addTrussLines(stage, materials.steel, [-27, -33], 15, 18, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -24.5, 16, side * 18, side * 0.25));

    const fixtures = [];
    for (let i = 0; i < 8; i++) {
        const z = -13 + i * 3.7;
        fixtures.push({ x: -27, y: 17.7, z, tilt: 0.5 }, { x: -33, y: 17.7, z, tilt: -0.3 });
    }

    const roofSkin = addRoofShell(kit, { radiusX: 71, radiusZ: 58, wallFrom: 19, top: 31, rise: 7, glow: '#ff2a3d' });

    const flown = [];
    for (let x = -10; x <= 30; x += 10) {
        [-1, 1].forEach((side) => flown.push({ x, y: 27, z: side * 14, tilt: -0.46, swing: 0.3, sweep: 0.28 }));
        kit.roofParts.box('steel', 0.3, 0.3, 30, x, 27.4, 0);
    }

    return {
        lights: [...movingLights(fixtures, { length: 46, radius: 5 }), ...movingLights(flown, { length: 34, radius: 4 }, roof)],
        bands: [band, name],
        mood: {
            house: [1.45, 0.16],
            overhead: [1, 0.08],
            terrace: '#5a5459',
            houseSeat: '#361820',
            skyLight: '#ffe0dc',
            groundLight: '#221216',
            spill: [-20, 9, 0],
            spillPower: 640,
            stageGlow: [-68, 16, 0],
            roofFade: [38, 56],
            haze: { x: -18, z: 0, width: 30, depth: 30 },
            tint: { colour: '#ff2038', amount: 0.55 },
            background: '#0b0506',
            fog: [170, 340],
            confetti: { x: -4, z: 0, width: 56, depth: 36, top: 18 },
        },
        onShow(value) {
            roofSkin.emissiveIntensity = lerp(1.2, 0.25, value);
        },
        onFrame(time, colour, show, { bass, phones }) {
            roofSkin.emissiveIntensity = lerp(1.2, 0.25, show) * (1 - phones * 0.8);
            cove.color.copy(colour).multiplyScalar(1.6);

            stripSpots.forEach((spot, index) => {
                const ripple = Math.pow(Math.sin(spot * 0.5 - time * 2.4) * 0.5 + 0.5, 3);
                const level = lerp(0.05, 0.12 + ripple * (0.9 + bass * 1.6), show) * (1 - phones * 0.8);
                strips.setColorAt(index, stripColour.copy(colour).multiplyScalar(level * 3));
            });
            strips.instanceColor.needsUpdate = true;
        },
    };
}

/* T-Mobile Arena: neon arches, a T-shaped runway and a mirrored floor */

function neon(kit) {
    const { materials, parts, roof, stage, uniforms, movingLights } = kit;
    const DECK = { x: -29.5, width: 11, depth: 26, height: 1.5 };
    const RUNWAY = { width: 3, height: 1.2, bar: -2 };

    materials.carpet.color.set('#20102a');
    materials.walkway.color.set('#231535');
    materials.floor.color.set('#0a0612');
    materials.floor.roughness = 0.12;
    materials.floor.metalness = 0.75;
    materials.floor.envMapIntensity = 0.7;

    const { cove, band } = seatingBowl(kit, { floor: [44, 32], strip: [2, 0.4, 1.6] });
    const name = addDeck(kit, DECK);

    addRunway(kit, DECK.x + DECK.width / 2, RUNWAY.bar, RUNWAY.width, RUNWAY.height);
    parts.box('deck', RUNWAY.width, 0.1, 25, RUNWAY.bar, RUNWAY.height - 0.05, 0);
    parts.box('mask', RUNWAY.width, RUNWAY.height - 0.1, 25, RUNWAY.bar, (RUNWAY.height - 0.1) / 2, 0);
    [-1, 1].forEach((side) => parts.box('riserEdge', 0.05, 0.05, 25, RUNWAY.bar + (side * RUNWAY.width) / 2, RUNWAY.height, 0));

    addBackdrop(kit, -38.5, 66, 30);
    addScreen(kit, 22, 12.5, -36.6, 8.8, 0, Math.PI / 2, { boost: 1.2 });

    /* Nested neon arches stepping forward from the screen */

    const arches = [];

    for (let i = 0; i < 8; i++) {
        const points = [];
        const halfWidth = 13.5 + i * 1.7;
        const height = 15.5 + i * 1.3;

        for (let step = 0; step <= 48; step++) {
            const [z, y] = superellipsePoint(Math.PI * (step / 48), halfWidth, height, 3.4);
            points.push(new THREE.Vector3(-36 + i * 1.5, Math.abs(y) + 0.1, z));
        }

        const material = new THREE.MeshBasicMaterial({ color: '#ffffff' });
        stage.add(new THREE.Mesh(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(points), 96, 0.13, 6), material));
        arches.push(material);
    }

    /* The centre-hung scoreboard */

    const board = new THREE.Group();
    const boardScreen = new THREE.Mesh(new THREE.CylinderGeometry(10, 10, 6, 8, 1, true), videoMaterial(uniforms, { grid: [720, 90], repeatU: 4, crop: 0.5 }));
    board.add(boardScreen);
    [-1, 1].forEach((side) => {
        const rim = new THREE.Mesh(new THREE.CylinderGeometry(10.2, 10.2, 0.3, 8), materials.darkMetal);
        rim.position.y = side * 3.1;
        board.add(rim);
    });
    board.position.set(8, 24, 0);
    roof.add(board);

    addBand(parts, -31, DECK.height, 5.5);
    micStand(parts, RUNWAY.bar, RUNWAY.height, 0);
    [-1, 1].forEach((side) => micStand(parts, RUNWAY.bar, RUNWAY.height, side * 10));
    addTrussLines(stage, materials.steel, [-24, -32], 16, 19, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -22, 16.5, side * 20, side * 0.25));

    const fixtures = [];
    for (let i = 0; i < 9; i++) {
        const z = -14 + i * 3.5;
        fixtures.push({ x: -24, y: 18.7, z, tilt: 0.5 }, { x: -32, y: 18.7, z, tilt: -0.3 });
    }

    const roofSkin = addRoofShell(kit, { radiusX: 72, radiusZ: 59, wallFrom: 20, top: 31, rise: 7, glow: '#d02cff' });
    const archColour = new THREE.Color();

    return {
        lights: movingLights(fixtures, { length: 46, radius: 5 }),
        bands: [band, name],
        mood: {
            house: [1.5, 0.2],
            overhead: [1.05, 0.1],
            terrace: '#5a5163',
            houseSeat: '#351a4c',
            skyLight: '#f0d6ff',
            groundLight: '#241238',
            spill: [-20, 9, 0],
            spillPower: 700,
            stageGlow: [-68, 16, 0],
            roofFade: [38, 56],
            haze: { x: -16, z: 0, width: 30, depth: 30 },
            tint: { colour: '#ff2ec4', amount: 0.3 },
            background: '#0a0612',
            fog: [170, 340],
            confetti: { x: -6, z: 0, width: 54, depth: 36, top: 18 },
        },
        onFrame(time, colour, show, { bass, kick, phones }) {
            board.rotation.y = time * 0.08;
            roofSkin.emissiveIntensity = lerp(1.2, 0.35, show) * (1 - phones * 0.8);
            cove.color.copy(colour).multiplyScalar(1.8);

            arches.forEach((material, index) => {
                const hue = (0.86 + index * 0.035 - time * 0.03) % 1;
                const pulse = Math.pow(Math.sin(index * 0.8 - time * 3) * 0.5 + 0.5, 2);
                const level = lerp(0.8, 0.8 + pulse * 1.4 + bass * 1.6 + kick, show) * (1 - phones * 0.8);
                material.color.copy(archColour.setHSL((hue + 1) % 1, 1, 0.55)).multiplyScalar(level);
            });
        },
    };
}

/* Scotiabank Arena: a square stage in the round, under a huge hanging cube */

function cube(kit) {
    const { materials, parts, roof, roofParts, stage, uniforms, movingLights } = kit;
    const DECK = { half: 7.6, height: 1.1 };
    const LOOP = { half: 13, height: 15 };

    materials.carpet.color.set('#141a22');
    materials.walkway.color.set('#1d2128');
    materials.floor.color.set('#08090c');

    const { cove, band } = seatingBowl(kit, { floor: [38, 34], strip: [1.8, 0.25, 0.3] });

    parts.box('deck', DECK.half * 2, 0.12, DECK.half * 2, 0, DECK.height - 0.06, 0);
    parts.box('mask', DECK.half * 2, DECK.height - 0.12, DECK.half * 2, 0, (DECK.height - 0.12) / 2, 0);
    [-1, 1].forEach((side) => {
        parts.box('riserEdge', 0.05, 0.05, DECK.half * 2, side * DECK.half, DECK.height, 0);
        parts.box('riserEdge', DECK.half * 2, 0.05, 0.05, 0, DECK.height, side * DECK.half);
    });

    const top = new THREE.Mesh(new THREE.PlaneGeometry(DECK.half * 1.5, DECK.half * 1.5).rotateX(-Math.PI / 2), videoMaterial(uniforms, { grid: [240, 240], crop: 1, boost: 0.7 }));
    top.position.y = DECK.height + 0.02;
    stage.add(top);

    parts.box('riser', 3.6, 0.7, 3.6, 0, DECK.height + 0.35, 0);
    addDrumKit(parts, 0, DECK.height + 0.7, 0);
    [[5.4, 0], [-5.4, 0], [0, 5.4], [0, -5.4]].forEach(([x, z]) => micStand(parts, x, DECK.height, z));

    /* The cube, with a small one spinning the other way inside its frame */

    const cubeGroup = new THREE.Group();
    const big = new THREE.Mesh(new THREE.BoxGeometry(17, 9.5, 17), videoMaterial(uniforms, { grid: [320, 180], crop: 1, boost: 1.15 }));
    const small = new THREE.Mesh(new THREE.BoxGeometry(5, 5, 5), videoMaterial(uniforms, { grid: [120, 120], crop: 1, boost: 1.3 }));
    small.position.y = -8.4;
    cubeGroup.add(big, small);
    cubeGroup.position.y = 21;
    stage.add(cubeGroup);

    /* A square truss around the stage */

    const loop = [];
    [-1, 1].forEach((side) => {
        for (let step = -LOOP.half; step <= LOOP.half; step += 2) {
            loop.push(strut(new THREE.Vector3(step - 1, LOOP.height, side * LOOP.half), new THREE.Vector3(step + 1, LOOP.height, side * LOOP.half), 1));
            loop.push(strut(new THREE.Vector3(side * LOOP.half, LOOP.height, step - 1), new THREE.Vector3(side * LOOP.half, LOOP.height, step + 1), 1));
        }
    });
    stage.add(instanced(new THREE.BoxGeometry(0.45, 1, 0.45), materials.steel, loop));

    const fixtures = [];

    [0, 1, 2, 3].forEach((side) => {
        const angle = (side / 4) * TAU;

        for (let i = -2; i <= 2; i++) {
            const along = i * 5.2;
            fixtures.push({
                x: Math.cos(angle) * LOOP.half - Math.sin(angle) * along,
                y: LOOP.height - 0.3,
                z: Math.sin(angle) * LOOP.half + Math.cos(angle) * along,
                yaw: Math.PI - angle,
                tilt: -0.52,
                swing: 0.32,
                sweep: 0.3,
            });
        }

        const corner = angle + Math.PI / 4;
        addLineArray(stage, materials.speaker, Math.cos(corner) * LOOP.half * 1.41, LOOP.height, Math.sin(corner) * LOOP.half * 1.41, -corner);
    });

    for (let i = 0; i < 4; i++) {
        const [x, z] = [[8, 8], [-8, 8], [-8, -8], [8, -8]][i];
        roofParts.box('steel', 0.05, 12, 0.05, x, 32, z);
    }

    const roofSkin = addRoofShell(kit, { radiusX: 64, radiusZ: 60, wallFrom: 18, top: 31, rise: 7, glow: '#ff3440' });

    return {
        lights: movingLights(fixtures, { length: 44, radius: 5 }),
        bands: [band],
        mood: {
            house: [1.5, 0.18],
            overhead: [1.05, 0.1],
            terrace: '#55585f',
            houseSeat: '#1c2433',
            skyLight: '#e2e8f4',
            groundLight: '#1a1c22',
            spill: [0, 11, 0],
            spillPower: 900,
            spillReach: 90,
            stageGlow: [0, 70, 0],
            roofFade: [38, 56],
            haze: { x: 0, z: 0, width: 34, depth: 34 },
            tint: { colour: '#ff2030', amount: 0.3 },
            background: '#070809',
            fog: [170, 340],
            confetti: { x: 0, z: 0, width: 46, depth: 42, top: 19 },
        },
        onFrame(time, colour, show, { bass, kick, phones }) {
            cubeGroup.rotation.y = time * 0.07;
            cubeGroup.position.y = 21 + Math.sin(time * 0.4) * 0.7 - bass * show * 0.7;
            cubeGroup.scale.setScalar(1 + kick * show * 0.02);
            small.rotation.y = -time * 0.5;
            roofSkin.emissiveIntensity = lerp(1.2, 0.3, show) * (1 - phones * 0.8);
            cove.color.copy(colour).multiplyScalar(1.6);
        },
    };
}

/* MetLife Stadium: open to the sky, with a runway out to a second stage */

function openStadium(kit) {
    const { data, materials, parts, stage, bowl: seatGroup, standard, movingLights } = kit;
    const layout = data.layout;
    const DECK = { x: -52, width: 16, depth: 46, height: 2 };
    const OUTER = { x: 107, z: 85, top: 29 };

    materials.carpet.color.set('#20232b');
    materials.walkway.color.set('#23262e');
    materials.floor.color.set('#ffffff');
    materials.floor.map = pitchTexture();
    materials.floor.roughness = 0.9;

    addLand(seatGroup, standard({ color: '#090b10', roughness: 1 }));
    addNightSky(stage);

    const { cove, band } = seatingBowl(kit, { floor: [66, 44], strip: [0.5, 0.9, 1.7] });

    addOuterWall(seatGroup, layout, standard({ color: '#23262e', roughness: 0.6, metalness: 0.5 }), OUTER.x, OUTER.z, OUTER.top);

    /* Tall aluminium fins round the rim, lit from behind */

    const fins = [];

    for (let i = 0; i < 220; i++) {
        const [x, z] = superellipsePoint((i / 220) * TAU, OUTER.x + 1, OUTER.z + 1, layout.corner_power);
        fins.push(placed(x, OUTER.top / 2 + 2, z, -Math.atan2(z, x), 0.5, OUTER.top + 4, 1.1));
    }

    const finMaterial = new THREE.MeshBasicMaterial({ color: '#ffffff' });
    seatGroup.add(instanced(new THREE.BoxGeometry(1, 1, 1), finMaterial, fins));

    /* Floodlight banks and corner screens */

    const floodMaterial = new THREE.MeshBasicMaterial({ color: new THREE.Color(5, 5, 5.4) });
    const floods = [];

    [[1, 1], [1, -1], [-1, 1], [-1, -1]].forEach(([sideX, sideZ]) => {
        const x = sideX * 92;
        const z = sideZ * 72;

        parts.box('steel', 0.8, 42, 0.8, x, 24, z);
        for (let i = 0; i < 12; i++) {
            floods.push(placed(x + ((i % 4) - 1.5) * 2.4 * sideZ, 44 + Math.floor(i / 4) * 2, z - ((i % 4) - 1.5) * 2.4 * sideX, 0, 1.8, 1.6, 0.3));
        }
    });
    stage.add(instanced(new THREE.BoxGeometry(1, 1, 1), floodMaterial, floods));

    [-1, 1].forEach((side) => {
        addScreen(kit, 40, 16, 104, 36, side * 42, -Math.PI / 2 + side * 0.34);
    });

    /* Stage, runway, second stage */

    const name = addDeck(kit, DECK);

    addRunway(kit, DECK.x + DECK.width / 2, 30, 3, 1.3);
    addRoundStage(kit, 36, 7, 1.3);
    addBackdrop(kit, -62, 84, 30);

    addScreen(kit, 42, 19, -60.5, 13.5, 0, Math.PI / 2, { boost: 1.15 });
    [-1, 1].forEach((side) => addScreen(kit, 13, 17, -58.5, 13, side * 29, Math.PI / 2 - side * 0.28));

    addBand(parts, -56, DECK.height, 8);
    micStand(parts, 36, 1.3, 0);
    addTrussLines(stage, materials.steel, [-46, -58], 25, 25, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -44, 23, side * 30, side * 0.2));

    const fixtures = [];
    for (let i = 0; i < 10; i++) {
        const z = -22.5 + i * 5;
        fixtures.push({ x: -46, y: 24.7, z, tilt: 0.52 }, { x: -58, y: 24.7, z, tilt: -0.28 });
    }

    return {
        lights: movingLights(fixtures, { length: 74, radius: 8 }),
        bands: [band, name],
        mood: {
            house: [2, 0.22],
            overhead: [1.4, 0.1],
            beam: [0.09, 0.26],
            terrace: '#62666f',
            houseSeat: '#1d2a44',
            skyLight: '#d6e2ff',
            groundLight: '#20242e',
            spill: [-34, 18, 0],
            spillPower: 3000,
            spillReach: 150,
            stageGlow: [-110, 40, 0],
            roofFade: [900, 1000],
            haze: { x: -30, z: 0, width: 44, depth: 50 },
            background: '#05060f',
            fog: [320, 640],
            confetti: { x: -10, z: 0, width: 92, depth: 66, top: 26 },
        },
        onFrame(time, colour, show, { bass, phones }) {
            finMaterial.color.copy(colour).multiplyScalar(lerp(0.5, 0.5 + bass, show) * (1 - phones * 0.8));
            floodMaterial.color.setScalar(lerp(5, 0.15, show) * (1 - phones * 0.5));
            cove.color.copy(colour).multiplyScalar(1.5);
        },
    };
}

/* SoFi Stadium: a bowl under a floating glass canopy, with an oval video ring */

function canopy(kit) {
    const { data, materials, parts, roof, roofParts, stage, uniforms, bowl: seatGroup, standard, movingLights } = kit;
    const layout = data.layout;
    const DECK = { x: -47, width: 14, depth: 42, height: 2 };
    const ROOF = { x: 128, z: 112, height: 48, rise: 9 };
    const RING = { x: 46, z: 31, y: 34, height: 11 };

    materials.carpet.color.set('#1a2030');
    materials.walkway.color.set('#212737');
    materials.floor.color.set('#0a0e18');

    addLand(seatGroup, standard({ color: '#0a0d14', roughness: 1 }));
    addNightSky(stage, { seed: 21, low: '#1f2650' });

    const { cove, band } = seatingBowl(kit, { floor: [64, 48], strip: [0.5, 1.2, 1.6] });
    addOuterWall(seatGroup, layout, standard({ color: '#1c2230', roughness: 0.35, metalness: 0.6 }), 104, 88, 26);

    /* The canopy */

    const tiles = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#2a3a52' : '#a9bdd6';
        context.fillRect(0, 0, width, height);
        context.strokeStyle = lit ? '#b8d4ff' : '#4d5a6e';
        context.lineWidth = 3;
        context.beginPath();
        context.moveTo(0, height / 2);
        context.lineTo(width / 2, 0);
        context.lineTo(width, height / 2);
        context.lineTo(width / 2, height);
        context.closePath();
        context.stroke();
    };
    const glass = standard({
        map: canvasTexture(128, 128, tiles(false)),
        emissiveMap: canvasTexture(128, 128, tiles(true)),
        emissive: '#9cc4ff',
        emissiveIntensity: 1,
        transparent: true,
        opacity: 0.42,
        depthWrite: false,
        roughness: 0.25,
        side: THREE.DoubleSide,
    });
    const roofHeight = (scale) => ROOF.height + ROOF.rise * (1 - scale * scale);
    const profile = [];

    for (let i = 0; i <= 14; i++) {
        const scale = Math.max(1 - i / 14, 0.002);
        profile.push([scale, roofHeight(scale)]);
    }

    roof.add(new THREE.Mesh(ring({
        profile,
        radii: (scale) => [ROOF.x * scale, ROOF.z * scale],
        power: 2.2,
        segments: 120,
        repeatU: 120,
        metresV: 12,
        smooth: true,
    }), glass));

    const masts = [];

    for (let i = 0; i < 24; i++) {
        const [x, z] = superellipsePoint((i / 24) * TAU, ROOF.x - 6, ROOF.z - 6, 2.2);
        masts.push(strut(new THREE.Vector3(x, 0, z), new THREE.Vector3(x * 0.96, roofHeight(0.95), z * 0.96), 1));
    }
    roof.add(instanced(new THREE.CylinderGeometry(0.4, 0.5, 1, 8), materials.steel, masts));

    /* The ring of video, showing both ways */

    const videoRing = new THREE.Group();
    const loop = new THREE.Mesh(
        new THREE.CylinderGeometry(1, 1, RING.height, 120, 1, true).scale(RING.x, 1, RING.z),
        videoMaterial(uniforms, { grid: [1600, 220], repeatU: 4, crop: 0.5, boost: 1.1 })
    );
    videoRing.add(loop);

    [-1, 1].forEach((side) => {
        const edge = new THREE.Mesh(new THREE.TorusGeometry(1, 0.012, 6, 120).rotateX(Math.PI / 2).scale(RING.x, 1, RING.z), materials.darkMetal);
        edge.position.y = (side * RING.height) / 2;
        videoRing.add(edge);
    });
    videoRing.position.y = RING.y;
    roof.add(videoRing);

    for (let i = 0; i < 12; i++) {
        const angle = (i / 12) * TAU;
        const x = Math.cos(angle) * RING.x;
        const z = Math.sin(angle) * RING.z;
        roofParts.box('steel', 0.05, 13, 0.05, x, RING.y + RING.height / 2 + 6.5, z);
    }

    /* Glowing spheres hung over the floor */

    const spheres = [[-16, 25, -13, 3.4, '#ff5a8a'], [2, 28, 12, 4.4, '#ffc23f'], [18, 24, -9, 2.6, '#4fd6ff'], [34, 27, 8, 3.2, '#9d6bff'], [-4, 22, 0, 1.8, '#5dff9b']].map(([x, y, z, radius, hex]) => {
        const material = new THREE.MeshBasicMaterial({ color: hex });
        const orb = new THREE.Mesh(new THREE.SphereGeometry(radius, 28, 18), material);
        orb.position.set(x, y, z);
        stage.add(orb);

        return { orb, material, base: new THREE.Color(hex), y };
    });

    /* Stage */

    const name = addDeck(kit, DECK);

    addBackdrop(kit, -58, 80, 29);
    addScreen(kit, 36, 17, -56.5, 12, 0, Math.PI / 2, { boost: 1.15 });
    [-1, 1].forEach((side) => addScreen(kit, 11, 15, -54, 11.5, side * 25, Math.PI / 2 - side * 0.28));

    addBand(parts, -51, DECK.height, 7);
    addTrussLines(stage, materials.steel, [-42, -52], 22, 24, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -40, 22, side * 27, side * 0.2));

    const fixtures = [];
    for (let i = 0; i < 9; i++) {
        const z = -20 + i * 5;
        fixtures.push({ x: -42, y: 23.7, z, tilt: 0.52 }, { x: -52, y: 23.7, z, tilt: -0.28 });
    }

    return {
        lights: movingLights(fixtures, { length: 70, radius: 8 }),
        bands: [band, name],
        mood: {
            house: [2, 0.24],
            overhead: [1.3, 0.1],
            beam: [0.09, 0.26],
            terrace: '#646a76',
            houseSeat: '#1c2c4c',
            skyLight: '#dfe9ff',
            groundLight: '#20242e',
            spill: [-30, 18, 0],
            spillPower: 3000,
            spillReach: 150,
            stageGlow: [-110, 40, 0],
            roofFade: [66, 108],
            haze: { x: -26, z: 0, width: 44, depth: 46 },
            background: '#060812',
            fog: [320, 640],
            confetti: { x: -8, z: 0, width: 88, depth: 62, top: 28 },
            wristbands: true,
        },
        onFrame(time, colour, show, { bass, kick, phones }) {
            glass.emissiveIntensity = lerp(1, 0.28, show) * (1 - phones * 0.8);
            cove.color.copy(colour).multiplyScalar(1.5);

            spheres.forEach(({ orb, material, base, y }, index) => {
                orb.position.y = y + Math.sin(time * 0.4 + index * 1.7) * 1.2;
                orb.scale.setScalar(1 + bass * show * 0.08 + kick * show * 0.05);
                material.color.copy(base).multiplyScalar(lerp(0.9, 1.1 + bass, show) * (1 - phones * 0.6));
            });
        },
    };
}

/* Wembley Stadium: a revolving round stage on the pitch, under the great arch */

function arch(kit) {
    const { data, materials, parts, roof, stage, uniforms, bowl: seatGroup, standard, movingLights } = kit;
    const layout = data.layout;
    const STAGE = { radius: 11.4, height: 1.8 };
    const PYLON = { radius: 10.6, top: 27, screenAt: 21 };
    const OUTER = { x: 96, z: 86, top: 30 };
    const ARCH = { span: 118, top: 92, z: -84 };

    materials.carpet.color.set('#1b2029');
    materials.walkway.color.set('#23262e');
    materials.floor.color.set('#ffffff');
    materials.floor.map = pitchTexture();
    materials.floor.roughness = 0.9;

    addLand(seatGroup, standard({ color: '#090b10', roughness: 1 }));
    addNightSky(stage, { seed: 66 });

    const { cove, band } = seatingBowl(kit, { floor: [60, 50], strip: [1.7, 0.6, 1.2] });

    addOuterWall(seatGroup, layout, standard({ color: '#2a2d34', roughness: 0.65, metalness: 0.3 }), OUTER.x, OUTER.z, OUTER.top);

    /* A ring of roof, open over the pitch */

    const roofMaterial = standard({
        color: '#2c3038',
        roughness: 0.5,
        metalness: 0.45,
        side: THREE.DoubleSide,
    });
    const radii = (scale) => [OUTER.x * scale, OUTER.z * scale];

    roof.add(new THREE.Mesh(ring({
        profile: [[1.03, 30], [0.86, 37], [0.7, 39]],
        radii,
        power: layout.corner_power,
        segments: 160,
    }), roofMaterial));

    const edgeMaterial = new THREE.MeshBasicMaterial({ color: new THREE.Color(1.5, 1.5, 1.6) });
    roof.add(new THREE.Mesh(ring({
        profile: [[0.702, 38.5], [0.698, 39.5]],
        radii,
        power: layout.corner_power,
        segments: 160,
    }), edgeMaterial));

    /* The arch: a lattice of two chords and cross braces */

    const archPoint = (progress, offset) => {
        const angle = Math.PI * progress;
        const height = Math.sin(angle) * ARCH.top;

        return new THREE.Vector3(-Math.cos(angle) * ARCH.span, height + offset, ARCH.z + offset * 0.6);
    };

    const chords = [];
    const braces = [];
    const STEPS = 72;

    for (let step = 0; step < STEPS; step++) {
        const from = step / STEPS;
        const to = (step + 1) / STEPS;

        [-2.2, 2.2].forEach((offset) => chords.push(strut(archPoint(from, offset), archPoint(to, offset), 1)));
        braces.push(strut(archPoint(from, step % 2 ? -2.2 : 2.2), archPoint(to, step % 2 ? 2.2 : -2.2), 1));
    }

    const archMaterial = new THREE.MeshBasicMaterial({ color: '#ffffff' });
    stage.add(instanced(new THREE.BoxGeometry(0.9, 1, 0.9), archMaterial, chords));
    stage.add(instanced(new THREE.BoxGeometry(0.34, 1, 0.34), materials.steel, braces));

    const cables = [];
    for (let i = 8; i <= 28; i += 2) {
        const point = archPoint(i / 36, 0);
        const [x, z] = superellipsePoint(-Math.PI / 2 + (point.x / ARCH.span) * 0.9, OUTER.x * 0.84, OUTER.z * 0.84, layout.corner_power);
        cables.push(strut(point, new THREE.Vector3(x, 37, z), 1));
    }
    roof.add(instanced(new THREE.CylinderGeometry(0.06, 0.06, 1, 5), materials.steel, cables));

    /* The stage: a turning disc inside four towers */

    parts.add('mask', new THREE.CylinderGeometry(STAGE.radius, STAGE.radius, STAGE.height, 64).translate(0, STAGE.height / 2, 0));
    parts.add('riserEdge', new THREE.TorusGeometry(STAGE.radius, 0.06, 8, 96).rotateX(Math.PI / 2).translate(0, STAGE.height, 0));

    const turntable = new THREE.Group();
    const floorScreen = new THREE.Mesh(new THREE.CircleGeometry(STAGE.radius - 1.4, 64).rotateX(-Math.PI / 2), videoMaterial(uniforms, { grid: [340, 340], crop: 1, boost: 0.8 }));
    floorScreen.position.y = STAGE.height + 0.02;
    turntable.add(floorScreen);

    const gear = new Parts(materials);
    [[6, 0], [-6, 0], [0, 6], [0, -6]].forEach(([x, z]) => micStand(gear, x, STAGE.height, z));
    gear.box('riser', 3.4, 0.5, 3.4, 0, STAGE.height + 0.25, 0);
    gear.box('riserEdge', 3.4, 0.05, 0.05, 0, STAGE.height + 0.5, 1.7);
    gear.drum('dark', 0.26, 1, 0, STAGE.height + 1, 0);
    gear.build(turntable);
    stage.add(turntable);

    const fixtures = [];

    [0, 1, 2, 3].forEach((side) => {
        // In the aisles between the pitch blocks, so no seat stares at a tower.
        const angle = Math.PI / 8 + (side / 4) * TAU;
        const x = Math.cos(angle) * PYLON.radius;
        const z = Math.sin(angle) * PYLON.radius;

        parts.box('steel', 0.9, PYLON.top, 0.9, x, PYLON.top / 2, z);
        addScreen(kit, 10, 6, Math.cos(angle) * (PYLON.radius + 0.7), PYLON.screenAt, Math.sin(angle) * (PYLON.radius + 0.7), Math.PI / 2 - angle);
        addLineArray(stage, materials.speaker, Math.cos(angle) * (PYLON.radius + 0.6), 17, Math.sin(angle) * (PYLON.radius + 0.6), -angle);
    });

    parts.add('steel', new THREE.TorusGeometry(PYLON.radius, 0.22, 8, 96).rotateX(Math.PI / 2).translate(0, PYLON.top, 0));

    for (let i = 0; i < 20; i++) {
        const angle = (i / 20) * TAU;
        fixtures.push({
            x: Math.cos(angle) * PYLON.radius,
            y: PYLON.top - 0.3,
            z: Math.sin(angle) * PYLON.radius,
            yaw: Math.PI - angle,
            tilt: -0.5,
            swing: 0.3,
            sweep: 0.3,
        });
    }

    return {
        lights: movingLights(fixtures, { length: 70, radius: 7 }),
        bands: [band],
        mood: {
            house: [2, 0.22],
            overhead: [1.4, 0.1],
            beam: [0.09, 0.26],
            terrace: '#62666f',
            houseSeat: '#1c2640',
            skyLight: '#dfe6ff',
            groundLight: '#20242e',
            spill: [0, 22, 0],
            spillPower: 3600,
            spillReach: 150,
            stageGlow: [0, 90, 0],
            roofFade: [62, 104],
            haze: { x: 0, z: 0, width: 44, depth: 44 },
            background: '#05060f',
            fog: [340, 700],
            confetti: { x: 0, z: 0, width: 76, depth: 66, top: 26 },
        },
        onFrame(time, colour, show, { bass, kick, phones }) {
            turntable.rotation.y = time * 0.14;
            cove.color.copy(colour).multiplyScalar(1.5);
            edgeMaterial.color.copy(colour).multiplyScalar(lerp(0.6, 1 + bass, smoothstep(show, 0, 1)));

            // White before the show, then the colour of the lights on the night.
            const level = lerp(1.5, 1.1 + bass * 1.2 + kick * 0.6, show) * (1 - phones * 0.7);
            archMaterial.color.setRGB(1, 1, 1).lerp(colour, show).multiplyScalar(level);
        },
    };
}

export const stadiums = { monolith, neon, cube, openStadium, canopy, arch };
