// The other buildings around the seats: an open-air amphitheatre, an oval concert
// hall, a domed stadium with the stage in the middle, and a round arena with a
// runway. Each one receives the kit from buildArena, builds into its groups and
// returns its moving lights, its mood and anything that animates.

import * as THREE from 'three';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';
import {
    TAU, UP, addAisles, addDrumKit, canvasTexture, instanced, ledBand, placed, ring,
    softDotTexture, strut, superellipsePoint, terraceProfile, trussModule, videoMaterial,
} from './arena-scene.js';

const { degToRad: rad, lerp, smoothstep } = THREE.MathUtils;

const last = (list) => list[list.length - 1];

/** A random stream that gives the same trees and details on every visit. */
export function seeded(seed) {
    let value = seed;

    return () => {
        value = (value * 1664525 + 1013904223) % 4294967296;
        return value / 4294967296;
    };
}

/* Shared pieces */

function centreOf(layout) {
    return layout.centre ? [layout.centre.x, layout.centre.z] : [0, 0];
}

export function sweepOf(layout, bowl) {
    return rad(bowl.sweep_degrees ?? layout.sweep_degrees);
}

/** A strip following one bowl. Profile points are [metres behind the first row, height]. */
function bowlRing(layout, bowl, profile, { margin = 3, ...options } = {}) {
    const sweep = sweepOf(layout, bowl);
    const closed = sweep >= Math.PI;

    return ring({
        profile,
        radii: (offset) => [bowl.radius_x + offset, bowl.radius_z + offset],
        power: layout.corner_power,
        centre: centreOf(layout),
        from: closed ? -Math.PI : -sweep - rad(margin),
        to: closed ? Math.PI : sweep + rad(margin),
        segments: 240,
        ...options,
    });
}

export function addTerraces(group, layout, material, { repeatU = 90, margin } = {}) {
    layout.bowls.forEach((bowl) => {
        group.add(new THREE.Mesh(bowlRing(layout, bowl, terraceProfile(bowl), { segments: 320, repeatU, metresV: 4, margin }), material));
    });
}

/** Closes the open ends of a tier, down to `floor`. */
export function addEndCaps(group, layout, bowl, material, floor, margin = 3) {
    const [centreX, centreZ] = centreOf(layout);
    const limit = sweepOf(layout, bowl) + rad(margin);
    const profile = terraceProfile(bowl);

    [-limit, limit].forEach((angle) => {
        const positions = [];
        const indices = [];

        profile.forEach(([offset, y], index) => {
            const [x, z] = superellipsePoint(angle, bowl.radius_x + offset, bowl.radius_z + offset, layout.corner_power);
            positions.push(x + centreX, y, z + centreZ, x + centreX, floor, z + centreZ);
            if (index > 0) {
                const a = (index - 1) * 2;
                indices.push(a, a + 1, a + 2, a + 2, a + 1, a + 3);
            }
        });

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        geometry.setIndex(indices);
        geometry.computeVertexNormals();
        group.add(new THREE.Mesh(geometry, material));
    });
}

/** The walkway between a tier and the one behind it. */
export function connect(group, layout, from, to, walkway, face) {
    const end = last(terraceProfile(from));
    const start = terraceProfile(to)[1];
    const edge = to.radius_x - from.radius_x + start[0];

    group.add(new THREE.Mesh(bowlRing(layout, from, [end, [edge, end[1]]], { repeatU: 90, metresV: 4 }), walkway));

    if (face && start[1] > end[1]) {
        group.add(new THREE.Mesh(bowlRing(layout, from, [[edge, end[1]], [edge, start[1]]], { repeatU: 96 }), face));
    }
}

export function frontRail(group, layout, bowl, glass, steel) {
    const offset = -bowl.row_depth / 2 - 0.06;
    const top = bowl.height + 0.98;

    group.add(new THREE.Mesh(bowlRing(layout, bowl, [[offset, bowl.height - 0.02], [offset, top]], { segments: 200 }), glass));
    group.add(new THREE.Mesh(bowlRing(layout, bowl, [[offset - 0.04, top], [offset - 0.04, top + 0.06], [offset + 0.04, top + 0.06], [offset + 0.04, top]], { segments: 200 }), steel));
}

export function ribbon(group, layout, bowl, text) {
    const texture = ledBand(text);
    texture.repeat.set(10, 1);

    const riser = terraceProfile(bowl)[1];
    const material = new THREE.MeshBasicMaterial({ map: texture, color: new THREE.Color(1.4, 1.4, 1.4) });
    group.add(new THREE.Mesh(bowlRing(layout, bowl, [[riser[0] - 0.02, riser[1] + 0.3], [riser[0] - 0.02, riser[1] + 1.15]], { segments: 200 }), material));

    return texture;
}

export function disc(radiusX, radiusZ, power, material, y) {
    const shape = new THREE.Shape();

    for (let i = 0; i <= 128; i++) {
        const [x, z] = superellipsePoint((i / 128) * TAU, radiusX, radiusZ, power);
        i === 0 ? shape.moveTo(x, z) : shape.lineTo(x, z);
    }

    const mesh = new THREE.Mesh(new THREE.ShapeGeometry(shape).rotateX(-Math.PI / 2), material);
    mesh.position.y = y;

    return mesh;
}

/** Flooring under each block on the floor, turned to match its seats. */
export function addCarpets(group, { blocks, data }, material) {
    const seats = data.seats;
    const matrices = [];

    blocks.forEach((indices) => {
        if (Number.isFinite(Number(seats.block[indices[0]]))) {
            return;
        }

        const yaw = seats.rotation[indices[0]];
        const sideX = Math.cos(yaw);
        const sideZ = -Math.sin(yaw);
        const backX = Math.sin(yaw);
        const backZ = Math.cos(yaw);
        const across = indices.map((i) => seats.x[i] * sideX + seats.z[i] * sideZ);
        const behind = indices.map((i) => seats.x[i] * backX + seats.z[i] * backZ);
        const minA = Math.min(...across);
        const maxA = Math.max(...across);
        const minB = Math.min(...behind);
        const maxB = Math.max(...behind);
        const midA = (minA + maxA) / 2;
        const midB = (minB + maxB) / 2;

        matrices.push(placed(midA * sideX + midB * backX, 0.015, midA * sideZ + midB * backZ, yaw, maxA - minA + 1, 1, maxB - minB + 1));
    });

    group.add(instanced(new THREE.BoxGeometry(1, 0.03, 1), material, matrices));
}

/** A hanging line of speakers curving down towards the crowd. */
export function addLineArray(group, material, x, top, z, yaw) {
    const cabinets = [];
    let y = top;
    let tilt = 0;

    for (let i = 0; i < 12; i++) {
        const lean = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 0, 1), -tilt);
        const turn = new THREE.Quaternion().setFromAxisAngle(UP, yaw);
        cabinets.push(new THREE.Matrix4().compose(new THREE.Vector3(x + i * 0.03, y, z), turn.multiply(lean), new THREE.Vector3(1, 1, 1)));
        y -= 0.5;
        tilt += 0.016 * (i + 1);
    }

    group.add(instanced(new THREE.BoxGeometry(0.8, 0.46, 1.3), material, cabinets));
}

/** Runs of truss along z, at fixed x and height, with legs at both ends. */
export function addTrussLines(group, material, lines, reach, top, floor) {
    const sections = [];

    lines.forEach((x) => {
        for (let z = -reach; z <= reach; z += 2) {
            sections.push(placed(x, top, z, Math.PI / 2));
        }
        [-reach, reach].forEach((z) => {
            for (let y = floor + 1; y < top; y += 2) {
                sections.push(new THREE.Matrix4().compose(
                    new THREE.Vector3(x, y, z),
                    new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 0, 1), Math.PI / 2),
                    new THREE.Vector3(1, 1, 1)
                ));
            }
        });
    });

    group.add(instanced(trussModule(), material, sections));
}

export function micStand(parts, x, floor, z) {
    parts.drum('steel', 0.015, 1.4, x, floor + 0.7, z);
    parts.drum('steel', 0.13, 0.02, x, floor + 0.01, z);
    parts.drum('dark', 0.03, 0.14, x + 0.05, floor + 1.45, z);
}

export function wedge(parts, x, floor, z) {
    const box = new THREE.BoxGeometry(0.42, 0.34, 0.7);
    box.rotateZ(0.5);
    parts.add('speaker', box.translate(x, floor + 0.2, z));
}

/** A dark strip with bright bars. Its colour follows the lights in the show. */
export function barStripMaterial(colour) {
    return new THREE.MeshBasicMaterial({
        color: colour,
        map: canvasTexture(256, 32, (context, width, height) => {
            context.fillStyle = '#16161a';
            context.fillRect(0, 0, width, height);
            context.fillStyle = '#ffffff';
            for (let i = 0; i < 4; i++) {
                context.fillRect(i * (width / 4) + 8, 6, width / 4 - 16, height - 12);
            }
        }),
    });
}

/** Dents a sphere so a tree crown does not look like a ball. */
function lumpy(sphere) {
    const position = sphere.attributes.position;

    for (let i = 0; i < position.count; i++) {
        const [x, y, z] = [position.getX(i), position.getY(i), position.getZ(i)];
        const push = 1 + Math.sin(x * 11) * Math.sin(y * 13 + 1) * Math.sin(z * 9 + 2) * 0.28;
        position.setXYZ(i, x * push, y * push, z * push);
    }
    sphere.computeVertexNormals();

    return sphere;
}

/* Amphitheatre: a hillside under the sky, facing a shell of arches */

function amphitheatre({ data, rows, uniforms, materials, standard, parts, bowl: seatGroup, stage, scene, movingLights }) {
    const layout = data.layout;
    const CENTRE = [layout.centre.x, layout.centre.z];
    const SHELL = { front: -30, back: -46, floor: 1.7, ribs: 9, outer: 16, inner: 8.5 };
    const DECK = { back: -47, mouth: -30.5, apron: 13, height: 1.7, halfWidth: 17 };

    /* The slope follows the rows of seats, then climbs away into the hills. */

    const slope = [[0, -0.2]];
    layout.bowls.forEach((tier) => {
        const rowsBack = (tier.rows - 1) * tier.row_depth;
        slope.push([tier.radius_x - 1.1, tier.height - 1.6]);
        slope.push([tier.radius_x + rowsBack + 0.6, tier.height + (tier.rows - 1) * tier.rise - 1.1]);
    });
    const crest = last(slope);
    slope.push([crest[0] + 60, crest[1] + 22], [crest[0] + 400, crest[1] + 60]);

    const fanHeight = (distance) => {
        for (let i = 1; i < slope.length; i++) {
            if (distance <= slope[i][0]) {
                const [ra, ya] = slope[i - 1];
                const [rb, yb] = slope[i];
                return lerp(ya, yb, (distance - ra) / (rb - ra));
            }
        }
        return last(slope)[1];
    };

    const noise = (x, z) => Math.sin(x * 0.021 + 1.3) * Math.sin(z * 0.017 + 0.7)
        + Math.sin(x * 0.053) * Math.cos(z * 0.047) * 0.5
        + Math.sin((x + z) * 0.11) * 0.2;

    const groundHeight = (x, z) => {
        const dx = x - CENTRE[0];
        const dz = z - CENTRE[1];
        const distance = Math.hypot(dx, dz);
        const angle = Math.abs(Math.atan2(dz, dx));
        const roughness = noise(x, z);
        const fan = fanHeight(distance) + roughness * smoothstep(distance, 74, 110) * 7;
        const hills = smoothstep(distance, 30, 130) * 20 + smoothstep(distance, 130, 330) * 34 + roughness * smoothstep(distance, 30, 90) * 6;

        return lerp(fan, hills, smoothstep(angle, rad(66), rad(84)));
    };

    const ground = new THREE.PlaneGeometry(680, 680, 170, 170).rotateX(-Math.PI / 2).translate(-10, 0, 0);
    const position = ground.attributes.position;
    const shades = new Float32Array(position.count * 3);
    const dry = new THREE.Color('#3b3a26');
    const scrub = new THREE.Color('#1f2c1c');
    const shade = new THREE.Color();

    for (let i = 0; i < position.count; i++) {
        const x = position.getX(i);
        const z = position.getZ(i);
        position.setY(i, groundHeight(x, z));
        shade.lerpColors(scrub, dry, noise(x * 3.1, z * 2.7) * 0.5 + 0.5);
        shade.toArray(shades, i * 3);
    }
    ground.setAttribute('color', new THREE.BufferAttribute(shades, 3));
    ground.computeVertexNormals();
    seatGroup.add(new THREE.Mesh(ground, standard({ vertexColors: true, roughness: 1 })));

    /* Tiers */

    const hedge = standard({ color: '#1d3322', roughness: 1, side: THREE.DoubleSide });
    materials.walkway.color.set('#4a4640');

    addTerraces(seatGroup, layout, materials.terrace);
    layout.bowls.forEach((tier, index) => {
        addEndCaps(seatGroup, layout, tier, hedge, tier.height - 2);
        if (layout.bowls[index + 1]) {
            connect(seatGroup, layout, tier, layout.bowls[index + 1], materials.walkway, hedge);
        }

        const offset = -tier.row_depth / 2 - 0.06;
        seatGroup.add(new THREE.Mesh(bowlRing(layout, tier, [[offset, tier.height - 0.3], [offset, tier.height + 0.5]], { segments: 200 }), hedge));
    });
    addAisles(seatGroup, layout, rows, data.seats, materials);

    /* Lanterns along the cross aisles and the top of the hill */

    const posts = [];
    const lamps = [];
    const sweep = sweepOf(layout, layout.bowls[0]) + rad(2);

    const aisles = layout.bowls.slice(1).map((tier, index) => [tier.radius_x - 1.2, last(terraceProfile(layout.bowls[index]))[1]]);

    [...aisles, [crest[0] + 2, null]].forEach(([distance, level]) => {
        const count = Math.round(distance / 4);
        for (let i = 0; i <= count; i++) {
            const angle = -sweep + (2 * sweep * i) / count;
            const x = CENTRE[0] + Math.cos(angle) * distance;
            const z = CENTRE[1] + Math.sin(angle) * distance;

            // Low bollards in front of seats, so nothing stands in anyone's view.
            const floor = level ?? groundHeight(x, z);
            const height = level === null ? 2.6 : 0.6;
            posts.push(placed(x, floor + height / 2, z, 0, 1, height / 2.6, 1));
            lamps.push(placed(x, floor + height + 0.08, z));
        }
    });
    seatGroup.add(instanced(new THREE.CylinderGeometry(0.05, 0.07, 2.6, 6), materials.darkMetal, posts));
    seatGroup.add(instanced(new THREE.SphereGeometry(0.17, 10, 8), new THREE.MeshBasicMaterial({ color: new THREE.Color(3, 2, 1.1) }), lamps));

    /* Trees on everything that is not seats or stage */

    const random = seeded(19);
    const pine = mergeGeometries([
        new THREE.ConeGeometry(0.5, 0.5, 9).translate(0, 0.27, 0),
        new THREE.ConeGeometry(0.42, 0.46, 9).translate(0, 0.46, 0),
        new THREE.ConeGeometry(0.32, 0.42, 9).translate(0, 0.66, 0),
        new THREE.ConeGeometry(0.2, 0.36, 9).translate(0, 0.84, 0),
    ]);
    const oak = lumpy(new THREE.IcosahedronGeometry(0.5, 2)).translate(0, 0.46, 0);
    const forests = [pine, oak].map(() => []);

    for (let i = 0; i < 3200 && forests[0].length + forests[1].length < 1700; i++) {
        const x = -280 + random() * 560;
        const z = -300 + random() * 600;
        const dx = x - CENTRE[0];
        const dz = z - CENTRE[1];
        const distance = Math.hypot(dx, dz);
        const angle = Math.abs(Math.atan2(dz, dx));

        if ((angle < rad(69) && distance < crest[0] + 5) || distance < 33) {
            continue;
        }

        const tall = 3.5 + random() * 5;
        const wide = tall * (0.5 + random() * 0.3);
        forests[random() < 0.55 ? 0 : 1].push(placed(x, groundHeight(x, z) - 0.3, z, random() * TAU, wide, tall, wide));
    }

    const leaves = standard({ color: '#ffffff', roughness: 1, envMapIntensity: 0.1 });
    const leafColour = new THREE.Color();
    forests.forEach((matrices, kind) => {
        const trees = instanced([pine, oak][kind], leaves, matrices);
        matrices.forEach((_, index) => {
            trees.setColorAt(index, leafColour.setHSL(0.26 + random() * 0.08, 0.34, 0.035 + random() * 0.045));
        });
        seatGroup.add(trees);
    });

    /* Sky, stars, moon */

    const skyUniforms = {
        uNight: { value: 0 },
        uTopDusk: { value: new THREE.Color('#222a63') },
        uMidDusk: { value: new THREE.Color('#70508f') },
        uLowDusk: { value: new THREE.Color('#e88a5d') },
        uTopNight: { value: new THREE.Color('#03040c') },
        uMidNight: { value: new THREE.Color('#0a0e24') },
        uLowNight: { value: new THREE.Color('#1c2342') },
        uRidge: { value: new THREE.Color('#0b0f1a') },
    };
    const sky = new THREE.Mesh(new THREE.SphereGeometry(560, 40, 24), new THREE.ShaderMaterial({
        uniforms: skyUniforms,
        side: THREE.BackSide,
        depthWrite: false,
        fog: false,
        vertexShader: 'varying vec3 vDirection; void main() { vDirection = position; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }',
        fragmentShader: `
            varying vec3 vDirection;
            uniform float uNight;
            uniform vec3 uTopDusk, uMidDusk, uLowDusk, uTopNight, uMidNight, uLowNight, uRidge;
            void main() {
                vec3 direction = normalize(vDirection);
                float height = max(direction.y, 0.0);
                vec3 dusk = mix(uLowDusk, uMidDusk, smoothstep(0.0, 0.22, height));
                dusk = mix(dusk, uTopDusk, smoothstep(0.16, 0.7, height));
                vec3 night = mix(uLowNight, uMidNight, smoothstep(0.0, 0.2, height));
                night = mix(night, uTopNight, smoothstep(0.12, 0.6, height));
                vec3 colour = mix(dusk, night, uNight);
                float around = atan(direction.z, direction.x);
                float ridge = 0.075 + 0.028 * sin(around * 5.0 + 1.0) + 0.014 * sin(around * 13.0) + 0.006 * sin(around * 37.0);
                colour = mix(uRidge, colour, smoothstep(ridge - 0.002, ridge + 0.002, direction.y));
                gl_FragColor = vec4(colour, 1.0);
            }`,
    }));
    sky.frustumCulled = false;
    stage.add(sky);

    const starCount = 1800;
    const starPositions = new Float32Array(starCount * 3);
    for (let i = 0; i < starCount; i++) {
        const around = random() * TAU;
        const up = Math.pow(random(), 0.7) * 0.97 + 0.03;
        const flat = Math.sqrt(1 - up * up);
        starPositions.set([Math.cos(around) * flat * 540, up * 540, Math.sin(around) * flat * 540], i * 3);
    }
    const starGeometry = new THREE.BufferGeometry();
    starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
    const stars = new THREE.Points(starGeometry, new THREE.PointsMaterial({
        map: softDotTexture(),
        size: 2.6,
        sizeAttenuation: false,
        transparent: true,
        opacity: 0.3,
        depthWrite: false,
        fog: false,
        blending: THREE.AdditiveBlending,
        color: new THREE.Color(1.6, 1.6, 1.8),
    }));
    stage.add(stars);

    const moon = new THREE.Mesh(new THREE.CircleGeometry(15, 40), new THREE.MeshBasicMaterial({ color: new THREE.Color(2.4, 2.3, 2.1), fog: false }));
    moon.position.set(-300, 330, 240);
    moon.lookAt(0, 0, 0);
    stage.add(moon);

    /* Stage deck, with a round apron reaching into the audience */

    const deckShape = new THREE.Shape();
    deckShape.moveTo(DECK.back, -15);
    deckShape.lineTo(DECK.mouth, -DECK.halfWidth);
    for (let i = 0; i <= 40; i++) {
        const angle = rad(-65 + i * 3.25);
        deckShape.lineTo(CENTRE[0] + Math.cos(angle) * DECK.apron, Math.sin(angle) * DECK.apron);
    }
    deckShape.lineTo(DECK.mouth, DECK.halfWidth);
    deckShape.lineTo(DECK.back, 15);

    const deckGeometry = new THREE.ExtrudeGeometry(deckShape, { depth: DECK.height, bevelEnabled: false, curveSegments: 4 });
    deckGeometry.rotateX(Math.PI / 2).translate(0, DECK.height, 0);
    stage.add(new THREE.Mesh(deckGeometry, [materials.deck, materials.mask]));

    /* The shell: concentric arches, each a little smaller than the last */

    const ribs = [];
    const panels = [];
    const radiusAt = (index) => lerp(SHELL.outer, SHELL.inner, Math.pow(index / (SHELL.ribs - 1), 0.85));
    const xAt = (index) => lerp(SHELL.front, SHELL.back, index / (SHELL.ribs - 1));

    for (let rib = 0; rib < SHELL.ribs; rib++) {
        const radius = radiusAt(rib);
        ribs.push(new THREE.TorusGeometry(radius, 0.13, 8, 72, Math.PI).rotateY(Math.PI / 2).translate(xAt(rib), SHELL.floor, 0));

        if (rib < SHELL.ribs - 1) {
            const inner = radiusAt(rib + 1);
            const positions = [];
            const indices = [];
            const steps = 48;

            for (let step = 0; step <= steps; step++) {
                const angle = (step / steps) * Math.PI;
                const [sin, cos] = [Math.sin(angle), Math.cos(angle)];
                positions.push(xAt(rib), SHELL.floor + radius * sin, radius * cos);
                positions.push(xAt(rib + 1), SHELL.floor + (inner + 0.5) * sin, (inner + 0.5) * cos);
                if (step < steps) {
                    const a = step * 2;
                    indices.push(a, a + 2, a + 1, a + 2, a + 3, a + 1);
                }
            }

            const panel = new THREE.BufferGeometry();
            panel.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            panel.setIndex(indices);
            panels.push(panel);
        }
    }

    const shellPanels = mergeGeometries(panels);
    shellPanels.computeVertexNormals();
    stage.add(new THREE.Mesh(shellPanels, standard({ color: '#8d877c', roughness: 0.7, side: THREE.DoubleSide })));

    const ribMaterial = new THREE.MeshBasicMaterial({ color: new THREE.Color(1.7, 1.2, 2.2) });
    stage.add(new THREE.Mesh(mergeGeometries(ribs), ribMaterial));

    [-1, 1].forEach((side) => {
        parts.drum('dark', 0.34, 3.4, SHELL.front, 1.7, side * SHELL.outer);
    });

    /* Video: a half disc at the back of the shell and two towers at the sides */

    const backdrop = new THREE.CircleGeometry(SHELL.inner + 0.3, 56, 0, Math.PI).rotateY(Math.PI / 2);
    const uv = backdrop.attributes.uv;
    for (let i = 0; i < uv.count; i++) {
        uv.setY(i, (uv.getY(i) - 0.5) * 2);
    }
    const backdropMesh = new THREE.Mesh(backdrop, videoMaterial(uniforms, { grid: [420, 210], crop: 1, boost: 1.2 }));
    backdropMesh.position.set(SHELL.back - 0.1, SHELL.floor, 0);
    stage.add(backdropMesh);

    [-1, 1].forEach((side) => {
        const screen = new THREE.Mesh(new THREE.PlaneGeometry(11, 6.2), videoMaterial(uniforms, { grid: [300, 170], crop: 1 }));
        screen.position.set(-27, 9.4, side * 27);
        screen.rotation.y = Math.PI / 2 + side * 0.72;
        stage.add(screen);

        const back = new THREE.BoxGeometry(11.5, 6.7, 0.3).rotateY(screen.rotation.y).translate(-27.12, 9.4, side * 27.1);
        parts.add('darkMetal', back);
        parts.drum('steel', 0.14, 7, -27.1 - 0.5, 3.1, side * 27.1);
        parts.drum('steel', 0.14, 7, -27.1 + 0.5, 3.1, side * 27.1);

        addLineArray(stage, materials.speaker, -31.5, 13.5, side * 12.6, side * 0.3);
    });

    /* On stage */

    [[-42.6, 3, 1, 15], [-40.8, 2.4, 0.55, 11]].forEach(([x, width, height, depth]) => {
        parts.box('riser', width, height, depth, x, DECK.height + height / 2, 0);
        parts.box('riserEdge', 0.05, 0.05, depth, x + width / 2, DECK.height + height, 0);
    });
    addDrumKit(parts, -41, DECK.height + 0.55, 0);
    [-6, 0, 6].forEach((z) => micStand(parts, -29, DECK.height, z));
    [-8, -3, 3, 8].forEach((z) => wedge(parts, -26.2 - Math.abs(z) * 0.4, DECK.height, z));
    [-1, 1].forEach((side) => {
        parts.box('speaker', 0.5, 1.5, 1, -38, DECK.height + 0.75, side * 10);
        parts.box('dark', 0.34, 0.06, 1.3, -39, DECK.height + 0.95, side * 6.4);
    });

    /* Lights hung along two arches, and searchlights on the hill */

    const fixtures = [];
    [[1, 0.42], [4, -0.2]].forEach(([rib, tilt]) => {
        for (let i = 1; i < 10; i++) {
            const angle = (i / 10) * Math.PI;
            const radius = radiusAt(rib) - 0.4;
            fixtures.push({ x: xAt(rib) + 0.3, y: SHELL.floor + radius * Math.sin(angle), z: radius * Math.cos(angle), tilt, swing: 0.3, sweep: 0.26 });
        }
    });

    const searchlights = [-1, 1].flatMap((side) => [14, 28].map((z) => ({
        x: -66, y: groundHeight(-66, side * z) + 1, z: side * z, tilt: 0.1, swing: 0.34, sweep: 0.4, upright: true,
    })));

    const lights = [...movingLights(fixtures, { length: 46, radius: 5 }), ...movingLights(searchlights, { length: 240, radius: 13 })];
    const glowColour = new THREE.Color();

    return {
        lights,
        mood: {
            house: [1.75, 0.36],
            overhead: [1.15, 0.14],
            beam: [0.1, 0.26],
            terrace: '#77706a',
            houseSeat: '#28344c',
            skyLight: '#c8d0ff',
            groundLight: '#2c2720',
            spill: [-30, 7, 0],
            spillPower: 420,
            stageGlow: [-70, 18, 0],
            haze: { x: -31, z: 0, width: 14, depth: 26 },
            background: '#232848',
            fog: [230, 520],
        },
        onShow(value) {
            skyUniforms.uNight.value = value * 0.75 + 0.25 * smoothstep(value, 0, 0.2);
            stars.material.opacity = lerp(0.3, 1, value);
            scene.fog.color.set('#2b2c50').lerp(glowColour.set('#0c1022'), value);
        },
        onFrame(time, colour) {
            ribMaterial.color.copy(colour).multiplyScalar(2.1);
        },
    };
}

/* Concert hall: an oval room of stacked boxes under a fluted dome */

function hall({ data, blocks, rows, uniforms, materials, standard, parts, bowl: seatGroup, stage, roof, movingLights }) {
    const layout = data.layout;
    const [stalls, ...upperTiers] = layout.bowls;
    const boxTiers = upperTiers.filter((tier) => tier.rows === 2);
    const balcony = last(upperTiers);
    const WALL = { x: 41, z: 36, top: 26.5 };
    const DECK = { height: 1, back: -34, halfWidth: 16 };
    const MARGIN = 0.8;
    const power = layout.corner_power;

    const parquet = canvasTexture(512, 512, (context, width) => {
        const cell = width / 8;
        for (let row = 0; row < 8; row++) {
            for (let column = 0; column < 8; column++) {
                const warmth = 70 + ((row * 7 + column * 13) % 11) * 5;
                context.fillStyle = `rgb(${warmth + 62}, ${warmth + 12}, ${warmth - 30})`;
                context.fillRect(column * cell, row * cell, cell - 1, cell - 1);
            }
        }
    });
    parquet.repeat.set(0.09, 0.09);

    const plaster = standard({ color: '#d6c7ab', roughness: 0.7, side: THREE.DoubleSide });
    const crimson = standard({ color: '#6f1a24', roughness: 0.92, side: THREE.DoubleSide });
    const gold = standard({ color: '#b98d3e', roughness: 0.35, metalness: 0.85, side: THREE.DoubleSide });
    const wood = standard({ color: '#5a3620', roughness: 0.5 });
    const floorMaterial = standard({ map: parquet, roughness: 0.42, envMapIntensity: 0.6 });

    Object.assign(materials, { plaster, crimson, gold, wood });
    materials.walkway.color.set('#4a1a20');
    materials.carpet.color.set('#581820');

    /* Arena floor and raked stalls */

    seatGroup.add(disc(WALL.x, WALL.z, power, materials.dark, -0.06));
    seatGroup.add(disc(stalls.radius_x, stalls.radius_z, power, floorMaterial, -0.02));
    addCarpets(seatGroup, { blocks, data }, materials.carpet);

    seatGroup.add(new THREE.Mesh(bowlRing(layout, stalls, terraceProfile(stalls), { segments: 320, repeatU: 90, metresV: 4 }), materials.terrace));
    addEndCaps(seatGroup, layout, stalls, plaster, 0);
    addAisles(seatGroup, layout, rows, data.seats, materials, { skip: (tier) => tier.rows === 2 });

    const stallsTop = last(terraceProfile(stalls))[1];
    const firstBox = boxTiers[0];
    seatGroup.add(new THREE.Mesh(bowlRing(layout, firstBox, [[-1.05, stallsTop - 0.02], [-1.05, firstBox.height - 0.3]], { segments: 200 }), crimson));

    /* Three rows of boxes, each a slab with a parapet and a red back wall */

    const walls = [];
    const sconces = [];

    boxTiers.forEach((tier) => {
        const back = tier.rows * tier.row_depth + 0.4;
        const floor = tier.height - 0.3;
        const wallTop = tier.height + 3.05;
        const options = { margin: MARGIN, segments: 240 };
        const strip = (profile, material, extra = {}) => seatGroup.add(new THREE.Mesh(bowlRing(layout, tier, profile, { ...options, ...extra }), material));

        strip(terraceProfile(tier), materials.carpet, { repeatU: 40 });
        strip([[-1.05, floor - 0.25], [back, floor - 0.25]], plaster);
        strip([[-1.05, floor - 0.25], [-1.05, tier.height + 0.8]], plaster);
        strip([[-1.1, tier.height + 0.8], [-1.1, tier.height + 0.9], [-0.92, tier.height + 0.9], [-0.92, tier.height + 0.8]], gold, { segments: 200 });
        strip([[-1.08, tier.height + 0.14], [-1.08, tier.height + 0.2]], gold, { segments: 200 });
        strip([[back, floor], [back, wallTop]], crimson);

        const blockAngle = (2 * sweepOf(layout, tier)) / tier.blocks;

        for (let i = 0; i <= tier.blocks; i++) {
            const angle = -sweepOf(layout, tier) + i * blockAngle;
            const [x, z] = superellipsePoint(angle, tier.radius_x + back / 2 - 0.5, tier.radius_z + back / 2 - 0.5, power);
            const yaw = Math.atan2(x / (tier.radius_x * tier.radius_x), z / (tier.radius_z * tier.radius_z));

            walls.push(placed(x, tier.height + 0.9, z, yaw, 0.1, 2.5, back + 0.9));

            const [lampX, lampZ] = superellipsePoint(angle, tier.radius_x + back - 0.3, tier.radius_z + back - 0.3, power);
            sconces.push(placed(lampX, tier.height + 1.9, lampZ));
        }
    });

    seatGroup.add(instanced(new THREE.BoxGeometry(1, 1, 1), plaster, walls));
    seatGroup.add(instanced(new THREE.SphereGeometry(0.11, 8, 6), new THREE.MeshBasicMaterial({ color: new THREE.Color(3.2, 2.2, 1.2) }), sconces));

    /* The balcony and the colonnade behind it */

    seatGroup.add(new THREE.Mesh(bowlRing(layout, balcony, terraceProfile(balcony), { segments: 320, repeatU: 90, metresV: 4, margin: MARGIN }), materials.terrace));
    const balconyFront = -0.95;
    seatGroup.add(new THREE.Mesh(bowlRing(layout, balcony, [[balconyFront, balcony.height - 1.42], [balconyFront, balcony.height + 0.72]], { segments: 200, margin: MARGIN }), plaster));
    seatGroup.add(new THREE.Mesh(bowlRing(layout, balcony, [[balconyFront - 0.06, balcony.height + 0.72], [balconyFront - 0.06, balcony.height + 0.82], [balconyFront + 0.1, balcony.height + 0.82], [balconyFront + 0.1, balcony.height + 0.72]], { segments: 200, margin: MARGIN }), gold));

    const columns = [];
    const columnCount = 72;
    const balconyTop = last(terraceProfile(balcony))[1];

    for (let i = 0; i <= columnCount; i++) {
        const limit = sweepOf(layout, balcony);
        const angle = -limit + (2 * limit * i) / columnCount;
        const [x, z] = superellipsePoint(angle, WALL.x - 0.6, WALL.z - 0.6, power);
        columns.push(placed(x, (balconyTop + WALL.top) / 2, z));
    }
    seatGroup.add(instanced(new THREE.CylinderGeometry(0.22, 0.26, WALL.top - balconyTop, 12), plaster, columns));

    stage.add(new THREE.Mesh(ring({
        profile: [[1, 0], [1, WALL.top]],
        radii: (scale) => [WALL.x * scale, WALL.z * scale],
        power,
        segments: 160,
    }), standard({ color: '#6f1a24', roughness: 0.92 })));

    /* The fluted dome, hung with acoustic discs */

    const domeHeight = (scale) => WALL.top + 14 * (1 - Math.pow(scale, 2.4));
    const domeProfile = [];
    for (let i = 0; i <= 18; i++) {
        const scale = 1 - (i / 18) * 0.86;
        domeProfile.push([scale, domeHeight(scale)]);
    }

    const flutes = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#cdb78f';
        context.fillRect(0, 0, width, height);
        const flute = context.createLinearGradient(0, 0, width, 0);
        flute.addColorStop(0, lit ? '#3a2a18' : '#8f7a55');
        flute.addColorStop(0.5, lit ? '#b78e55' : '#e9dcc0');
        flute.addColorStop(1, lit ? '#3a2a18' : '#8f7a55');
        context.fillStyle = flute;
        context.fillRect(0, 0, width, height);
        context.fillStyle = lit ? '#000' : '#6e5a3a';
        for (let i = 1; i < 6; i++) {
            context.fillRect(0, (i / 6) * height, width, 3);
        }
    };

    const domeMaterial = standard({
        map: canvasTexture(64, 256, flutes(false)),
        emissiveMap: canvasTexture(64, 256, flutes(true)),
        emissive: '#ffffff',
        emissiveIntensity: 0.55,
        side: THREE.DoubleSide,
        roughness: 0.8,
    });
    roof.add(new THREE.Mesh(ring({
        profile: domeProfile,
        radii: (scale) => [WALL.x * scale, WALL.z * scale],
        power,
        segments: 160,
        repeatU: 64,
        smooth: true,
    }), domeMaterial));

    const oculus = new THREE.Mesh(
        new THREE.CircleGeometry(1, 48).rotateX(Math.PI / 2).scale(WALL.x * 0.14, 1, WALL.z * 0.14),
        new THREE.MeshBasicMaterial({ color: new THREE.Color(2.6, 2.1, 1.4), side: THREE.DoubleSide })
    );
    oculus.position.y = domeHeight(0.14);
    roof.add(oculus);

    const random = seeded(7);
    const mushrooms = [];
    const stems = [];
    [[0.26, 9], [0.44, 17], [0.6, 25], [0.74, 34]].forEach(([scale, count], ringIndex) => {
        for (let i = 0; i < count; i++) {
            const angle = ((i + ringIndex * 0.5) / count) * TAU;
            const x = Math.cos(angle) * WALL.x * scale;
            const z = Math.sin(angle) * WALL.z * scale;
            const y = domeHeight(scale) - 8.5 + (random() - 0.5) * 1.1;
            const size = 1.1 + random() * 0.95;
            mushrooms.push(placed(x, y, z, 0, size, 0.2, size));
            stems.push(strut(new THREE.Vector3(x, y, z), new THREE.Vector3(x, domeHeight(scale), z), 1));
        }
    });
    roof.add(instanced(new THREE.SphereGeometry(1, 20, 10), standard({ color: '#e6dcc8', emissive: '#a88a60', emissiveIntensity: 0.4, roughness: 0.5, side: THREE.DoubleSide }), mushrooms));
    roof.add(instanced(new THREE.CylinderGeometry(0.018, 0.018, 1, 5), materials.steel, stems));

    /* Stage: a sprung wooden platform with an oval front, choir risers and organ */

    const deckShape = new THREE.Shape();
    deckShape.moveTo(DECK.back, -DECK.halfWidth);
    deckShape.lineTo(-27, -DECK.halfWidth);
    deckShape.quadraticCurveTo(-16.2, -13, -15.8, 0);
    deckShape.quadraticCurveTo(-16.2, 13, -27, DECK.halfWidth);
    deckShape.lineTo(DECK.back, DECK.halfWidth);

    const deckGeometry = new THREE.ExtrudeGeometry(deckShape, { depth: DECK.height, bevelEnabled: false, curveSegments: 24 });
    deckGeometry.rotateX(Math.PI / 2).translate(0, DECK.height, 0);
    stage.add(new THREE.Mesh(deckGeometry, [floorMaterial, wood]));

    for (let step = 0; step < 7; step++) {
        const height = DECK.height + (step + 1) * 0.42;
        parts.box('wood', 1, height, 34 - step, -30 - step * 1, height / 2, 0);
        parts.box('crimson', 1.02, 0.04, 34 - step, -30 - step * 1, height, 0);
    }

    const ORGAN = { x: -39, base: 5 };
    parts.box('wood', 2, 9.5, 36, ORGAN.x, ORGAN.base + 4.75, 0);

    const pipes = [];
    for (let i = -34; i <= 34; i++) {
        const z = i * 0.52;
        const crown = Math.abs(Math.sin(i * 0.21)) * 3.4 + (1 - Math.abs(i) / 36) * 6.5;
        const height = 5 + crown;
        const front = i % 2 === 0;
        pipes.push(placed(ORGAN.x + (front ? 1.05 : 0.5), ORGAN.base + 9.5 + height / 2, z, 0, front ? 0.24 : 0.2, height, front ? 0.24 : 0.2));
    }
    stage.add(instanced(new THREE.CylinderGeometry(1, 1, 1, 10), standard({ color: '#c4b080', roughness: 0.32, metalness: 0.9 }), pipes));

    const screen = new THREE.Mesh(new THREE.PlaneGeometry(15, 8.4), videoMaterial(uniforms, { grid: [420, 236], crop: 1, boost: 1 }));
    screen.position.set(ORGAN.x + 1.1, ORGAN.base + 4.7, 0);
    screen.rotation.y = Math.PI / 2;
    stage.add(screen);

    addGrandPiano(stage, materials, -21.5, DECK.height, -4.5);
    addDrumKit(parts, -28, DECK.height + 0.42, 4.5);
    parts.box('riser', 3, 0.42, 3.4, -28, DECK.height + 0.21, 4.5);

    [0, -7, 7].forEach((z) => micStand(parts, -19.5, DECK.height, z));
    parts.drum('wood', 0.26, 1.15, -24.5, DECK.height + 0.68, 9);
    parts.drum('wood', 0.05, 0.85, -24.5, DECK.height + 1.6, 9);
    [-10, -6, 6, 10].forEach((z) => {
        parts.box('darkMetal', 0.06, 0.34, 0.5, -25.5, DECK.height + 1.05, z);
        parts.drum('steel', 0.012, 1, -25.5, DECK.height + 0.5, z);
    });

    /* A small lighting rig over the platform */

    addTrussLines(stage, materials.darkMetal, [-28, -20], 14, 15.5, DECK.height);
    addLineArray(stage, materials.speaker, -19, 15, 15, 0.22);
    addLineArray(stage, materials.speaker, -19, 15, -15, -0.22);

    const fixtures = [];
    for (let i = 0; i < 7; i++) {
        const z = -11 + i * 3.67;
        fixtures.push({ x: -20, y: 15.2, z, tilt: -0.3, swing: 0.2, sweep: 0.14 }, { x: -28, y: 15.2, z, tilt: -0.14, swing: 0.16, sweep: 0.12 });
    }

    return {
        lights: movingLights(fixtures, { length: 24, radius: 3 }),
        mood: {
            house: [2, 0.46],
            overhead: [0.8, 0.1],
            beam: [0.07, 0.17],
            terrace: '#7b2c36',
            houseSeat: '#7a1626',
            skyLight: '#ffe6c4',
            groundLight: '#3c1c14',
            spill: [-20, 9, 0],
            spillPower: 520,
            spillReach: 60,
            roofFade: [40, 58],
            haze: { x: -20, z: 0, width: 12, depth: 20 },
            tint: { colour: '#ffb877', amount: 0.62 },
            background: '#100a09',
            fog: [130, 280],
        },
        onShow(value) {
            domeMaterial.emissiveIntensity = lerp(0.55, 0.3, value);
        },
    };
}

function addGrandPiano(group, materials, x, floor, z) {
    const gloss = new THREE.MeshStandardMaterial({ color: '#050506', roughness: 0.16, metalness: 0.3 });
    const piano = new THREE.Group();
    const outline = new THREE.Shape();

    outline.moveTo(-0.75, 0);
    outline.lineTo(0.75, 0);
    outline.lineTo(0.75, 1.1);
    outline.bezierCurveTo(0.75, 1.8, 0.3, 1.7, 0.2, 2.2);
    outline.bezierCurveTo(0.1, 2.6, -0.75, 2.6, -0.75, 2);
    outline.lineTo(-0.75, 0);

    const body = new THREE.Mesh(new THREE.ExtrudeGeometry(outline, { depth: 0.32, bevelEnabled: false, curveSegments: 12 }), gloss);
    body.rotation.x = Math.PI / 2;
    body.position.y = 1;
    piano.add(body);

    const lid = new THREE.Mesh(new THREE.ExtrudeGeometry(outline, { depth: 0.03, bevelEnabled: false, curveSegments: 12 }), gloss);
    lid.rotation.x = Math.PI / 2;
    lid.position.x = -0.75;
    const hinge = new THREE.Group();
    hinge.position.set(0.75, 1.02, 0);
    hinge.rotation.z = -0.6;
    hinge.add(lid);
    piano.add(hinge);

    const keys = new THREE.Mesh(new THREE.BoxGeometry(1.42, 0.03, 0.22), new THREE.MeshStandardMaterial({ color: '#f1efe8', roughness: 0.4 }));
    keys.position.set(0, 0.74, -0.11);
    piano.add(keys);

    [[-0.6, 0.15], [0.6, 0.15], [-0.4, 2.1]].forEach(([legX, legZ]) => {
        const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.04, 0.68, 10), gloss);
        leg.position.set(legX, 0.34, legZ);
        piano.add(leg);
    });

    const stool = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.09, 0.34), materials.dark);
    stool.position.set(0, 0.48, -0.62);
    piano.add(stool);

    piano.position.set(x, floor, z);
    piano.rotation.y = Math.PI - 0.22;
    group.add(piano);
}

/* Dome: an air-supported roof with the stage in the middle of the field */

function dome({ data, blocks, rows, uniforms, materials, standard, parts, roofParts, bowl: seatGroup, stage, roof, movingLights }) {
    const layout = data.layout;
    const [lower, upper] = layout.bowls;
    const STAGE = { radius: 11, height: 1.7 };
    const WALL = { radius: 92.8, top: 30 };
    const ROOF_RISE = 32;
    const power = layout.corner_power;
    const random = seeded(31);

    const fieldTexture = canvasTexture(256, 256, (context, width) => {
        context.fillStyle = '#17231f';
        context.fillRect(0, 0, width, width);
        context.strokeStyle = '#26382f';
        context.lineWidth = 3;
        for (let i = 0; i <= 4; i++) {
            const at = (i / 4) * width;
            context.beginPath();
            context.moveTo(at, 0);
            context.lineTo(at, width);
            context.moveTo(0, at);
            context.lineTo(width, at);
            context.stroke();
        }
    });
    fieldTexture.repeat.set(0.06, 0.06);

    materials.carpet.color.set('#22303a');
    materials.walkway.color.set('#3a3d46');

    seatGroup.add(disc(60, 60, power, standard({ map: fieldTexture, roughness: 0.9, envMapIntensity: 0.2 }), -0.02));
    addCarpets(seatGroup, { blocks, data }, materials.carpet);

    addTerraces(seatGroup, layout, materials.terrace, { repeatU: 130 });
    addAisles(seatGroup, layout, rows, data.seats, materials);

    const concourse = barStripMaterial(new THREE.Color(2.4, 2.1, 1.6));
    connect(seatGroup, layout, lower, upper, materials.walkway, concourse);

    frontRail(seatGroup, layout, lower, materials.glass, materials.steel);
    frontRail(seatGroup, layout, upper, materials.glass, materials.steel);
    seatGroup.add(new THREE.Mesh(bowlRing(layout, lower, [[-1.1, 0], [-1.1, lower.height - 1.4]], { segments: 200 }), materials.dark));

    const band = ribbon(seatGroup, layout, upper, data.stageName);

    /* The roof: a pale quilted membrane held by a net of cables */

    const quilt = canvasTexture(256, 256, (context, width) => {
        context.fillStyle = '#c7ceda';
        context.fillRect(0, 0, width, width);

        const soft = context.createRadialGradient(width / 2, width / 2, 10, width / 2, width / 2, width * 0.62);
        soft.addColorStop(0, '#f2f4f8');
        soft.addColorStop(1, '#a9b2c2');
        context.fillStyle = soft;
        context.beginPath();
        context.moveTo(width / 2, 0);
        context.lineTo(width, width / 2);
        context.lineTo(width / 2, width);
        context.lineTo(0, width / 2);
        context.fill();

        context.strokeStyle = '#6f7a8c';
        context.lineWidth = 5;
        context.beginPath();
        context.moveTo(width / 2, 0);
        context.lineTo(width, width / 2);
        context.lineTo(width / 2, width);
        context.lineTo(0, width / 2);
        context.closePath();
        context.stroke();
    });

    const roofHeight = (scale) => WALL.top + ROOF_RISE * (1 - Math.pow(scale, 2.2));
    const membraneProfile = [];
    for (let i = 0; i <= 24; i++) {
        const scale = Math.max(1 - i / 24, 0.002);
        membraneProfile.push([scale, roofHeight(scale)]);
    }

    const membrane = standard({ map: quilt, emissive: '#cdd6e6', emissiveMap: quilt, emissiveIntensity: 0.42, roughness: 0.9, side: THREE.DoubleSide });
    roof.add(new THREE.Mesh(ring({
        profile: membraneProfile,
        radii: (scale) => [WALL.radius * scale, WALL.radius * scale],
        power,
        segments: 180,
        repeatU: 44,
        metresV: 13,
        smooth: true,
    }), membrane));

    roof.add(new THREE.Mesh(ring({
        profile: [[1, last(terraceProfile(upper))[1] - 0.4], [1, WALL.top]],
        radii: (scale) => [WALL.radius * scale, WALL.radius * scale],
        power,
        segments: 180,
        repeatU: 72,
    }), barStripMaterial(new THREE.Color(0.5, 0.55, 0.62))));

    /* Round stage with a video floor */

    const stageBody = new THREE.CylinderGeometry(STAGE.radius, STAGE.radius, STAGE.height, 72);
    parts.add('mask', stageBody.translate(0, STAGE.height / 2, 0));
    parts.add('riserEdge', new THREE.TorusGeometry(STAGE.radius, 0.05, 8, 96).rotateX(Math.PI / 2).translate(0, STAGE.height + 0.02, 0));

    const videoFloor = new THREE.Mesh(
        new THREE.CircleGeometry(STAGE.radius - 0.05, 72).rotateX(-Math.PI / 2),
        videoMaterial(uniforms, { grid: [560, 560], crop: 1, boost: 0.9 })
    );
    videoFloor.position.y = STAGE.height + 0.02;
    stage.add(videoFloor);

    const name = ledBand(data.stageName);
    name.repeat.set(3, 1);
    const nameBand = new THREE.Mesh(
        new THREE.CylinderGeometry(STAGE.radius + 0.03, STAGE.radius + 0.03, 0.7, 72, 1, true),
        new THREE.MeshBasicMaterial({ map: name, color: new THREE.Color(1.3, 1.3, 1.3) })
    );
    nameBand.position.y = STAGE.height - 0.6;
    stage.add(nameBand);

    parts.add('riser', new THREE.CylinderGeometry(2.8, 3, 0.5, 40).translate(0, STAGE.height + 0.25, 0));
    parts.box('darkMetal', 3, 1.05, 1, 0, STAGE.height + 1.02, -0.4);
    parts.box('screen', 3.04, 0.05, 1.04, 0, STAGE.height + 1.56, -0.4);

    // Stairs sit in the aisles between blocks, so no front row looks at one.
    [0, 1, 2, 3].forEach((quarter) => {
        const microphone = (quarter / 4) * TAU + rad(45);
        const stairs = (quarter / 4) * TAU + rad(22.5);
        micStand(parts, Math.cos(microphone) * 7, STAGE.height, Math.sin(microphone) * 7);

        for (let step = 0; step < 5; step++) {
            const height = (STAGE.height / 5) * (5 - step);
            const reach = STAGE.radius + 0.2 + step * 0.34;
            parts.add('darkMetal', new THREE.BoxGeometry(0.34, height, 1.6)
                .rotateY(-stairs)
                .translate(Math.cos(stairs) * reach, height / 2, Math.sin(stairs) * reach));
        }
    });

    const barriers = [];
    for (let i = 0; i < 56; i++) {
        const angle = (i / 56) * TAU;
        barriers.push(placed(Math.cos(angle) * 14, 0.6, Math.sin(angle) * 14, -angle, 0.06, 1.15, 1.5));
    }
    stage.add(instanced(new THREE.BoxGeometry(1, 1, 1), materials.steel, barriers));

    /* Flown rig: a video ring, kinetic pods and a truss ring of lights */

    const RIG = { ring: 24, top: 30 };
    const videoRing = new THREE.Mesh(
        new THREE.CylinderGeometry(16, 16, 5.4, 96, 1, true),
        videoMaterial(uniforms, { grid: [1100, 120], repeatU: 4, crop: 0.5 })
    );
    videoRing.position.y = 21;
    roof.add(videoRing);
    roofParts.add('darkMetal', new THREE.TorusGeometry(16, 0.22, 8, 96).rotateX(Math.PI / 2).translate(0, 23.8, 0));
    roofParts.add('darkMetal', new THREE.TorusGeometry(16, 0.22, 8, 96).rotateX(Math.PI / 2).translate(0, 18.2, 0));

    const truss = [];
    const sections = Math.round((TAU * RIG.ring) / 2);
    for (let i = 0; i < sections; i++) {
        const angle = (i / sections) * TAU;
        truss.push(placed(Math.cos(angle) * RIG.ring, RIG.top, Math.sin(angle) * RIG.ring, -angle - Math.PI / 2));
    }
    roof.add(instanced(trussModule(), materials.steel, truss));

    const cables = [];
    for (let i = 0; i < 16; i++) {
        const angle = (i / 16) * TAU;
        const [x, z] = [Math.cos(angle), Math.sin(angle)];
        cables.push(strut(new THREE.Vector3(x * RIG.ring, RIG.top, z * RIG.ring), new THREE.Vector3(x * RIG.ring, roofHeight(RIG.ring / WALL.radius), z * RIG.ring), 1));
        if (i % 2 === 0) {
            cables.push(strut(new THREE.Vector3(x * 16, 23.9, z * 16), new THREE.Vector3(x * 16, roofHeight(16 / WALL.radius), z * 16), 1));
        }
    }
    roof.add(instanced(new THREE.CylinderGeometry(0.05, 0.05, 1, 6), materials.steel, cables));

    const podCount = 28;
    const pods = new THREE.InstancedMesh(
        new THREE.CylinderGeometry(1.15, 1.15, 0.22, 6),
        new THREE.MeshBasicMaterial({ color: '#ffffff' }),
        podCount
    );
    pods.frustumCulled = false;
    roof.add(pods);

    const placePods = (time) => {
        for (let i = 0; i < podCount; i++) {
            const angle = (i / podCount) * TAU;
            const y = 17.5 + Math.sin(time * 0.6 + angle * 3) * 3.4;
            pods.setMatrixAt(i, placed(Math.cos(angle) * 21, y, Math.sin(angle) * 21, angle));
        }
        pods.instanceMatrix.needsUpdate = true;
    };
    placePods(0);

    const fixtures = [];
    for (let i = 0; i < 24; i++) {
        const angle = (i / 24) * TAU;
        const outward = i % 2 === 0;
        fixtures.push({
            x: Math.cos(angle) * RIG.ring,
            y: RIG.top - 0.3,
            z: Math.sin(angle) * RIG.ring,
            yaw: Math.PI - angle,
            tilt: outward ? -0.62 : 0.46,
            swing: 0.38,
            sweep: 0.3,
        });
    }

    /* Lasers fanning out from the stage */

    const laserCount = 14;
    const laserGeometry = new THREE.CylinderGeometry(0.04, 0.04, 130, 5).translate(0, 65, 0);
    const laserMaterial = new THREE.MeshBasicMaterial({ color: '#ffffff', transparent: true, opacity: 0, blending: THREE.AdditiveBlending, depthWrite: false, fog: false });
    const lasers = new THREE.InstancedMesh(laserGeometry, laserMaterial, laserCount);
    lasers.frustumCulled = false;
    lasers.position.y = STAGE.height + 0.3;
    lasers.visible = false;
    stage.add(lasers);

    const laserTurn = new THREE.Euler();
    const laserQuaternion = new THREE.Quaternion();
    const laserMatrix = new THREE.Matrix4();
    const laserPosition = new THREE.Vector3();
    const unit = new THREE.Vector3(1, 1, 1);
    const beats = Array.from({ length: laserCount }, () => random() * TAU);

    const aimLasers = (time) => {
        for (let i = 0; i < laserCount; i++) {
            const angle = (i / laserCount) * TAU + time * 0.16;
            laserPosition.set(Math.cos((i / laserCount) * TAU) * 9, 0, Math.sin((i / laserCount) * TAU) * 9);
            laserTurn.set(0, -angle, -1.05 + Math.sin(time * 0.5 + beats[i]) * 0.22, 'YXZ');
            laserQuaternion.setFromEuler(laserTurn);
            lasers.setMatrixAt(i, laserMatrix.compose(laserPosition, laserQuaternion, unit));
        }
        lasers.instanceMatrix.needsUpdate = true;
    };
    aimLasers(0);

    return {
        lights: movingLights(fixtures, { length: 74, radius: 8 }, roof),
        bands: [band, name],
        mood: {
            house: [2.1, 0.3],
            overhead: [1.4, 0.1],
            beam: [0.09, 0.26],
            terrace: '#6f737d',
            houseSeat: '#1c2c4c',
            skyLight: '#eef2ff',
            groundLight: '#2a2e3a',
            spill: [0, 16, 0],
            spillPower: 3200,
            spillReach: 120,
            stageGlow: [0, 70, 0],
            roofFade: [62, 104],
            haze: { x: 0, z: 0, width: 34, depth: 44 },
            confetti: { x: 0, z: 0, width: 60, depth: 60, top: 26 },
            background: '#090c13',
            fog: [260, 560],
        },
        onShow(value) {
            membrane.emissiveIntensity = lerp(0.42, 0.13, value);
            lasers.visible = value > 0.3;
            laserMaterial.opacity = smoothstep(value, 0.3, 0.9) * 0.38;
        },
        onFrame(time, colour, show, { phones }) {
            membrane.emissiveIntensity = lerp(0.42, 0.13, show) * (1 - phones * 0.8);
            placePods(time);
            pods.material.color.copy(colour).multiplyScalar(2);
            laserMaterial.color.copy(colour).multiplyScalar(1.6);
            aimLasers(time);
        },
    };
}

/* Round arena: a runway to a second stage, under rings of kinetic light */

function round({ data, blocks, rows, uniforms, materials, standard, parts, bowl: seatGroup, stage, roof, movingLights }) {
    const layout = data.layout;
    const [lower, upper] = layout.bowls;
    const WALL = { radius: 61.8, top: 30 };
    const DECK = { x: -24, width: 12, depth: 26, height: 1.7 };
    const RUNWAY = { from: -18, to: 6, width: 3.2, height: 1.1 };
    const SECOND = { x: 9, radius: 5 };
    const power = layout.corner_power;

    materials.carpet.color.set('#1c1730');
    materials.walkway.color.set('#25212f');
    materials.floor.color.set('#0a0812');

    seatGroup.add(disc(44, 44, power, materials.floor, -0.02));
    addCarpets(seatGroup, { blocks, data }, materials.carpet);

    addTerraces(seatGroup, layout, materials.terrace);
    addEndCaps(seatGroup, layout, lower, materials.mask, 0);
    addEndCaps(seatGroup, layout, upper, materials.mask, terraceProfile(upper)[0][1]);
    addAisles(seatGroup, layout, rows, data.seats, materials);

    const coveMaterial = barStripMaterial(new THREE.Color(1.4, 0.6, 2.2));
    connect(seatGroup, layout, lower, upper, materials.walkway, coveMaterial);
    frontRail(seatGroup, layout, lower, materials.glass, materials.steel);
    frontRail(seatGroup, layout, upper, materials.glass, materials.steel);

    const band = ribbon(seatGroup, layout, upper, data.stageName);

    stage.add(new THREE.Mesh(ring({
        profile: [[40, 0], [40, WALL.top]],
        radii: (offset) => [offset, offset],
        power,
        from: rad(151),
        to: TAU - rad(151),
        segments: 80,
        repeatU: 44,
    }), materials.drape));

    /* Roof: radial trusses meeting a set of hanging light rings */

    const wallPanels = canvasTexture(128, 256, (context, width, height) => {
        context.fillStyle = '#0f0c18';
        context.fillRect(0, 0, width, height);
        const fin = context.createLinearGradient(0, 0, width, 0);
        fin.addColorStop(0, '#0c0a14');
        fin.addColorStop(0.5, '#262034');
        fin.addColorStop(1, '#0c0a14');
        context.fillStyle = fin;
        context.fillRect(width * 0.1, 0, width * 0.8, height);
    });
    const wallMaterial = standard({ map: wallPanels, roughness: 0.8 });
    const wallStart = last(terraceProfile(upper))[1] - 0.2;

    roof.add(new THREE.Mesh(ring({
        profile: [[1, wallStart], [1, WALL.top]],
        radii: (scale) => [WALL.radius * scale, WALL.radius * scale],
        power,
        segments: 160,
        repeatU: 80,
    }), wallMaterial));

    const roofHeight = (scale) => WALL.top + 8 * (1 - scale * scale);
    const roofProfile = [];
    for (let i = 0; i <= 12; i++) {
        const scale = Math.max(1 - i / 12, 0.002);
        roofProfile.push([scale, roofHeight(scale)]);
    }

    const roofPanel = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#100d18';
        context.fillRect(0, 0, width, height);
        context.fillStyle = lit ? '#fff' : '#26222f';
        [0.26, 0.5, 0.74].forEach((row) => context.fillRect(0, row * height, width, 4));
        context.fillStyle = lit ? '#000' : '#1b1824';
        for (let i = 0; i < 8; i++) {
            context.fillRect((i / 8) * width, 0, 3, height);
        }
    };
    const roofMaterial = standard({
        map: canvasTexture(1024, 256, roofPanel(false)),
        emissiveMap: canvasTexture(1024, 256, roofPanel(true)),
        emissive: '#7b4dff',
        emissiveIntensity: 1.4,
    });
    roof.add(new THREE.Mesh(ring({
        profile: roofProfile,
        radii: (scale) => [WALL.radius * scale, WALL.radius * scale],
        power,
        segments: 96,
        repeatU: 3,
        smooth: true,
    }), roofMaterial));

    const beams = [];
    for (let i = 0; i < 28; i++) {
        const angle = (i / 28) * TAU;
        let previous = null;
        for (let step = 0; step <= 7; step++) {
            const scale = 1 - step * 0.11;
            const current = new THREE.Vector3(Math.cos(angle) * WALL.radius * scale, roofHeight(scale) - 0.9, Math.sin(angle) * WALL.radius * scale);
            if (previous) {
                beams.push(strut(previous, current, 1));
            }
            previous = current;
        }
    }
    roof.add(instanced(new THREE.BoxGeometry(0.4, 1, 0.9), materials.darkMetal, beams));

    const halos = [[7.5, 3.4, 23], [12.5, 1.6, 25], [17.5, 0.9, 26.6]].map(([radius, height, y], index) => {
        const halo = new THREE.Group();
        const screen = new THREE.Mesh(
            new THREE.CylinderGeometry(radius, radius, height, 96, 1, true),
            videoMaterial(uniforms, { grid: [Math.round(radius * 90), Math.round(height * 30)], repeatU: 2 + index, crop: 0.4 })
        );
        halo.add(screen);

        [-1, 1].forEach((side) => {
            const frame = new THREE.Mesh(new THREE.TorusGeometry(radius, 0.1, 8, 96).rotateX(Math.PI / 2), materials.darkMetal);
            frame.position.y = (side * height) / 2;
            halo.add(frame);
        });

        halo.position.set(0, y, 0);
        roof.add(halo);

        return { halo, y };
    });

    const cables = [];
    for (let i = 0; i < 12; i++) {
        const angle = (i / 12) * TAU;
        [17.5, 7.5].forEach((radius) => {
            const scale = radius / WALL.radius;
            cables.push(strut(new THREE.Vector3(Math.cos(angle) * radius, 24, Math.sin(angle) * radius), new THREE.Vector3(Math.cos(angle) * radius, roofHeight(scale), Math.sin(angle) * radius), 1));
        });
    }
    roof.add(instanced(new THREE.CylinderGeometry(0.03, 0.03, 1, 5), materials.steel, cables));

    /* Main stage */

    parts.box('deck', DECK.width, 0.12, DECK.depth, DECK.x, DECK.height - 0.06, 0);
    parts.box('mask', DECK.width, DECK.height - 0.12, DECK.depth, DECK.x, (DECK.height - 0.12) / 2, 0);
    parts.box('riserEdge', 0.05, 0.05, DECK.depth, DECK.x + DECK.width / 2, DECK.height, 0);

    const name = ledBand(data.stageName);
    name.repeat.set(1.4, 1);
    const front = new THREE.Mesh(new THREE.PlaneGeometry(DECK.depth, 1.05), new THREE.MeshBasicMaterial({ map: name, color: new THREE.Color(1.3, 1.3, 1.3) }));
    front.rotation.y = Math.PI / 2;
    front.position.set(DECK.x + DECK.width / 2 + 0.02, 0.72, 0);
    stage.add(front);

    /* Jagged fins of LED, one image spread across all of them */

    const FINS = { count: 9, span: 38, bottom: 2.4, top: 16.5, x: -31 };
    const fins = [];
    const finWidth = FINS.span / FINS.count;

    for (let i = 0; i < FINS.count; i++) {
        const left = -FINS.span / 2 + i * finWidth;
        const right = left + finWidth * 0.88;
        const middle = i - (FINS.count - 1) / 2;
        const lean = middle * 0.75;
        const near = FINS.x + (i % 2 ? -1.8 : 0);
        const far = FINS.x + (i % 2 ? 0 : -1.8);
        const lift = i % 2 ? 1.2 : 0;
        const corners = [
            [near, FINS.bottom + lift * 0.4, left],
            [far, FINS.bottom, right],
            [far + lean * 0.1, FINS.top - lift, right + lean],
            [near + lean * 0.1, FINS.top + lift * 0.6, left + lean],
        ];

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(corners.flat(), 3));
        geometry.setAttribute('uv', new THREE.Float32BufferAttribute(corners.flatMap(([, y, z]) => [
            (FINS.span / 2 + 3 - z) / (FINS.span + 6),
            (y - FINS.bottom) / (FINS.top - FINS.bottom + 1),
        ]), 2));
        geometry.setIndex([0, 1, 2, 0, 2, 3]);
        fins.push(geometry);
    }
    stage.add(new THREE.Mesh(mergeGeometries(fins), videoMaterial(uniforms, { grid: [640, 240], crop: 1, boost: 1.2 })));

    /* Runway and second stage */

    const runwayLength = RUNWAY.to - RUNWAY.from;
    const runwayMiddle = (RUNWAY.from + RUNWAY.to) / 2;
    parts.box('deck', runwayLength, 0.1, RUNWAY.width, runwayMiddle, RUNWAY.height - 0.05, 0);
    parts.box('mask', runwayLength, RUNWAY.height - 0.1, RUNWAY.width, runwayMiddle, (RUNWAY.height - 0.1) / 2, 0);
    [-1, 1].forEach((side) => {
        parts.box('riserEdge', runwayLength, 0.05, 0.05, runwayMiddle, RUNWAY.height, (side * RUNWAY.width) / 2);
    });

    parts.add('mask', new THREE.CylinderGeometry(SECOND.radius, SECOND.radius, RUNWAY.height, 56).translate(SECOND.x, RUNWAY.height / 2, 0));
    parts.add('riserEdge', new THREE.TorusGeometry(SECOND.radius, 0.05, 8, 72).rotateX(Math.PI / 2).translate(SECOND.x, RUNWAY.height + 0.02, 0));
    const secondFloor = new THREE.Mesh(new THREE.CircleGeometry(SECOND.radius - 0.05, 56).rotateX(-Math.PI / 2), videoMaterial(uniforms, { grid: [260, 260], crop: 1, boost: 0.8 }));
    secondFloor.position.set(SECOND.x, RUNWAY.height + 0.02, 0);
    stage.add(secondFloor);

    const barriers = [];
    for (let x = RUNWAY.from + 2; x < RUNWAY.to; x += 2) {
        [-1, 1].forEach((side) => barriers.push(placed(x, 0.6, side * 3, Math.PI / 2, 0.06, 1.15, 1.9)));
    }
    for (let z = -13; z <= 13; z += 2) {
        if (Math.abs(z) > 3) {
            barriers.push(placed(-16.6, 0.6, z, 0, 0.06, 1.15, 1.9));
        }
    }
    for (let i = -8; i <= 8; i++) {
        const angle = (i / 8) * 2.2;
        barriers.push(placed(SECOND.x + Math.cos(angle) * 6.1, 0.6, Math.sin(angle) * 6.1, -angle, 0.06, 1.15, 0.9));
    }
    stage.add(instanced(new THREE.BoxGeometry(1, 1, 1), materials.steel, barriers));

    /* Band on stage */

    [[-28, 3, 1.1, 18], [-26.5, 2.4, 0.7, 12]].forEach(([x, width, height, depth]) => {
        parts.box('riser', width, height, depth, x, DECK.height + height / 2, 0);
        parts.box('riserEdge', 0.05, 0.05, depth, x + width / 2, DECK.height + height, 0);
    });
    addDrumKit(parts, -26.5, DECK.height + 0.7, 0);
    [-5, 0, 5].forEach((z) => micStand(parts, -20, DECK.height, z));
    micStand(parts, SECOND.x, RUNWAY.height, 0);
    [-11, -7, -3, 3, 7, 11].forEach((z) => wedge(parts, -18.8, DECK.height, z));

    /* Rig */

    addTrussLines(stage, materials.steel, [-27, -20], 15, 17, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -18, 15, side * 19, side * 0.25));

    const fixtures = [];
    for (let i = 0; i < 8; i++) {
        const z = -13 + i * 3.7;
        fixtures.push({ x: -20, y: 16.7, z, tilt: 0.48 }, { x: -27, y: 16.7, z, tilt: -0.34 });
    }

    const flown = [];
    for (let i = 0; i < 12; i++) {
        const angle = (i / 12) * TAU;
        flown.push({ x: Math.cos(angle) * 17.5, y: 26, z: Math.sin(angle) * 17.5, yaw: Math.PI - angle, tilt: -0.5, swing: 0.3, sweep: 0.26 });
    }

    return {
        lights: [...movingLights(fixtures, { length: 46, radius: 5 }), ...movingLights(flown, { length: 46, radius: 5 }, roof)],
        bands: [band, name],
        mood: {
            house: [1.5, 0.24],
            overhead: [1.1, 0.1],
            terrace: '#56505f',
            houseSeat: '#2c1a4a',
            skyLight: '#ddd0ff',
            groundLight: '#221a3a',
            spill: [-18, 9, 0],
            stageGlow: [-64, 16, 0],
            roofFade: [38, 56],
            haze: { x: -10, z: 0, width: 30, depth: 30 },
            background: '#08050f',
            fog: [170, 340],
        },
        onShow(value) {
            roofMaterial.emissiveIntensity = lerp(1.4, 0.8, value);
        },
        onFrame(time, colour, show, { phones }) {
            roofMaterial.emissiveIntensity = lerp(1.4, 0.8, show) * (1 - phones * 0.8);
            halos.forEach(({ halo, y }, index) => {
                halo.position.y = y + Math.sin(time * 0.5 + index * 1.4) * 0.7;
            });
            coveMaterial.color.copy(colour).multiplyScalar(2);
            roofMaterial.emissive.copy(colour);
        },
    };
}

/**
 * A long brick hall from the 1930s under concrete portal frames, with a seated
 * floor, a mirror ball, and walls of old stage bulbs beside the screen.
 */
function shoebox({ data, blocks, rows, uniforms, materials, standard, parts, roofParts, bowl: seatGroup, stage, roof, movingLights }) {
    const layout = data.layout;
    const [lower, upper] = layout.bowls;
    const HALL = { back: -46, front: 64, half: 50, eaves: 22, crown: 33 };
    const DECK = { x: -29, width: 11, depth: 30, height: 1.6 };
    const SCREEN = { x: -35.4, width: 20, bottom: 2.7, height: 11.2 };
    const BALL = { x: 8, y: 20.5, z: 0, radius: 2.1 };
    const length = HALL.front - HALL.back;
    const middle = (HALL.front + HALL.back) / 2;
    const arch = (z) => HALL.eaves + (HALL.crown - HALL.eaves) * (1 - (z / HALL.half) ** 2);
    const random = seeded(1934);

    materials.carpet.color.set('#1a1f28');
    materials.walkway.color.set('#22252c');
    materials.floor.color.set('#0b0c10');

    const ground = new THREE.Mesh(new THREE.PlaneGeometry(length, HALL.half * 2).rotateX(-Math.PI / 2), materials.dark);
    ground.position.set(middle, -0.06, 0);
    seatGroup.add(ground);
    seatGroup.add(disc(lower.radius_x + 2, lower.radius_z + 2, layout.corner_power, materials.floor, -0.02));
    addCarpets(seatGroup, { blocks, data }, materials.carpet);

    addTerraces(seatGroup, layout, materials.terrace);
    addEndCaps(seatGroup, layout, lower, materials.mask, 0);
    addEndCaps(seatGroup, layout, upper, materials.mask, 0);
    addAisles(seatGroup, layout, rows, data.seats, materials);

    const coveMaterial = barStripMaterial(new THREE.Color(0.6, 0.9, 1.7));
    connect(seatGroup, layout, lower, upper, materials.walkway, coveMaterial);
    frontRail(seatGroup, layout, lower, materials.glass, materials.steel);
    frontRail(seatGroup, layout, upper, materials.glass, materials.steel);

    const band = ribbon(seatGroup, layout, upper, data.stageName);

    /* Brick walls, which only show from inside */

    const brick = canvasTexture(512, 256, (context, width, height) => {
        const courses = 14;
        const across = 8;
        const brickHeight = height / courses;
        const brickWidth = width / across;

        context.fillStyle = '#1e1513';
        context.fillRect(0, 0, width, height);

        for (let course = 0; course < courses; course++) {
            for (let column = -1; column < across; column++) {
                const shade = 58 + random() * 36;
                context.fillStyle = `rgb(${shade + 26}, ${shade * 0.64}, ${shade * 0.5})`;
                context.fillRect((column + (course % 2) * 0.5) * brickWidth + 1, course * brickHeight + 1, brickWidth - 2, brickHeight - 2);
            }
        }
    });

    const metres = (geometry, across, up) => {
        const uv = geometry.attributes.uv;
        for (let i = 0; i < uv.count; i++) {
            uv.setXY(i, uv.getX(i) * across, uv.getY(i) * up);
        }
        return geometry;
    };

    const sideWall = (z, yaw) => metres(new THREE.PlaneGeometry(length, HALL.eaves), length / 9, HALL.eaves / 4.5)
        .rotateY(yaw)
        .translate(middle, HALL.eaves / 2, z);

    const gable = new THREE.Shape();
    gable.moveTo(-HALL.half, 0);
    gable.lineTo(HALL.half, 0);
    for (let step = 0; step <= 24; step++) {
        const z = HALL.half - (step / 24) * HALL.half * 2;
        gable.lineTo(z, arch(z));
    }

    const endWall = (x, yaw) => metres(new THREE.ShapeGeometry(gable), 1 / 9, 1 / 4.5).rotateY(yaw).translate(x, 0, 0);

    stage.add(new THREE.Mesh(mergeGeometries([
        sideWall(-HALL.half, 0),
        sideWall(HALL.half, Math.PI),
        endWall(HALL.front, -Math.PI / 2),
        endWall(HALL.back, Math.PI / 2),
    ]), standard({ map: brick, roughness: 0.92 })));

    /* Portal frames and the barrel roof they carry */

    const concreteFrame = standard({ color: '#6c6b68', roughness: 0.86 });
    const arches = [];
    const piers = [];

    for (let x = HALL.back + 6; x < HALL.front; x += 12) {
        let previous = null;

        for (let step = 0; step <= 16; step++) {
            const z = -HALL.half + (step / 16) * HALL.half * 2;
            const current = new THREE.Vector3(x, arch(z) - 1.1, z);

            if (previous) {
                arches.push(strut(previous, current, 1));
            }
            previous = current;
        }

        [-1, 1].forEach((side) => piers.push(placed(x, HALL.eaves / 2, side * (HALL.half - 1), 0, 1.7, HALL.eaves, 2)));
    }

    roof.add(instanced(new THREE.BoxGeometry(1.1, 1, 1.8), concreteFrame, arches));
    roof.add(instanced(new THREE.BoxGeometry(1, 1, 1), concreteFrame, piers));

    const skin = new THREE.PlaneGeometry(length, HALL.half * 2, 1, 32).rotateX(Math.PI / 2).translate(middle, 0, 0);
    const skinPoints = skin.attributes.position;
    for (let i = 0; i < skinPoints.count; i++) {
        skinPoints.setY(i, arch(skinPoints.getZ(i)));
    }
    skin.computeVertexNormals();

    const roofPanels = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#14161b';
        context.fillRect(0, 0, width, height);
        context.fillStyle = lit ? '#000' : '#20232a';
        for (let i = 0; i < 18; i++) {
            context.fillRect(0, (i / 18) * height, width, 3);
        }
        context.fillStyle = lit ? '#fff' : '#39404c';
        [0.2, 0.37, 0.63, 0.8].forEach((row) => context.fillRect(0, row * height, width, height * 0.045));
    };
    const roofMaterial = standard({
        map: canvasTexture(256, 1024, roofPanels(false)),
        emissiveMap: canvasTexture(256, 1024, roofPanels(true)),
        emissive: '#8fb4ff',
        emissiveIntensity: 1.2,
        side: THREE.DoubleSide,
    });
    roof.add(new THREE.Mesh(skin, roofMaterial));

    /* Mirror ball */

    const mirror = new THREE.Mesh(new THREE.IcosahedronGeometry(BALL.radius, 3), standard({
        color: '#e7ebf2',
        metalness: 1,
        roughness: 0.12,
        flatShading: true,
        envMapIntensity: 2.2,
    }));
    mirror.position.set(BALL.x, BALL.y, BALL.z);
    stage.add(mirror);
    roofParts.box('steel', 0.05, HALL.crown - BALL.y - BALL.radius, 0.05, BALL.x, (HALL.crown + BALL.y + BALL.radius) / 2, BALL.z);

    const ballGlow = new THREE.Sprite(new THREE.SpriteMaterial({
        map: softDotTexture(),
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        opacity: 0,
        fog: false,
    }));
    ballGlow.position.copy(mirror.position);
    ballGlow.scale.setScalar(11);
    stage.add(ballGlow);

    const sparkCount = 900;
    const sparkPoints = new Float32Array(sparkCount * 3);

    for (let i = 0; i < sparkCount; i++) {
        const height = random() * 2 - 1;
        const angle = random() * TAU;
        const flat = Math.sqrt(1 - height * height);
        sparkPoints.set([Math.cos(angle) * flat * 56, Math.abs(height) * -BALL.y + (random() - 0.2) * 9, Math.sin(angle) * flat * 44], i * 3);
    }

    const sparkGeometry = new THREE.BufferGeometry();
    sparkGeometry.setAttribute('position', new THREE.BufferAttribute(sparkPoints, 3));

    const sparkMaterial = new THREE.PointsMaterial({
        map: softDotTexture(),
        size: 0.75,
        color: '#dfe9ff',
        transparent: true,
        opacity: 0,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    });
    const sparks = new THREE.Points(sparkGeometry, sparkMaterial);
    sparks.position.copy(mirror.position);
    sparks.frustumCulled = false;
    sparks.visible = false;
    stage.add(sparks);

    /* Stage */

    parts.box('deck', DECK.width, 0.12, DECK.depth, DECK.x, DECK.height - 0.06, 0);
    parts.box('mask', DECK.width, DECK.height - 0.12, DECK.depth, DECK.x, (DECK.height - 0.12) / 2, 0);
    parts.box('riserEdge', 0.05, 0.05, DECK.depth, DECK.x + DECK.width / 2, DECK.height, 0);

    const name = ledBand(data.stageName);
    name.repeat.set(1.4, 1);
    const front = new THREE.Mesh(new THREE.PlaneGeometry(DECK.depth, 1.05), new THREE.MeshBasicMaterial({ map: name, color: new THREE.Color(1.3, 1.3, 1.3) }));
    front.rotation.y = Math.PI / 2;
    front.position.set(DECK.x + DECK.width / 2 + 0.02, 0.72, 0);
    stage.add(front);

    const backdrop = new THREE.Mesh(metres(new THREE.PlaneGeometry(64, 27), 30, 1), materials.drape);
    backdrop.rotation.y = Math.PI / 2;
    backdrop.position.set(SCREEN.x - 1.4, 13.5, 0);
    stage.add(backdrop);

    const screen = new THREE.Mesh(
        new THREE.PlaneGeometry(SCREEN.width, SCREEN.height),
        videoMaterial(uniforms, { grid: [640, 360], crop: 1, boost: 1.15 })
    );
    screen.rotation.y = Math.PI / 2;
    screen.position.set(SCREEN.x, SCREEN.bottom + SCREEN.height / 2, 0);
    stage.add(screen);
    parts.box('darkMetal', 0.2, SCREEN.height + 0.4, SCREEN.width + 0.4, SCREEN.x - 0.12, SCREEN.bottom + SCREEN.height / 2, 0);

    [-1, 1].forEach((side) => {
        const close = new THREE.Mesh(new THREE.PlaneGeometry(8.6, 4.9), videoMaterial(uniforms, { grid: [300, 170], crop: 0.8 }));
        close.rotation.y = Math.PI / 2 - side * 0.3;
        close.position.set(-32.5, 12.6, side * 23);
        stage.add(close);
    });

    /* Walls of old stage bulbs either side of the screen */

    const BULBS = { columns: 7, rows: 12, gap: 0.64 };
    const bulbPlaces = [];
    const bulbGrid = [];

    [-1, 1].forEach((side) => {
        for (let column = 0; column < BULBS.columns; column++) {
            for (let row = 0; row < BULBS.rows; row++) {
                const z = side * (SCREEN.width / 2 + 0.9 + column * BULBS.gap);
                const y = SCREEN.bottom + 0.7 + row * BULBS.gap;
                bulbPlaces.push(placed(SCREEN.x + 0.12, y, z));
                bulbGrid.push({ column, row });
            }
        }
        parts.box('darkMetal', 0.12, BULBS.rows * BULBS.gap + 0.3, BULBS.columns * BULBS.gap + 0.3, SCREEN.x, SCREEN.bottom + 0.4 + (BULBS.rows * BULBS.gap) / 2, side * (SCREEN.width / 2 + 0.6 + (BULBS.columns * BULBS.gap) / 2));
    });

    const bulbs = instanced(new THREE.SphereGeometry(0.2, 10, 8), new THREE.MeshBasicMaterial({ color: '#ffffff' }), bulbPlaces);
    const bulbColour = new THREE.Color();
    stage.add(bulbs);

    /* A rock backline */

    const AMP = { x: -33.1, height: 1.5, depth: 2.2 };

    [-13, -10.6, -8.2, -5.8, 5.8, 8.2, 10.6, 13].forEach((z) => {
        parts.box('speaker', 0.9, AMP.height, AMP.depth, AMP.x, DECK.height + AMP.height / 2, z);
        parts.box('speaker', 0.9, AMP.height, AMP.depth, AMP.x, DECK.height + AMP.height * 1.5 + 0.02, z);
        parts.box('darkMetal', 0.95, 0.6, AMP.depth, AMP.x, DECK.height + AMP.height * 2 + 0.33, z);
        parts.box('metal', 0.02, 0.05, AMP.depth * 0.7, AMP.x + 0.49, DECK.height + AMP.height * 2 + 0.38, z);
        parts.box('riserEdge', 0.02, 0.05, 0.05, AMP.x + 0.49, DECK.height + AMP.height * 2 + 0.22, z + 0.9);
    });

    parts.box('riser', 3.4, 0.7, 7.5, -31.5, DECK.height + 0.35, 0);
    parts.box('riserEdge', 0.05, 0.05, 7.5, -29.8, DECK.height + 0.7, 0);
    addDrumKit(parts, -31.5, DECK.height + 0.7, 0);
    [-5.5, 0, 5.5].forEach((z) => micStand(parts, -25.6, DECK.height, z));
    [-9, -5, 5, 9].forEach((z) => wedge(parts, -24.3, DECK.height, z));

    const barriers = [];
    for (let z = -14; z <= 14; z += 2) {
        barriers.push(placed(-22.3, 0.6, z, 0, 0.06, 1.15, 1.9));
    }
    stage.add(instanced(new THREE.BoxGeometry(1, 1, 1), materials.steel, barriers));

    /* Rig */

    addTrussLines(stage, materials.steel, [-24.5, -31], 15, 17.6, 0);
    [-1, 1].forEach((side) => addLineArray(stage, materials.speaker, -23, 15.5, side * 19.5, side * 0.24));

    const fixtures = [];
    for (let i = 0; i < 8; i++) {
        const z = -13 + i * 3.7;
        fixtures.push({ x: -24.5, y: 17.3, z, tilt: 0.48 }, { x: -31, y: 17.3, z, tilt: -0.34 });
    }

    const flown = [];
    [-1, 1].forEach((side) => {
        roofParts.box('steel', 56, 0.4, 0.4, 16, 21.5, side * 17);

        for (let x = -8; x <= 40; x += 12) {
            flown.push({ x, y: 21.2, z: side * 17, tilt: -0.5, swing: 0.34, sweep: 0.3 });
            roofParts.box('steel', 0.05, arch(17) - 21.7, 0.05, x, (arch(17) + 21.7) / 2, side * 17);
        }
    });

    return {
        lights: [...movingLights(fixtures, { length: 44, radius: 5 }), ...movingLights(flown, { length: 34, radius: 4.2 }, roof)],
        bands: [band, name],
        mood: {
            house: [1.45, 0.2],
            overhead: [1, 0.08],
            terrace: '#5f6168',
            houseSeat: '#1f2a38',
            skyLight: '#dbe6ff',
            groundLight: '#1e2026',
            spill: [-20, 9, 0],
            spillPower: 620,
            stageGlow: [-70, 16, 0],
            roofFade: [36, 54],
            haze: { x: -18, z: 0, width: 26, depth: 30 },
            background: '#0a0c10',
            fog: [170, 340],
            confetti: { x: -6, z: 0, width: 48, depth: 34, top: 17 },
        },
        onShow(value) {
            roofMaterial.emissiveIntensity = lerp(1.2, 0.16, value);
        },
        onFrame(time, colour, show, { bass, kick }) {
            mirror.rotation.y = time * 0.28;
            sparks.rotation.y = time * 0.28;
            sparks.visible = show > 0.05;
            sparkMaterial.opacity = show * (0.28 + bass * 0.72);
            ballGlow.material.opacity = show * (0.1 + kick * 0.5);
            coveMaterial.color.copy(colour).multiplyScalar(1.6);

            bulbGrid.forEach(({ column, row }, index) => {
                const chase = Math.sin(row * 0.9 + column * 0.5 - time * 3.2) * 0.5 + 0.5;
                const level = lerp(0.12, 0.2 + chase * 0.4 + bass * 0.9, show);
                bulbs.setColorAt(index, bulbColour.setRGB(3 * level, 1.65 * level, 0.65 * level));
            });
            bulbs.instanceColor.needsUpdate = true;
        },
    };
}

export const venues = { amphitheatre, hall, dome, round, shoebox };
