// The shared 3D kit and the default indoor arena: bowl, ceiling, bridges, stage
// and lighting. Seat positions come from the server and everything else is built
// from them, so aisles and rails always line up with the real seats. seat-map.js
// owns the seats and the camera; this file owns what they sit inside. Other
// buildings live in venues.js and plug in through buildArena's `venue` option.

import * as THREE from 'three';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';

export const TAU = Math.PI * 2;
const FONT = "'Geist', system-ui, sans-serif";

const CEILING_HEIGHT = 25;
const DOME_RISE = 9;
const VIDEO_SAMPLE_MS = 250;
const WAVE_SECONDS = 8;
const WAVE_SPEED = 1.7;
const CONFETTI_SECONDS = 16;
const NOSING = '#e9b949';
const STAGE_FRONT_X = -29;

const FALLBACK_COLOURS = ['#6a3dff', '#ff3d7f', '#17c6c6', '#ffb020'];

export const seatUniforms = {
    uHouse: { value: 0 },
    uHouseColor: { value: new THREE.Color('#1a2140') },
};

export function superellipsePoint(angle, radiusX, radiusZ, power) {
    const cos = Math.cos(angle);
    const sin = Math.sin(angle);
    const radius = Math.pow(
        Math.pow(Math.abs(cos) / radiusX, power) + Math.pow(Math.abs(sin) / radiusZ, power),
        -1 / power
    );

    return [radius * cos, radius * sin];
}

/* Seats */

function roundedRect(width, height, radius) {
    const shape = new THREE.Shape();
    const x = -width / 2;
    const y = -height / 2;

    shape.moveTo(x + radius, y);
    shape.lineTo(x + width - radius, y);
    shape.quadraticCurveTo(x + width, y, x + width, y + radius);
    shape.lineTo(x + width, y + height - radius);
    shape.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    shape.lineTo(x + radius, y + height);
    shape.quadraticCurveTo(x, y + height, x, y + height - radius);
    shape.lineTo(x, y + radius);
    shape.quadraticCurveTo(x, y, x + radius, y);

    return shape;
}

function cushion(width, height, thickness, radius) {
    const geometry = new THREE.ExtrudeGeometry(roundedRect(width, height, radius), {
        depth: thickness,
        bevelEnabled: true,
        bevelThickness: 0.018,
        bevelSize: 0.016,
        bevelSegments: 2,
        curveSegments: 4,
    });
    geometry.translate(0, 0, -thickness / 2);

    return geometry;
}

/** Bends a flat cushion so it wraps around the sitter, like a moulded seat. */
function curve(geometry, amount) {
    const position = geometry.attributes.position;

    for (let i = 0; i < position.count; i++) {
        const x = position.getX(i);
        position.setZ(i, position.getZ(i) + x * x * amount);
    }
    geometry.computeVertexNormals();

    return geometry;
}

export function seatGeometries() {
    const farPan = new THREE.BoxGeometry(0.47, 0.1, 0.42);
    farPan.translate(0, 0.44, 0.03);
    const farBack = new THREE.BoxGeometry(0.47, 0.56, 0.08);
    farBack.rotateX(-0.12);
    farBack.translate(0, 0.74, -0.2);

    const pan = curve(cushion(0.43, 0.39, 0.06, 0.06), 0.18);
    pan.rotateX(-Math.PI / 2 + 0.05);
    pan.translate(0, 0.44, 0.04);

    const back = curve(cushion(0.43, 0.52, 0.05, 0.09), 0.5);
    back.rotateX(-0.14);
    back.translate(0, 0.77, -0.2);

    const leg = new THREE.BoxGeometry(0.05, 0.62, 0.34);
    leg.translate(-0.28, 0.31, -0.06);
    const arm = new THREE.BoxGeometry(0.055, 0.035, 0.36);
    arm.translate(-0.28, 0.635, 0.02);
    const foot = new THREE.BoxGeometry(0.08, 0.03, 0.42);
    foot.translate(-0.28, 0.015, -0.04);
    const spine = new THREE.BoxGeometry(0.2, 0.42, 0.018);
    spine.rotateX(-0.14);
    spine.translate(0, 0.74, -0.255);

    return {
        far: mergeGeometries([farPan, farBack]),
        near: mergeGeometries([pan, back]),
        frame: mergeGeometries([leg, arm, foot, spine]),
    };
}

/** Tier colours in the plan view; the real upholstery once you are in a seat. */
export function seatMaterial(parameters) {
    const material = new THREE.MeshStandardMaterial(parameters);

    material.onBeforeCompile = (shader) => {
        shader.uniforms.uHouse = seatUniforms.uHouse;
        shader.uniforms.uHouseColor = seatUniforms.uHouseColor;
        shader.vertexShader = `attribute float aKeep;
            uniform float uHouse;
            uniform vec3 uHouseColor;
            varying float vKeep;
            ${shader.vertexShader.replace('#include <color_vertex>', `#include <color_vertex>
            vKeep = aKeep;
            vColor = mix(vColor, uHouseColor, uHouse * (1.0 - aKeep));`)}`;
        shader.fragmentShader = `varying float vKeep;
            ${shader.fragmentShader.replace('#include <emissivemap_fragment>', `#include <emissivemap_fragment>
            totalEmissiveRadiance += diffuseColor.rgb * vKeep * 0.85;`)}`;
    };

    return material;
}

export function fabricNormalMap() {
    const size = 64;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const context = canvas.getContext('2d');
    const image = context.createImageData(size, size);

    for (let y = 0; y < size; y++) {
        for (let x = 0; x < size; x++) {
            const over = (x >> 1) % 2 === (y >> 1) % 2;
            const offset = (y * size + x) * 4;
            image.data[offset] = 128 + (over ? 26 : -26) + Math.random() * 14;
            image.data[offset + 1] = 128 + (over ? -26 : 26) + Math.random() * 14;
            image.data[offset + 2] = 235;
            image.data[offset + 3] = 255;
        }
    }
    context.putImageData(image, 0, 0);

    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(9, 9);
    texture.anisotropy = 4;

    return texture;
}

/* Small helpers */

export function canvasTexture(width, height, draw, { srgb = true } = {}) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    draw(canvas.getContext('2d'), width, height);

    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.anisotropy = 8;
    if (srgb) {
        texture.colorSpace = THREE.SRGBColorSpace;
    }

    return texture;
}

/**
 * Sweeps a profile of [a, y] points around the arena. `radii(a)` turns a into
 * the two radii of the superellipse at that point. Faces point up or inwards.
 */
export function ring({ profile, radii, power, centre = [0, 0], from = -Math.PI, to = Math.PI, segments = 240, repeatU = 1, metresV = 0, smooth = false }) {
    const positions = [];
    const uvs = [];
    const indices = [];
    const lengths = [0];

    for (let i = 1; i < profile.length; i++) {
        const [ra, rb] = radii(profile[i][0]);
        const [pa, pb] = radii(profile[i - 1][0]);
        lengths.push(lengths[i - 1] + Math.hypot(ra - pa, rb - pb, profile[i][1] - profile[i - 1][1]));
    }
    const total = lengths[lengths.length - 1] || 1;

    for (let segment = 0; segment <= segments; segment++) {
        const angle = from + ((to - from) * segment) / segments;

        profile.forEach(([a, y], index) => {
            const [radiusX, radiusZ] = radii(a);
            const [x, z] = superellipsePoint(angle, radiusX, radiusZ, power);
            positions.push(x + centre[0], y, z + centre[1]);
            uvs.push((segment / segments) * repeatU, metresV ? lengths[index] / metresV : lengths[index] / total);
        });
    }

    const stride = profile.length;
    for (let segment = 0; segment < segments; segment++) {
        for (let point = 0; point < stride - 1; point++) {
            const a = segment * stride + point;
            const b = a + stride;
            indices.push(a, b, a + 1, b, b + 1, a + 1);
        }
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
    geometry.setAttribute('uv', new THREE.Float32BufferAttribute(uvs, 2));
    geometry.setIndex(indices);

    const finished = smooth ? geometry : geometry.toNonIndexed();
    finished.computeVertexNormals();

    return finished;
}

export function instanced(geometry, material, matrices) {
    const mesh = new THREE.InstancedMesh(geometry, material, matrices.length);
    matrices.forEach((matrix, index) => mesh.setMatrixAt(index, matrix));
    mesh.instanceMatrix.needsUpdate = true;
    mesh.frustumCulled = false;

    return mesh;
}

export const UP = new THREE.Vector3(0, 1, 0);

/** A unit cylinder or box stretched between two points. */
export function strut(a, b, thickness) {
    const direction = new THREE.Vector3().subVectors(b, a);
    const length = direction.length();
    const quaternion = new THREE.Quaternion().setFromUnitVectors(UP, direction.normalize());
    const middle = new THREE.Vector3().addVectors(a, b).multiplyScalar(0.5);

    return new THREE.Matrix4().compose(middle, quaternion, new THREE.Vector3(thickness, length, thickness));
}

export function placed(x, y, z, rotationY = 0, scaleX = 1, scaleY = 1, scaleZ = 1) {
    return new THREE.Matrix4().compose(
        new THREE.Vector3(x, y, z),
        new THREE.Quaternion().setFromAxisAngle(UP, rotationY),
        new THREE.Vector3(scaleX, scaleY, scaleZ)
    );
}

/** Collects geometry by material so a whole stage is only a few draw calls. */
export class Parts {
    constructor(materials) {
        this.materials = materials;
        this.pieces = {};
    }

    add(name, geometry) {
        (this.pieces[name] ??= []).push(geometry);
        return this;
    }

    box(name, width, height, depth, x, y, z) {
        return this.add(name, new THREE.BoxGeometry(width, height, depth).translate(x, y, z));
    }

    drum(name, radius, length, x, y, z, axis = 'y') {
        const geometry = new THREE.CylinderGeometry(radius, radius, length, 16);
        if (axis === 'x') {
            geometry.rotateZ(Math.PI / 2);
        }
        if (axis === 'z') {
            geometry.rotateX(Math.PI / 2);
        }
        return this.add(name, geometry.translate(x, y, z));
    }

    build(group) {
        Object.entries(this.pieces).forEach(([name, list]) => {
            group.add(new THREE.Mesh(mergeGeometries(list), this.materials[name]));
        });
    }
}

/* Surfaces */

export function concreteTexture() {
    return canvasTexture(256, 256, (context, width, height) => {
        context.fillStyle = '#3a3a40';
        context.fillRect(0, 0, width, height);

        for (let i = 0; i < 2600; i++) {
            const shade = 44 + Math.random() * 34;
            context.fillStyle = `rgba(${shade}, ${shade}, ${shade + 4}, .5)`;
            context.fillRect(Math.random() * width, Math.random() * height, 1 + Math.random() * 3, 1 + Math.random() * 3);
        }
    });
}

function wallPanels() {
    const draw = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#17171d';
        context.fillRect(0, 0, width, height);

        if (!lit) {
            context.strokeStyle = '#08080c';
            context.lineWidth = 4;
            for (let x = 0; x <= width; x += width / 6) {
                context.strokeRect(x, 0, width / 6, height / 2);
                context.strokeRect(x - width / 12, height / 2, width / 6, height / 2);
            }
        }

        // A concourse portal with the lights of the walkway behind it.
        const door = { x: width * 0.42, y: height * 0.34, width: width * 0.16, height: height * 0.66 };
        const glow = context.createLinearGradient(0, door.y, 0, height);
        glow.addColorStop(0, lit ? '#ffcf94' : '#7a5c3a');
        glow.addColorStop(1, lit ? '#6e4a24' : '#2a2019');
        context.fillStyle = glow;
        context.fillRect(door.x, door.y, door.width, door.height);

        context.fillStyle = lit ? '#fff' : '#d9d9de';
        context.fillRect(door.x + door.width * 0.2, door.y - height * 0.09, door.width * 0.6, height * 0.05);
    };

    return { map: canvasTexture(512, 128, draw(false)), emissive: canvasTexture(512, 128, draw(true)) };
}

function suiteWindows() {
    const draw = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#101016';
        context.fillRect(0, 0, width, height);

        const bays = 4;
        const bay = width / bays;

        for (let i = 0; i < bays; i++) {
            const warmth = 0.5 + Math.random() * 0.5;
            const glass = context.createLinearGradient(0, height * 0.2, 0, height * 0.86);
            glass.addColorStop(0, `rgba(255, 214, 160, ${warmth})`);
            glass.addColorStop(1, `rgba(120, 76, 44, ${warmth})`);
            context.fillStyle = lit ? glass : '#2c2622';
            context.fillRect(i * bay + 6, height * 0.2, bay - 12, height * 0.66);

            if (Math.random() < 0.6) {
                context.fillStyle = lit ? '#000' : '#141417';
                const x = i * bay + 20 + Math.random() * (bay - 50);
                context.fillRect(x, height * 0.46, 10, height * 0.4);
                context.beginPath();
                context.arc(x + 5, height * 0.4, 7, 0, TAU);
                context.fill();
            }
        }
    };

    return { map: canvasTexture(512, 128, draw(false)), emissive: canvasTexture(512, 128, draw(true)) };
}

function ceilingTexture() {
    const draw = (lit) => (context, width, height) => {
        context.fillStyle = lit ? '#000' : '#101016';
        context.fillRect(0, 0, width, height);

        if (!lit) {
            context.strokeStyle = '#1f1f28';
            context.lineWidth = 3;
            for (let i = 0; i <= 72; i++) {
                const x = (i / 72) * width;
                context.beginPath();
                context.moveTo(x, 0);
                context.lineTo(x, height);
                context.stroke();
            }
            for (let i = 0; i <= 12; i++) {
                context.beginPath();
                context.moveTo(0, (i / 12) * height);
                context.lineTo(width, (i / 12) * height);
                context.stroke();
            }
        }

        // Rings of recessed light, as in a circular arena ceiling.
        [0.22, 0.46, 0.72].forEach((row) => {
            context.fillStyle = lit ? '#b9c6ff' : '#30323f';
            for (let i = 0; i < 144; i++) {
                if (i % 3 !== 0) {
                    context.fillRect((i / 144) * width, row * height, width / 300, 3);
                }
            }
        });
    };

    return { map: canvasTexture(2048, 512, draw(false)), emissive: canvasTexture(2048, 512, draw(true)) };
}

export function ledBand(text) {
    const texture = canvasTexture(2048, 64, (context, width, height) => {
        const gradient = context.createLinearGradient(0, 0, width, 0);
        gradient.addColorStop(0, '#ff3d5e');
        gradient.addColorStop(0.33, '#6a3dff');
        gradient.addColorStop(0.66, '#17c6c6');
        gradient.addColorStop(1, '#ff3d5e');
        context.fillStyle = gradient;
        context.fillRect(0, 0, width, height);

        context.fillStyle = '#ffffff';
        context.font = `800 40px ${FONT}`;
        context.textBaseline = 'middle';
        const label = `${text.toUpperCase()}   ·   `;
        const step = Math.max(context.measureText(label).width, 200);
        for (let x = 0; x < width; x += step) {
            context.fillText(label, x, height / 2 + 2);
        }
    });

    return texture;
}

/* The bowl */

export function terraceProfile(bowl) {
    const half = bowl.row_depth / 2;
    const profile = [[-half - 0.6, bowl.height - 1.42], [-half, bowl.height - 1.42]];

    for (let row = 0; row < bowl.rows; row++) {
        const offset = row * bowl.row_depth;
        const y = bowl.height + row * bowl.rise - 0.02;
        profile.push([offset - half, y], [offset + half, y]);
    }

    return profile;
}

function addBowl(group, layout, materials, stageName) {
    const { corner_power: power, sweep_degrees: sweep } = layout;
    const [lower, upper] = layout.bowls;
    const limit = THREE.MathUtils.degToRad(sweep + 3);
    const around = (bowl) => (offset) => [bowl.radius_x + offset, bowl.radius_z + offset];

    const lowerProfile = terraceProfile(lower);
    const upperProfile = terraceProfile(upper);
    const lowerEnd = lowerProfile[lowerProfile.length - 1];
    const upperStart = upperProfile[0];
    const upperEnd = upperProfile[upperProfile.length - 1];
    const toUpper = upper.radius_x - lower.radius_x;
    const bandTop = upperStart[1] + 1.2;

    const sweepRing = (bowl, profile, options) => ring({
        profile, radii: around(bowl), power, from: -limit, to: limit, ...options,
    });

    group.add(new THREE.Mesh(sweepRing(lower, lowerProfile, { segments: 320, repeatU: 90, metresV: 4 }), materials.terrace));
    group.add(new THREE.Mesh(sweepRing(upper, upperProfile, { segments: 320, repeatU: 110, metresV: 4 }), materials.terrace));

    // Walkway behind the lower bowl, then the band of lit suites above it.
    group.add(new THREE.Mesh(sweepRing(lower, [lowerEnd, [toUpper - 1.6, lowerEnd[1]]], { repeatU: 90, metresV: 4 }), materials.walkway));
    group.add(new THREE.Mesh(sweepRing(lower, [[toUpper - 1.6, lowerEnd[1]], [toUpper - 1.6, bandTop]], { repeatU: 72 }), materials.suites));
    group.add(new THREE.Mesh(sweepRing(lower, [[toUpper - 1.6, bandTop], [toUpper + upperStart[0], bandTop]], {}), materials.dark));

    // The moving LED band along the front of the upper tier.
    const fascia = [[upperStart[0] - 0.05, upperStart[1] + 0.3], [upperStart[0] - 0.05, upperStart[1] + 1.15]];
    const band = ledBand(stageName);
    band.repeat.set(10, 1);
    const ribbonMaterial = new THREE.MeshBasicMaterial({ map: band, color: new THREE.Color(1.5, 1.5, 1.5) });
    group.add(new THREE.Mesh(sweepRing(upper, fascia, { segments: 200 }), ribbonMaterial));

    // Glass with a steel top rail at the front of each tier.
    [lower, upper].forEach((bowl) => {
        const offset = -bowl.row_depth / 2 - 0.06;
        const top = bowl.height + 0.98;
        group.add(new THREE.Mesh(sweepRing(bowl, [[offset, bowl.height - 0.02], [offset, top]], { segments: 200 }), materials.glass));
        group.add(new THREE.Mesh(sweepRing(bowl, [[offset - 0.04, top], [offset - 0.04, top + 0.06], [offset + 0.04, top + 0.06], [offset + 0.04, top]], { segments: 200 }), materials.steel));
    });

    // Masking at the ends of the seating, next to the stage.
    [-limit, limit].forEach((angle) => {
        [[lower, lowerProfile], [upper, upperProfile]].forEach(([bowl, profile]) => {
            const positions = [];
            const indices = [];

            profile.forEach(([offset, y], index) => {
                const [x, z] = superellipsePoint(angle, bowl.radius_x + offset, bowl.radius_z + offset, power);
                positions.push(x, y, z, x, bowl === lower ? 0 : upperStart[1], z);
                if (index > 0) {
                    const a = (index - 1) * 2;
                    indices.push(a, a + 1, a + 2, a + 2, a + 1, a + 3);
                }
            });

            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
            geometry.setIndex(indices);
            geometry.computeVertexNormals();
            group.add(new THREE.Mesh(geometry, materials.mask));
        });
    });

    return { band, upperEnd, bandTop };
}

/** Steps, lights and handrails in the gaps between neighbouring blocks. */
export function addAisles(group, layout, rows, seats, materials, { skip = () => false } = {}) {
    const byBlock = new Map();

    rows.forEach((indices, key) => {
        const [block, row] = key.split('|');
        if (!byBlock.has(block)) {
            byBlock.set(block, new Map());
        }
        byBlock.get(block).set(row, indices);
    });

    const treads = [];
    const nosing = [];
    const dots = [];
    const posts = [];
    const rails = [];

    const point = (i) => new THREE.Vector3(seats.x[i], seats.y[i], seats.z[i]);

    layout.bowls.filter((bowl) => !skip(bowl)).forEach((bowl) => {
        const last = bowl.first_block + bowl.blocks - 1;
        const closesTheCircle = (bowl.sweep_degrees ?? layout.sweep_degrees) >= 180;

        for (let number = bowl.first_block; number <= (closesTheCircle ? last : last - 1); number++) {
            const left = byBlock.get(String(number));
            const right = byBlock.get(String(number === last ? bowl.first_block : number + 1));

            if (!left || !right) {
                continue;
            }

            const line = [];

            left.forEach((seatsA, row) => {
                const seatsB = right.get(row);
                if (!seatsB) {
                    return;
                }

                let best = null;
                [seatsA[0], seatsA[seatsA.length - 1]].forEach((a) => {
                    [seatsB[0], seatsB[seatsB.length - 1]].forEach((b) => {
                        const gap = point(a).distanceTo(point(b));
                        if (!best || gap < best.gap) {
                            best = { gap, a, b };
                        }
                    });
                });

                line.push({
                    position: point(best.a).add(point(best.b)).multiplyScalar(0.5),
                    rotation: seats.rotation[best.a] + Math.atan2(
                        Math.sin(seats.rotation[best.b] - seats.rotation[best.a]),
                        Math.cos(seats.rotation[best.b] - seats.rotation[best.a])
                    ) / 2,
                    width: Math.max(best.gap - 0.56, 0.5),
                });
            });

            line.sort((a, b) => a.position.y - b.position.y);

            line.forEach((step, index) => {
                const { position, rotation, width } = step;
                const next = line[index + 1];

                nosing.push(placed(position.x, position.y + 0.005, position.z, rotation, width, 1, 1));

                if (next) {
                    const middle = new THREE.Vector3().addVectors(position, next.position).multiplyScalar(0.5);
                    const rise = next.position.y - position.y;
                    treads.push(placed(middle.x, position.y + rise / 4, middle.z, rotation, width, rise / 2, 0.42));
                    nosing.push(placed(middle.x, position.y + rise / 2 + 0.005, middle.z, rotation, width, 1, 1));

                    const railFrom = position.clone().setY(position.y + 0.92);
                    rails.push(strut(railFrom, next.position.clone().setY(next.position.y + 0.92), 1));
                }

                if (index % 3 === 0) {
                    posts.push(strut(position, position.clone().setY(position.y + 0.92), 1));
                }

                const side = new THREE.Vector3(Math.cos(rotation), 0, -Math.sin(rotation)).multiplyScalar(width / 2 - 0.03);
                [1, -1].forEach((sign) => {
                    const dot = position.clone().addScaledVector(side, sign);
                    dots.push(placed(dot.x, dot.y + 0.1, dot.z));
                });
            });
        }
    });

    const treadGeometry = new THREE.BoxGeometry(1, 1, 1);
    const nosingGeometry = new THREE.BoxGeometry(1, 0.014, 0.05);
    nosingGeometry.translate(0, 0, 0.19);
    const dotGeometry = new THREE.SphereGeometry(0.028, 6, 4);
    const tubeGeometry = new THREE.CylinderGeometry(0.022, 0.022, 1, 6);

    group.add(instanced(treadGeometry, materials.walkway, treads));
    group.add(instanced(nosingGeometry, materials.nosing, nosing));
    group.add(instanced(dotGeometry, materials.stepLight, dots));
    group.add(instanced(tubeGeometry, materials.steel, [...posts, ...rails]));
}

function addFloor(group, layout, blocks, seats, materials, parts) {
    const power = layout.corner_power;
    const shape = new THREE.Shape();

    for (let i = 0; i <= 128; i++) {
        const [x, z] = superellipsePoint((i / 128) * TAU, 74, 60, power);
        i === 0 ? shape.moveTo(x, z) : shape.lineTo(x, z);
    }

    const floor = new THREE.Mesh(new THREE.ShapeGeometry(shape).rotateX(-Math.PI / 2), materials.floor);
    floor.position.y = -0.02;
    group.add(floor);

    blocks.forEach((indices) => {
        if (Number.isFinite(Number(seats.block[indices[0]]))) {
            return;
        }

        const xs = indices.map((i) => seats.x[i]);
        const zs = indices.map((i) => seats.z[i]);
        const minX = Math.min(...xs) - 0.6;
        const maxX = Math.max(...xs) + 0.6;
        const minZ = Math.min(...zs) - 0.5;
        const maxZ = Math.max(...zs) + 0.5;

        parts.box('carpet', maxX - minX, 0.03, maxZ - minZ, (minX + maxX) / 2, 0.015, (minZ + maxZ) / 2);
    });

    // Crowd barrier in front of the stage.
    for (let z = -13; z <= 13; z += 2) {
        parts.box('steel', 0.06, 1.1, 1.9, -27.1, 0.75, z);
        parts.box('steel', 0.9, 0.05, 1.9, -26.75, 0.03, z);
        parts.box('darkMetal', 0.7, 0.05, 0.05, -26.8, 0.5, z - 0.8);
        parts.box('darkMetal', 0.7, 0.05, 0.05, -26.8, 0.5, z + 0.8);
    }

    // Front of house: the mix position at the back of the floor.
    const mix = { x: 28, z: 0 };
    parts.box('dark', 6.5, 0.4, 5.5, mix.x, 1.4, mix.z);
    [[-3, -2.5], [3, -2.5], [-3, 2.5], [3, 2.5]].forEach(([dx, dz]) => {
        parts.drum('steel', 0.09, 5, mix.x + dx, 2.5, mix.z + dz);
    });
    parts.box('dark', 6.9, 0.25, 5.9, mix.x, 5, mix.z);
    parts.box('darkMetal', 2.6, 0.9, 1, mix.x - 1.2, 2, mix.z - 0.6);
    parts.box('darkMetal', 2.6, 0.9, 1, mix.x - 1.2, 2, mix.z + 1);
    parts.box('screen', 0.05, 0.5, 0.9, mix.x - 2.5, 2.85, mix.z - 0.6);
    parts.box('screen', 0.05, 0.5, 0.9, mix.x - 2.5, 2.85, mix.z + 1);
}

function addCeiling(group, layout, upperEnd, materials) {
    const power = layout.corner_power;
    const [, upper] = layout.bowls;
    const wallRadius = upperEnd[0];
    const scaled = (scale) => [(upper.radius_x + wallRadius) * scale, (upper.radius_z + wallRadius) * scale];
    const domeY = (scale) => CEILING_HEIGHT + DOME_RISE * (1 - Math.pow(scale, 2.3));

    group.add(new THREE.Mesh(ring({
        profile: [[1, upperEnd[1]], [1, CEILING_HEIGHT]],
        radii: scaled,
        power,
        repeatU: 60,
    }), materials.wall));

    const domeProfile = [];
    for (let i = 0; i <= 16; i++) {
        const scale = 1 - i / 16;
        domeProfile.push([Math.max(scale, 0.002), domeY(scale)]);
    }
    group.add(new THREE.Mesh(ring({ profile: domeProfile, radii: scaled, power, segments: 144, repeatU: 1, smooth: true }), materials.ceiling));

    const ribs = [];
    const ribCount = 36;

    for (let i = 0; i < ribCount; i++) {
        const angle = (i / ribCount) * TAU;
        let previous = null;

        for (let step = 0; step <= 8; step++) {
            const scale = 1 - step * 0.09;
            const [radiusX, radiusZ] = scaled(scale);
            const [x, z] = superellipsePoint(angle, radiusX, radiusZ, power);
            const current = new THREE.Vector3(x, domeY(scale) - 0.5, z);

            if (previous) {
                ribs.push(strut(previous, current, 1));
            }
            previous = current;
        }
    }
    group.add(instanced(new THREE.BoxGeometry(0.3, 1, 0.5), materials.darkMetal, ribs));

    [0.68, 0.42].forEach((scale) => {
        const y = domeY(scale) - 2.2;
        const profile = [[scale + 0.012, y], [scale - 0.012, y]];
        group.add(new THREE.Mesh(ring({ profile, radii: scaled, power, segments: 120 }), materials.steelBoth));
        group.add(new THREE.Mesh(ring({ profile: [[scale + 0.012, y + 0.5], [scale + 0.012, y + 0.54]], radii: scaled, power, segments: 120 }), materials.steelBoth));
    });

    return { scaled, domeY, power };
}

/** Two long walkways hung from the roof, with the screen between them. */
function addBridges(group, ceiling, uniforms, parts, materials) {
    const BRIDGE = { length: 62, width: 3.4, y: 24, z: 12.5, centreX: 2 };
    const hangers = [];

    [-1, 1].forEach((side) => {
        const z = BRIDGE.z * side;

        parts.box('darkMetal', BRIDGE.length, 0.55, BRIDGE.width, BRIDGE.centreX, BRIDGE.y, z);
        parts.box('steel', BRIDGE.length, 0.05, 0.05, BRIDGE.centreX, BRIDGE.y + 1.25, z + BRIDGE.width / 2);
        parts.box('steel', BRIDGE.length, 0.05, 0.05, BRIDGE.centreX, BRIDGE.y + 1.25, z - BRIDGE.width / 2);
        parts.box('glass', BRIDGE.length, 1, 0.03, BRIDGE.centreX, BRIDGE.y + 0.75, z + BRIDGE.width / 2);
        parts.box('glass', BRIDGE.length, 1, 0.03, BRIDGE.centreX, BRIDGE.y + 0.75, z - BRIDGE.width / 2);
        parts.box('underLight', BRIDGE.length - 1, 0.04, 0.18, BRIDGE.centreX, BRIDGE.y - 0.3, z);

        for (let x = BRIDGE.centreX - BRIDGE.length / 2 + 1; x <= BRIDGE.centreX + BRIDGE.length / 2 - 1; x += 3.6) {
            const scale = Math.hypot(x / ceiling.scaled(1)[0], z / ceiling.scaled(1)[1]);
            const top = ceiling.domeY(scale);

            [-1, 1].forEach((edge) => {
                hangers.push(strut(
                    new THREE.Vector3(x, BRIDGE.y + 0.2, z + (edge * BRIDGE.width) / 2),
                    new THREE.Vector3(x, top, z + edge * (BRIDGE.width / 2 + 0.5)),
                    1
                ));
            });
        }
    });

    group.add(instanced(new THREE.CylinderGeometry(0.035, 0.035, 1, 6), materials.steel, hangers));

    // Round centre-hung screen.
    const SCREEN = { radius: 8.6, height: 4.4, y: 20.4, x: 2 };
    const screen = new THREE.Mesh(
        new THREE.CylinderGeometry(SCREEN.radius, SCREEN.radius, SCREEN.height, 72, 1, true),
        videoMaterial(uniforms, { grid: [720, 90], repeatU: 3 })
    );
    screen.position.set(SCREEN.x, SCREEN.y, 0);
    group.add(screen);

    parts.drum('darkMetal', SCREEN.radius + 0.25, 0.5, SCREEN.x, SCREEN.y + SCREEN.height / 2 + 0.25, 0);
    parts.drum('darkMetal', SCREEN.radius + 0.25, 0.3, SCREEN.x, SCREEN.y - SCREEN.height / 2 - 0.15, 0);
    parts.drum('underLight', 0.5, 0.04, SCREEN.x, SCREEN.y - SCREEN.height / 2 - 0.32, 0);

    for (let i = 0; i < 8; i++) {
        const angle = (i / 8) * TAU;
        const x = SCREEN.x + Math.cos(angle) * 5;
        const z = Math.sin(angle) * 5;
        parts.drum('steel', 0.05, 11, x, SCREEN.y + SCREEN.height / 2 + 5.8, z);
    }
}

/* Stage */

export function videoMaterial(uniforms, { grid, repeatU = 1, crop = 0.66, boost = 1.1 }) {
    return new THREE.ShaderMaterial({
        uniforms: {
            ...uniforms,
            uGrid: { value: new THREE.Vector2(grid[0], grid[1]) },
            uRepeat: { value: repeatU },
            uCrop: { value: crop },
            uBoost: { value: boost },
        },
        side: THREE.DoubleSide,
        vertexShader: 'varying vec2 vUv; void main() { vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }',
        fragmentShader: `
            varying vec2 vUv;
            uniform float uTime;
            uniform float uHasVideo;
            uniform float uRepeat;
            uniform float uCrop;
            uniform float uBoost;
            uniform float uBeat;
            uniform vec2 uGrid;
            uniform sampler2D uVideo;
            void main() {
                float bands = sin(vUv.x * 14.0 + uTime * 1.3) * 0.5 + 0.5;
                float sweep = smoothstep(0.2, 0.8, sin(vUv.y * 6.0 + uTime * 0.8 + vUv.x * 5.0) * 0.5 + 0.5);
                vec3 colour = mix(vec3(0.12, 0.05, 0.5), vec3(0.8, 0.06, 0.32), bands * sweep);
                if (uHasVideo > 0.5) {
                    vec2 uv = vec2(fract(vUv.x * uRepeat), (vUv.y - 0.5) * uCrop + 0.5);
                    colour = pow(texture2D(uVideo, uv).rgb, vec3(2.2));
                }
                // Individual LEDs up close, smoothed away with distance.
                vec2 cells = vUv * uGrid;
                float spread = fwidth(cells.x) + fwidth(cells.y);
                float led = smoothstep(0.62, 0.3, length(fract(cells) - 0.5));
                float mask = mix(0.25 + led * 1.2, 1.0, smoothstep(0.35, 1.1, spread));
                gl_FragColor = vec4(colour * mask * uBoost * (1.0 + uBeat * 0.3), 1.0);
            }`,
    });
}

/** One two-metre section of box truss, built along +x. */
export function trussModule() {
    const size = 0.52;
    const half = size / 2;
    const pieces = [];
    const bar = (length, thickness) => new THREE.BoxGeometry(length, thickness, thickness);

    [[half, half], [half, -half], [-half, half], [-half, -half]].forEach(([y, z]) => {
        pieces.push(bar(2, 0.07).translate(0, y, z));
    });

    const brace = Math.hypot(2 / 2, size);
    const angle = Math.atan2(size, 1);

    [-1, 1].forEach((side) => {
        [[-0.5, 1], [0.5, -1]].forEach(([x, sign]) => {
            pieces.push(bar(brace, 0.035).rotateZ(angle * sign).translate(x, 0, side * half));

            const flat = bar(brace, 0.035);
            flat.rotateY(angle * sign);
            pieces.push(flat.translate(x, side * half, 0));
        });
    });

    return mergeGeometries(pieces);
}

function addRig(group, parts, materials) {
    const RIG = { top: 15.4, front: -30, back: -38, reach: 17 };
    const sections = [];

    for (let z = -RIG.reach; z <= RIG.reach; z += 2) {
        const yaw = Math.PI / 2;
        sections.push(placed(RIG.front, RIG.top, z, yaw), placed(RIG.back, RIG.top, z, yaw));
    }
    for (let x = RIG.back + 1; x < RIG.front; x += 2) {
        sections.push(placed(x, RIG.top, RIG.reach), placed(x, RIG.top, -RIG.reach));
    }

    // Towers carrying the roof down to the deck.
    [[RIG.front, RIG.reach], [RIG.front, -RIG.reach], [RIG.back, RIG.reach], [RIG.back, -RIG.reach]].forEach(([x, z]) => {
        for (let y = 1; y < RIG.top; y += 2) {
            sections.push(new THREE.Matrix4().compose(
                new THREE.Vector3(x, y, z),
                new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 0, 1), Math.PI / 2),
                new THREE.Vector3(1, 1, 1)
            ));
        }
    });

    group.add(instanced(trussModule(), materials.steel, sections));

    // Line-array speakers hanging in a J curve either side of the stage.
    const cabinets = [];
    [-1, 1].forEach((side) => {
        let y = 14.6;
        let tilt = 0;

        for (let i = 0; i < 12; i++) {
            const tiltQuat = new THREE.Quaternion().setFromAxisAngle(new THREE.Vector3(0, 0, 1), -tilt);
            const spin = new THREE.Quaternion().setFromAxisAngle(UP, side * 0.25);
            cabinets.push(new THREE.Matrix4().compose(new THREE.Vector3(-31.5 + i * 0.03, y, side * 20), spin.multiply(tiltQuat), new THREE.Vector3(1, 1, 1)));
            y -= 0.5;
            tilt += 0.016 * (i + 1);
        }

        parts.drum('steel', 0.03, 8, -31.5, 18.5, side * 20);
    });

    const cabinet = new THREE.BoxGeometry(0.8, 0.46, 1.3);
    group.add(instanced(cabinet, materials.speaker, cabinets));

    return RIG;
}

export function addDrumKit(parts, x, y, z) {
    parts.drum('drumShell', 0.34, 0.45, x + 0.4, y + 0.34, z, 'x');
    parts.drum('metal', 0.34, 0.01, x + 0.64, y + 0.34, z, 'x');
    [[0.25, 0.55, 0.36, 0.2], [0.25, 0.55, -0.36, 0.2], [-0.05, 0.42, 0.72, 0.22]].forEach(([dx, dy, dz, radius]) => {
        parts.drum('drumShell', radius, 0.25, x + dx, y + dy, z + dz);
    });
    parts.drum('drumShell', 0.2, 0.14, x + 0.58, y + 0.5, z + 0.52);
    [[0.3, 0.95, 0.75, 0.25], [0.3, 0.95, -0.72, 0.25], [0.6, 0.78, 0.85, 0.17]].forEach(([dx, dy, dz, radius]) => {
        parts.drum('brass', radius, 0.008, x + dx, y + dy, z + dz);
        parts.drum('steel', 0.012, dy, x + dx, y + dy / 2, z + dz);
    });
    parts.drum('dark', 0.16, 0.45, x - 0.5, y + 0.22, z);
}

function addStage(group, uniforms, parts, stageName, materials) {
    const DECK = { x: -35, width: 12, depth: 27, height: 1.7 };

    parts.box('deck', DECK.width, 0.12, DECK.depth, DECK.x, DECK.height - 0.06, 0);
    parts.box('mask', DECK.width, DECK.height - 0.12, DECK.depth, DECK.x, (DECK.height - 0.12) / 2, 0);

    // Scrolling name along the front edge of the stage.
    const name = ledBand(stageName);
    name.repeat.set(1.4, 1);
    const front = new THREE.Mesh(
        new THREE.PlaneGeometry(DECK.depth, 1.05),
        new THREE.MeshBasicMaterial({ map: name, color: new THREE.Color(1.3, 1.3, 1.3) })
    );
    front.rotation.y = Math.PI / 2;
    front.position.set(STAGE_FRONT_X + 0.02, 0.72, 0);
    group.add(front);

    // Curved LED wall, slightly wrapping around the audience.
    const WALL = { radius: 38, arc: 0.43, height: 11.5, bottom: 2.3 };
    const centreX = -41.6 + WALL.radius;
    const wallPositions = [];
    const wallUvs = [];
    const wallIndices = [];
    const columns = 48;

    for (let i = 0; i <= columns; i++) {
        const angle = -WALL.arc + (2 * WALL.arc * i) / columns;
        const x = centreX - Math.cos(angle) * WALL.radius;
        const z = -Math.sin(angle) * WALL.radius;
        wallPositions.push(x, WALL.bottom, z, x, WALL.bottom + WALL.height, z);
        wallUvs.push(i / columns, 0, i / columns, 1);
        if (i < columns) {
            const a = i * 2;
            wallIndices.push(a, a + 2, a + 1, a + 2, a + 3, a + 1);
        }
    }

    const wallGeometry = new THREE.BufferGeometry();
    wallGeometry.setAttribute('position', new THREE.Float32BufferAttribute(wallPositions, 3));
    wallGeometry.setAttribute('uv', new THREE.Float32BufferAttribute(wallUvs, 2));
    wallGeometry.setIndex(wallIndices);
    group.add(new THREE.Mesh(wallGeometry, videoMaterial(uniforms, { grid: [620, 230] })));

    // Frame and seams of the LED cabinets.
    for (let i = 0; i <= 12; i++) {
        const angle = -WALL.arc + (2 * WALL.arc * i) / 12;
        const x = centreX - Math.cos(angle) * (WALL.radius + 0.06);
        const z = -Math.sin(angle) * (WALL.radius + 0.06);
        parts.box('darkMetal', 0.12, WALL.height + 0.3, 0.1, x, WALL.bottom + WALL.height / 2, z);
    }
    parts.box('darkMetal', 0.5, 0.25, 33, -42, WALL.bottom + WALL.height + 0.1, 0);
    parts.box('darkMetal', 0.5, 0.25, 33, -42, WALL.bottom - 0.1, 0);

    // Side screens flown over the corners, for the seats furthest back.
    [-1, 1].forEach((side) => {
        const screen = new THREE.Mesh(new THREE.PlaneGeometry(8.4, 4.7), videoMaterial(uniforms, { grid: [260, 145], crop: 1 }));
        screen.position.set(-37, 19, side * 25);
        screen.rotation.y = Math.PI / 2 + side * 0.62;
        group.add(screen);

        parts.drum('steel', 0.03, 9, -37, 26, side * 25);
    });

    // Risers.
    [[-38.6, 3, 1.1, 18], [-37.3, 2.4, 0.7, 13]].forEach(([x, width, height, depth]) => {
        parts.box('riser', width, height, depth, x, DECK.height + height / 2, 0);
        parts.box('riserEdge', 0.05, 0.05, depth, x + width / 2, DECK.height + height, 0);
    });

    addDrumKit(parts, -37.3, DECK.height + 0.7, 0);

    // Keys, backline and monitors.
    [-1, 1].forEach((side) => {
        const z = side * 6.4;
        parts.box('dark', 0.34, 0.06, 1.3, -37.3, DECK.height + 2, z);
        parts.box('steel', 0.04, 0.9, 0.04, -37.3, DECK.height + 1.55, z - 0.5);
        parts.box('steel', 0.04, 0.9, 0.04, -37.3, DECK.height + 1.55, z + 0.5);

        for (let i = 0; i < 2; i++) {
            const stack = side * (9.8 + i * 1);
            parts.box('speaker', 0.45, 0.95, 0.85, -36.2, DECK.height + 0.48, stack);
            parts.box('speaker', 0.4, 0.55, 0.85, -36.2, DECK.height + 1.23, stack);
        }

        // Stairs at each side of the deck.
        for (let step = 0; step < 6; step++) {
            parts.box('darkMetal', 0.3, 0.28 * (6 - step), 1.4, DECK.x + DECK.width / 2 - 1 - step * 0.3, (0.28 * (6 - step)) / 2, side * (DECK.depth / 2 + 0.75));
        }
    });

    for (let z = -11; z <= 11; z += 4.4) {
        const wedge = new THREE.BoxGeometry(0.42, 0.34, 0.7);
        wedge.rotateZ(0.5);
        parts.add('speaker', wedge.translate(-30, DECK.height + 0.2, z));
    }

    [-5, 0, 5].forEach((z) => {
        parts.drum('steel', 0.015, 1.4, -31, DECK.height + 0.7, z);
        parts.drum('steel', 0.13, 0.02, -31, DECK.height + 0.01, z);
        parts.drum('dark', 0.03, 0.14, -30.95, DECK.height + 1.45, z);
    });

    // Bass cabinets stacked under the front of the stage.
    [-1, 1].forEach((side) => {
        for (let i = 0; i < 4; i++) {
            parts.box('speaker', 1, 0.8, 1.1, -28.1, 0.42 + (i % 2) * 0.84, side * (14.8 + Math.floor(i / 2) * 1.14));
        }
    });

}

/** The pleated curtain that closes off the stage end of the arena. */
function addDrape(group, layout, materials) {
    const { corner_power: power, sweep_degrees: sweep } = layout;
    const from = THREE.MathUtils.degToRad(sweep + 3);
    const lower = layout.bowls[0];
    const extra = 17.5;

    group.add(new THREE.Mesh(ring({
        profile: [[extra, 0], [extra, CEILING_HEIGHT + 2]],
        radii: (offset) => [lower.radius_x + offset, lower.radius_z + offset],
        power,
        from,
        to: TAU - from,
        segments: 120,
        repeatU: 70,
    }), materials.drape));
}

/* Moving lights */

/**
 * Hangs a fixture at each { x, y, z }. `tilt` leans the beam towards -x before
 * `yaw` turns the fixture; `swing` and `sweep` set how far it moves in the show.
 * `upright` stands it on the ground, for searchlights.
 */
function addMovingLights(group, fixtures, materials, { length = 44, radius = 4.2 } = {}) {
    const coneGeometry = new THREE.ConeGeometry(radius, length, 28, 1, true);
    coneGeometry.translate(0, -length / 2, 0);

    const base = new THREE.BoxGeometry(0.46, 0.2, 0.46);
    base.translate(0, -0.38, 0);
    const yokeA = new THREE.BoxGeometry(0.05, 0.42, 0.34);
    yokeA.translate(0.2, -0.62, 0);
    const yokeB = new THREE.BoxGeometry(0.05, 0.42, 0.34);
    yokeB.translate(-0.2, -0.62, 0);
    const mount = mergeGeometries([base, yokeA, yokeB]);

    const headGeometry = new THREE.CylinderGeometry(0.17, 0.13, 0.42, 16);
    headGeometry.translate(0, -0.08, 0);
    const lensGeometry = new THREE.CircleGeometry(0.15, 16);
    lensGeometry.rotateX(Math.PI / 2);
    lensGeometry.translate(0, -0.3, 0);

    const lights = [];

    fixtures.forEach(({ x, y, z, yaw = 0, tilt = -0.5, swing = 0.42, sweep = 0.32, upright = false }, index) => {
        const fixture = new THREE.Group();
        fixture.position.set(x, y, z);
        fixture.rotation.y = yaw;
        if (upright) {
            fixture.rotation.x = Math.PI;
        }
        fixture.add(new THREE.Mesh(mount, materials.darkMetal));

        const head = new THREE.Group();
        head.position.y = -0.7;
        head.add(new THREE.Mesh(headGeometry, materials.darkMetal));

        const lensMaterial = new THREE.MeshBasicMaterial({ color: '#ffffff' });
        head.add(new THREE.Mesh(lensGeometry, lensMaterial));

        const beam = new THREE.Mesh(coneGeometry, new THREE.ShaderMaterial({
            uniforms: {
                uColor: { value: new THREE.Color('#ffffff') },
                uLength: { value: length },
                uStrength: { value: 0.2 },
            },
            transparent: true,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
            side: THREE.DoubleSide,
            fog: false,
            vertexShader: `
                uniform float uLength;
                varying float vDrop;
                varying vec3 vNormalView;
                varying vec3 vToCamera;
                void main() {
                    vDrop = -position.y / uLength;
                    vec4 view = modelViewMatrix * vec4(position, 1.0);
                    vNormalView = normalize(normalMatrix * normal);
                    vToCamera = normalize(-view.xyz);
                    gl_Position = projectionMatrix * view;
                }`,
            fragmentShader: `
                uniform vec3 uColor;
                uniform float uStrength;
                varying float vDrop;
                varying vec3 vNormalView;
                varying vec3 vToCamera;
                void main() {
                    float fade = pow(1.0 - vDrop, 2.0);
                    float soft = pow(abs(dot(vNormalView, vToCamera)), 2.2);
                    gl_FragColor = vec4(uColor, fade * soft * uStrength);
                }`,
        }));
        head.add(beam);
        fixture.add(head);
        group.add(fixture);

        lights.push({ head, beam, lens: lensMaterial, offset: index * 0.9, tilt, swing, sweep });
    });

    return lights;
}

export function softDotTexture() {
    return canvasTexture(128, 128, (context, width) => {
        const gradient = context.createRadialGradient(width / 2, width / 2, 0, width / 2, width / 2, width / 2);
        gradient.addColorStop(0, 'rgba(255, 255, 255, 1)');
        gradient.addColorStop(0.5, 'rgba(255, 255, 255, .28)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
        context.fillStyle = gradient;
        context.fillRect(0, 0, width, width);
    });
}

function addHaze(group, { x, z, width, depth }) {
    const texture = softDotTexture();
    const puffs = [];

    for (let i = 0; i < 12; i++) {
        const sprite = new THREE.Sprite(new THREE.SpriteMaterial({
            map: texture,
            transparent: true,
            depthWrite: false,
            blending: THREE.AdditiveBlending,
            opacity: 0.05,
            fog: false,
        }));
        const size = 16 + Math.random() * 16;
        sprite.scale.set(size, size * 0.6, 1);
        sprite.position.set(x + (Math.random() - 0.5) * width, 3 + Math.random() * 9, z + (Math.random() - 0.5) * depth);
        group.add(sprite);
        puffs.push({ sprite, phase: Math.random() * TAU, z: sprite.position.z });
    }

    const count = 500;
    const positions = new Float32Array(count * 3);

    for (let i = 0; i < count; i++) {
        positions[i * 3] = x + (Math.random() - 0.5) * width * 2.5;
        positions[i * 3 + 1] = 1 + Math.random() * 16;
        positions[i * 3 + 2] = z + (Math.random() - 0.5) * depth * 1.6;
    }

    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const dust = new THREE.Points(geometry, new THREE.PointsMaterial({
        map: texture,
        size: 0.09,
        transparent: true,
        opacity: 0.25,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
    }));
    group.add(dust);

    return { puffs, dust };
}

/* Audience */

function personGeometries() {
    const body = new THREE.CylinderGeometry(0.13, 0.19, 0.56, 7);
    body.translate(0, 0.74, -0.04);
    const head = new THREE.SphereGeometry(0.095, 8, 6);
    head.translate(0, 1.13, -0.02);

    const armLeft = new THREE.CylinderGeometry(0.024, 0.036, 0.46, 5);
    armLeft.rotateZ(0.2);
    armLeft.translate(-0.22, 1.2, 0);
    const armRight = armLeft.clone();
    armRight.rotateY(Math.PI);

    return {
        seated: mergeGeometries([body, head]),
        cheering: mergeGeometries([body, head, armLeft, armRight]),
    };
}

function addAudience(group, seats) {
    const count = seats.id.length;
    const { seated, cheering } = personGeometries();
    const material = new THREE.MeshStandardMaterial({ color: '#ffffff', roughness: 0.9 });
    const crowdUniforms = {
        uCrowdTime: { value: 0 },
        uBounce: { value: 0 },
        uWaveAt: { value: 0 },
        uWaveOn: { value: 0 },
        uCrowdCentre: { value: new THREE.Vector2() },
    };

    // People move in the shader, so a whole stadium can jump for the cost of one uniform.
    material.onBeforeCompile = (shader) => {
        Object.assign(shader.uniforms, crowdUniforms);
        shader.vertexShader = `
            uniform float uCrowdTime;
            uniform float uBounce;
            uniform float uWaveAt;
            uniform float uWaveOn;
            uniform vec2 uCrowdCentre;
            ${shader.vertexShader}`.replace('#include <begin_vertex>', `
            #include <begin_vertex>
            vec3 crowdAt = instanceMatrix[3].xyz;
            float crowdSeed = fract(sin(dot(crowdAt.xz, vec2(12.9898, 78.233))) * 43758.5453);
            float crowdHop = abs(sin(uCrowdTime * (3.4 + crowdSeed * 0.8) + crowdSeed * 6.28));
            float crowdAngle = atan(crowdAt.z - uCrowdCentre.y, crowdAt.x - uCrowdCentre.x);
            float crowdGap = mod(uWaveAt - crowdAngle, 6.2832);
            float crowdRise = smoothstep(0.0, 0.22, crowdGap) * smoothstep(0.95, 0.22, crowdGap) * uWaveOn;
            transformed.y *= 1.0 + crowdRise * 0.28;
            transformed.y += crowdHop * uBounce * (0.08 + crowdSeed * 0.12) + crowdRise * 0.42;`);
    };

    const people = [seated, cheering].map((geometry) => {
        const mesh = new THREE.InstancedMesh(geometry, material, count);
        mesh.count = 0;
        mesh.frustumCulled = false;
        mesh.visible = false;
        group.add(mesh);
        return mesh;
    });

    const phoneTexture = softDotTexture();
    const phonePositions = new Float32Array(count * 3);
    const phoneGeometry = new THREE.BufferGeometry();
    phoneGeometry.setAttribute('position', new THREE.BufferAttribute(phonePositions, 3));
    phoneGeometry.setAttribute('aPhase', new THREE.BufferAttribute(new Float32Array(count).map(() => Math.random() * TAU), 1));
    const phoneRare = new Float32Array(count);
    phoneGeometry.setAttribute('aRare', new THREE.BufferAttribute(phoneRare, 1));
    phoneGeometry.setDrawRange(0, 0);

    const phoneUniforms = {
        uTime: { value: 0 },
        uMap: { value: phoneTexture },
        uScale: { value: 300 },
        uPhones: { value: 0 },
    };
    const phones = new THREE.Points(phoneGeometry, new THREE.ShaderMaterial({
        uniforms: phoneUniforms,
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending,
        vertexShader: `
            attribute float aPhase;
            attribute float aRare;
            uniform float uTime;
            uniform float uScale;
            uniform float uPhones;
            varying float vGlow;
            void main() {
                float lit = max(aRare, uPhones);
                float idle = 0.35 + 0.65 * smoothstep(0.1, 1.0, sin(uTime * 1.2 + aPhase * 6.0) * 0.5 + 0.5);
                vGlow = mix(idle, 0.9, uPhones) * lit;
                vec3 place = position;
                place.x += sin(uTime * 0.9 + aPhase * 6.0) * 0.18 * uPhones;
                place.z += cos(uTime * 0.7 + aPhase * 4.0) * 0.18 * uPhones;
                vec4 view = modelViewMatrix * vec4(place, 1.0);
                gl_PointSize = max(uScale * (0.14 + uPhones * 0.12) / -view.z, 1.5) * lit;
                gl_Position = projectionMatrix * view;
            }`,
        fragmentShader: `
            uniform sampler2D uMap;
            varying float vGlow;
            void main() {
                float dot = texture2D(uMap, gl_PointCoord).a;
                gl_FragColor = vec4(vec3(0.9, 0.95, 1.0) * vGlow * 2.0, dot * vGlow);
            }`,
    }));
    phones.frustumCulled = false;
    phones.visible = false;
    group.add(phones);

    const tones = ['#20242e', '#2c2226', '#23282a', '#2f2a26', '#1c2030', '#3a2c2c'].map((hex) => new THREE.Color(hex));

    function setPeople(soldIndices) {
        const matrix = new THREE.Matrix4();
        const counts = [0, 0];
        let phoneCount = 0;

        soldIndices.forEach((index) => {
            // Stable per seat, so people do not swap places on every poll.
            const hash = (index * 2654435761) >>> 0;
            const variant = hash % 11 === 0 ? 1 : 0;
            const mesh = people[variant];
            const slot = counts[variant]++;

            matrix.compose(
                new THREE.Vector3(seats.x[index], seats.y[index], seats.z[index]),
                new THREE.Quaternion().setFromAxisAngle(UP, seats.rotation[index] + ((hash % 40) - 20) / 100),
                new THREE.Vector3(1, 0.94 + (hash % 13) / 100, 1)
            );
            mesh.setMatrixAt(slot, matrix);
            mesh.setColorAt(slot, tones[hash % tones.length]);

            // One in nine is filming already. One in three joins in when the lights go down.
            if (hash % 3 === 0) {
                phonePositions.set([seats.x[index], seats.y[index] + 1.3, seats.z[index]], phoneCount * 3);
                phoneRare[phoneCount] = hash % 9 === 0 ? 1 : 0;
                phoneCount++;
            }
        });

        people.forEach((mesh, variant) => {
            mesh.count = counts[variant];
            mesh.instanceMatrix.needsUpdate = true;
            if (mesh.instanceColor) {
                mesh.instanceColor.needsUpdate = true;
            }
        });

        phoneGeometry.attributes.position.needsUpdate = true;
        phoneGeometry.attributes.aRare.needsUpdate = true;
        phoneGeometry.setDrawRange(0, phoneCount);
    }

    return { people, phones, phoneUniforms, crowdUniforms, setPeople };
}

/* Confetti: thrown up by cannons, slowed by the air, then fluttering down. */

function addConfetti(group, { x, z, width, depth, top }) {
    const count = 1100;
    const seeds = new Float32Array(count * 4).map(() => Math.random());
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.BufferAttribute(new Float32Array(count * 3), 3));
    geometry.setAttribute('aSeed', new THREE.BufferAttribute(seeds, 4));

    const uniforms = {
        uAge: { value: 0 },
        uArea: { value: new THREE.Vector4(x, z, width / 2, depth / 2) },
        uTop: { value: top },
        uTint: { value: new THREE.Color('#ffffff') },
    };

    const points = new THREE.Points(geometry, new THREE.ShaderMaterial({
        uniforms,
        vertexShader: `
            attribute vec4 aSeed;
            uniform float uAge;
            uniform vec4 uArea;
            uniform float uTop;
            varying vec4 vSeed;
            varying float vFlip;
            void main() {
                float age = max(uAge - aSeed.w * 1.1, 0.0);
                float climb = (1.0 - exp(-age * 2.4)) * uTop * (0.55 + aSeed.y * 0.55);
                float fall = max(age - 1.1, 0.0) * (1.5 + aSeed.x * 1.3);
                vec3 place = vec3(
                    uArea.x + (aSeed.x - 0.5) * 2.0 * uArea.z + sin(age * 1.6 + aSeed.y * 20.0) * 0.9,
                    1.0 + climb - fall,
                    uArea.y + (aSeed.z - 0.5) * 2.0 * uArea.w + cos(age * 1.3 + aSeed.x * 20.0) * 0.9
                );
                vec4 view = modelViewMatrix * vec4(place, 1.0);
                vSeed = aSeed;
                vFlip = 0.25 + 0.75 * abs(sin(age * 5.0 + aSeed.z * 30.0));
                gl_PointSize = place.y < 0.0 || age <= 0.0 ? 0.0 : clamp(84.0 / -view.z, 1.6, 15.0);
                gl_Position = projectionMatrix * view;
            }`,
        fragmentShader: `
            uniform float uAge;
            uniform vec3 uTint;
            varying vec4 vSeed;
            varying float vFlip;
            void main() {
                float turn = vSeed.y * 6.28 + uAge;
                vec2 at = gl_PointCoord - 0.5;
                vec2 paper = vec2(at.x * cos(turn) - at.y * sin(turn), at.x * sin(turn) + at.y * cos(turn));
                if (abs(paper.x) > 0.48 * vFlip || abs(paper.y) > 0.26) {
                    discard;
                }
                vec3 colour = vSeed.w < 0.34 ? uTint : vSeed.w < 0.67 ? vec3(1.0, 0.82, 0.3) : vec3(1.0);
                gl_FragColor = vec4(colour * (0.8 + vFlip) * 1.5, 1.0);
            }`,
    }));
    points.frustumCulled = false;
    points.visible = false;
    group.add(points);

    return { points, uniforms };
}

/* Lighting */

function environmentMap(renderer) {
    const room = new THREE.Scene();
    room.background = new THREE.Color('#05050a');

    const glow = (colour, width, height, x, y, z) => {
        const panel = new THREE.Mesh(
            new THREE.PlaneGeometry(width, height),
            new THREE.MeshBasicMaterial({ color: colour, side: THREE.DoubleSide })
        );
        panel.position.set(x, y, z);
        panel.lookAt(0, 0, 0);
        room.add(panel);
    };

    glow(new THREE.Color(1.5, 1.4, 1.8), 8, 4, -10, 2, 0);
    glow(new THREE.Color(0.5, 0.5, 0.65), 12, 12, 0, 10, 0);
    glow(new THREE.Color(0.2, 0.2, 0.3), 6, 3, 10, 2, 0);

    const generator = new THREE.PMREMGenerator(renderer);
    const texture = generator.fromScene(room, 0.03).texture;
    generator.dispose();

    return texture;
}

function createStageVideo(url) {
    if (!url) {
        return null;
    }

    const video = document.createElement('video');
    video.crossOrigin = 'anonymous';
    video.src = url;
    video.muted = true;
    video.loop = true;
    video.playsInline = true;
    video.play().catch(() => {
        // Autoplay can be refused; the screens keep their animated pattern.
    });

    return new THREE.VideoTexture(video);
}

/** Reduces the video to one colour so the lights follow what is on screen. */
function videoSampler(texture) {
    const canvas = document.createElement('canvas');
    canvas.width = 8;
    canvas.height = 8;
    const context = canvas.getContext('2d', { willReadFrequently: true });

    return (colour) => {
        if (!texture || texture.image.readyState < 2) {
            return false;
        }

        try {
            context.drawImage(texture.image, 0, 0, 8, 8);
            const pixels = context.getImageData(0, 0, 8, 8).data;
            let red = 0;
            let green = 0;
            let blue = 0;

            for (let i = 0; i < pixels.length; i += 4) {
                red += pixels[i];
                green += pixels[i + 1];
                blue += pixels[i + 2];
            }

            colour.setRGB(red / 64 / 255, green / 64 / 255, blue / 64 / 255, THREE.SRGBColorSpace);
            return true;
        } catch (error) {
            return false;
        }
    };
}

/* The default venue */

/** The indoor arena. A venue gets the kit, builds into its groups and returns its lights and mood. */
function arenaVenue({ data, blocks, rows, uniforms, materials, standard, parts, roofParts, bowl, stage, roof, movingLights }) {
    const walls = wallPanels();
    const suites = suiteWindows();
    const ceilingMaps = ceilingTexture();

    materials.suites = standard({ map: suites.map, emissiveMap: suites.emissive, emissive: '#ffffff', emissiveIntensity: 1 });
    materials.wall = standard({ map: walls.map, emissiveMap: walls.emissive, emissive: '#ffffff', emissiveIntensity: 0.9, transparent: true });
    materials.ceiling = standard({ map: ceilingMaps.map, emissiveMap: ceilingMaps.emissive, emissive: '#ffffff', emissiveIntensity: 1, transparent: true });

    addFloor(bowl, data.layout, blocks, data.seats, materials, parts);
    const { band, upperEnd } = addBowl(bowl, data.layout, materials, data.stageName);
    addAisles(bowl, data.layout, rows, data.seats, materials);
    addDrape(stage, data.layout, materials);
    addStage(stage, uniforms, parts, data.stageName, materials);
    const rig = addRig(stage, parts, materials);

    const fixtures = [];
    for (let i = 0; i < 9; i++) {
        const z = -14 + i * 3.5;
        fixtures.push({ x: rig.front, y: rig.top - 0.2, z, tilt: -0.72 }, { x: rig.back, y: rig.top - 0.2, z, tilt: -0.42 });
    }

    const ceiling = addCeiling(roof, data.layout, upperEnd, materials);
    addBridges(roof, ceiling, uniforms, roofParts, materials);

    return { lights: movingLights(fixtures), bands: [band] };
}

const DEFAULT_MOOD = {
    house: [1.6, 0.34],
    overhead: [1.2, 0.12],
    beam: [0.13, 0.3],
    terrace: '#5c5c66',
    houseSeat: '#1a2140',
    skyLight: '#dfe4ff',
    groundLight: '#26222e',
    spill: [-27, 9, 0],
    spillPower: 700,
    spillReach: 80,
    stageGlow: [-60, 16, 0],
    roofFade: [34, 52],
    haze: { x: -23, z: 0, width: 16, depth: 28 },
    tint: null,
    background: null,
    fog: null,
};

/* Everything together */

export function buildArena({ scene, renderer, data, blocks, rows, reducedMotion, venue = arenaVenue }) {
    scene.environment = environmentMap(renderer);

    const concrete = concreteTexture();

    const standard = (options) => new THREE.MeshStandardMaterial({ roughness: 0.75, metalness: 0, ...options });

    const materials = {
        terrace: standard({ map: concrete, color: '#5c5c66' }),
        walkway: standard({ color: '#23232b', roughness: 0.9 }),
        floor: standard({ color: '#08080b', roughness: 0.72, envMapIntensity: 0.25 }),
        carpet: standard({ color: '#181a24', roughness: 0.95 }),
        dark: standard({ color: '#0b0b0f', roughness: 0.8 }),
        deck: standard({ color: '#141418', roughness: 0.45 }),
        mask: standard({ color: '#040406', roughness: 0.95, side: THREE.DoubleSide }),
        riser: standard({ color: '#101014', roughness: 0.6 }),
        riserEdge: new THREE.MeshBasicMaterial({ color: new THREE.Color(2, 2, 2.2) }),
        darkMetal: standard({ color: '#121216', roughness: 0.5, metalness: 0.7 }),
        steel: standard({ color: '#5d5f68', roughness: 0.45, metalness: 0.85 }),
        steelBoth: standard({ color: '#32333a', roughness: 0.5, metalness: 0.7, side: THREE.DoubleSide }),
        metal: standard({ color: '#c9c9cf', roughness: 0.3, metalness: 0.9 }),
        brass: standard({ color: '#b78f3c', roughness: 0.3, metalness: 1 }),
        drumShell: standard({ color: '#5a1220', roughness: 0.25, metalness: 0.2 }),
        speaker: standard({ color: '#08080a', roughness: 0.7 }),
        glass: standard({ color: '#c8dcea', roughness: 0.08, metalness: 0.2, transparent: true, opacity: 0.16, depthWrite: false, side: THREE.DoubleSide }),
        nosing: standard({ color: NOSING, roughness: 0.6 }),
        stepLight: new THREE.MeshBasicMaterial({ color: new THREE.Color(2.2, 1.7, 0.9) }),
        screen: new THREE.MeshBasicMaterial({ color: new THREE.Color(0.6, 0.8, 1.4) }),
        underLight: new THREE.MeshBasicMaterial({ color: new THREE.Color(1.4, 1.5, 2) }),
        drape: standard({
            roughness: 0.95,
            color: '#0a0a0d',
            map: canvasTexture(64, 8, (context, width, height) => {
                const pleat = context.createLinearGradient(0, 0, width, 0);
                pleat.addColorStop(0, '#111');
                pleat.addColorStop(0.5, '#6a6a6a');
                pleat.addColorStop(1, '#111');
                context.fillStyle = pleat;
                context.fillRect(0, 0, width, height);
            }),
        }),
    };

    const video = createStageVideo(data.stageVideo);
    const uniforms = {
        uTime: { value: 0 },
        uVideo: { value: video },
        uHasVideo: { value: 0 },
        uBeat: { value: 0 },
    };
    video?.image.addEventListener('playing', () => {
        uniforms.uHasVideo.value = 1;
    });

    const bowl = new THREE.Group();
    const stage = new THREE.Group();
    const roof = new THREE.Group();
    scene.add(bowl, stage, roof);

    const parts = new Parts(materials);
    const roofParts = new Parts(materials);

    const built = (venue ?? arenaVenue)({
        scene, data, blocks, rows, uniforms, materials, standard, parts, roofParts, bowl, stage, roof,
        movingLights: (fixtures, options, group = stage) => addMovingLights(group, fixtures, materials, options),
    });

    const { lights, bands = [], onFrame, onShow } = built;
    const mood = { ...DEFAULT_MOOD, ...built.mood };

    const haze = addHaze(stage, mood.haze);
    const audience = addAudience(bowl, data.seats);
    const confetti = addConfetti(stage, mood.confetti ?? { x: mood.haze.x + 14, z: mood.haze.z, width: 52, depth: 44, top: 16 });

    if (data.layout.centre) {
        audience.crowdUniforms.uCrowdCentre.value.set(data.layout.centre.x, data.layout.centre.z);
    }

    parts.build(stage);
    roofParts.build(roof);

    seatUniforms.uHouseColor.value.set(mood.houseSeat);
    if (mood.background) {
        scene.background = new THREE.Color(mood.background);
        scene.fog.color.set(mood.background);
    }
    if (mood.fog) {
        [scene.fog.near, scene.fog.far] = mood.fog;
    }

    /* Lights */

    const house = new THREE.HemisphereLight(mood.skyLight, mood.groundLight, mood.house[0]);
    const overhead = new THREE.DirectionalLight('#ffffff', mood.overhead[0]);
    overhead.position.set(10, 80, 20);
    const stageGlow = new THREE.DirectionalLight('#ffffff', 0.7);
    stageGlow.position.set(...mood.stageGlow);
    const spill = new THREE.PointLight('#ffffff', mood.spillPower, mood.spillReach, 2);
    spill.position.set(...mood.spill);
    scene.add(house, overhead, stageGlow, spill);

    /* Per-frame */

    // The roof fades on its own copies, so shared steel elsewhere stays solid.
    const shared = new Set(Object.values(materials));
    const copies = new Map();
    const fading = new Set();

    roof.traverse((child) => {
        if (!child.material) {
            return;
        }
        if (shared.has(child.material)) {
            if (!copies.has(child.material)) {
                copies.set(child.material, child.material.clone());
            }
            child.material = copies.get(child.material);
        }
        fading.add(child.material);
    });

    const baseOpacity = new Map([...fading].map((material) => [material, material.opacity]));
    const alwaysTransparent = new Set([...fading].filter((material) => material.transparent));

    const sampleVideo = videoSampler(video);
    const lightColour = new THREE.Color(FALLBACK_COLOURS[0]);
    const nextColour = new THREE.Color();
    const fallback = new THREE.Color();
    const tint = mood.tint && new THREE.Color(mood.tint.colour);
    const flash = lightColour.clone();
    const WHITE = new THREE.Color('#ffffff');
    const energy = { bass: 0, treble: 0, kick: 0, phones: 0 };
    let nextSampleAt = 0;
    let show = 0;
    let roofOpacity = -1;
    let lightClock = 0;
    let phones = 0;
    let phonesWanted = 0;
    let waveAge = Infinity;
    let confettiAge = Infinity;

    function setShow(value) {
        show = value;
        seatUniforms.uHouse.value = value;

        house.intensity = THREE.MathUtils.lerp(mood.house[0], mood.house[1], value);
        overhead.intensity = THREE.MathUtils.lerp(mood.overhead[0], mood.overhead[1], value);
        materials.terrace.color.set(mood.terrace).multiplyScalar(THREE.MathUtils.lerp(1, 0.55, value));

        audience.people.forEach((mesh) => {
            mesh.visible = value > 0.02;
        });
        audience.phones.visible = value > 0.5;

        lights.forEach((light) => {
            light.beam.material.uniforms.uStrength.value = THREE.MathUtils.lerp(mood.beam[0], mood.beam[1], value);
        });

        onShow?.(value);
    }

    function setRoof(height) {
        const opacity = 1 - THREE.MathUtils.smoothstep(height, mood.roofFade[0], mood.roofFade[1]);

        if (opacity === roofOpacity) {
            return;
        }

        roofOpacity = opacity;
        roof.visible = opacity > 0.01;

        fading.forEach((material) => {
            material.opacity = baseOpacity.get(material) * opacity;
            material.transparent = opacity < 1 || alwaysTransparent.has(material);
        });
    }

    function moveLights(time) {
        const strength = THREE.MathUtils.lerp(mood.beam[0], mood.beam[1], show) * (1 + energy.bass * 0.7) * (1 - phones * 0.8);

        lights.forEach(({ head, beam, lens, offset, tilt, swing, sweep }) => {
            head.rotation.x = Math.sin(time * 0.31 + offset * 1.7) * swing;
            head.rotation.z = tilt + Math.sin(time * 0.42 + offset) * sweep;
            beam.material.uniforms.uStrength.value = strength;
            beam.material.uniforms.uColor.value.copy(flash);
            lens.color.copy(flash).multiplyScalar(3 * (1 - phones * 0.7));
        });
    }

    function animateCrowd(time, delta) {
        const crowd = audience.crowdUniforms;

        phones += (phonesWanted - phones) * Math.min(delta * 2, 1);
        audience.phoneUniforms.uPhones.value = phones;
        energy.phones = phones;

        waveAge += delta;
        crowd.uWaveOn.value = THREE.MathUtils.smoothstep(waveAge, 0, 0.5) * (1 - THREE.MathUtils.smoothstep(waveAge, WAVE_SECONDS - 1, WAVE_SECONDS));
        crowd.uWaveAt.value = -Math.PI + waveAge * WAVE_SPEED;
        crowd.uCrowdTime.value = time;
        crowd.uBounce.value = show * (0.1 + energy.bass * 1.15);

        if (confetti.points.visible) {
            confettiAge += delta;
            confetti.uniforms.uAge.value = confettiAge;
            confetti.points.visible = confettiAge < CONFETTI_SECONDS;
        }
    }

    function update(time, camera, inSeat, delta) {
        const target = inSeat ? 1 : 0;

        if (Math.abs(target - show) > 0.001) {
            setShow(show + (target - show) * Math.min(delta * 2.4, 1));
        }

        setRoof(camera.position.y);

        if (reducedMotion) {
            return;
        }

        uniforms.uTime.value = time;
        bands.forEach((band) => {
            band.offset.x = time * 0.03;
        });
        audience.phoneUniforms.uTime.value = time;
        uniforms.uBeat.value = energy.kick * show;
        lightClock += delta * (1 + energy.bass * 2.4);

        if (time * 1000 > nextSampleAt) {
            nextSampleAt = time * 1000 + VIDEO_SAMPLE_MS;

            if (!sampleVideo(nextColour)) {
                const step = time / 6;
                const index = Math.floor(step) % FALLBACK_COLOURS.length;
                nextColour.set(FALLBACK_COLOURS[index]).lerp(fallback.set(FALLBACK_COLOURS[(index + 1) % FALLBACK_COLOURS.length]), step % 1);
            }

            // Lights stay saturated even when the video is dark.
            const hsl = {};
            nextColour.getHSL(hsl);
            nextColour.setHSL(hsl.h, Math.min(hsl.s * 1.4 + 0.15, 1), Math.max(hsl.l, 0.42));

            if (tint) {
                nextColour.lerp(tint, mood.tint.amount);
            }
        }

        lightColour.lerp(nextColour, Math.min(delta * 3, 1));
        flash.copy(lightColour).lerp(WHITE, energy.kick * 0.35);
        stageGlow.color.copy(flash);
        stageGlow.intensity = 0.7 * (1 - phones * 0.75);
        spill.color.copy(flash);
        spill.intensity = mood.spillPower * (1 + energy.bass * 0.7) * (1 - phones * 0.75);
        house.intensity = THREE.MathUtils.lerp(mood.house[0], mood.house[1], show) * (1 - phones * 0.6);
        confetti.uniforms.uTint.value.copy(lightColour);
        animateCrowd(time, delta);
        moveLights(lightClock);
        onFrame?.(time, flash, show, energy);

        haze.puffs.forEach(({ sprite, phase, z }) => {
            sprite.position.z = z + Math.sin(time * 0.12 + phase) * 2.5;
            sprite.material.color.copy(lightColour);
        });
        haze.dust.rotation.y = Math.sin(time * 0.02) * 0.02;
    }

    setShow(0);
    setRoof(999);
    moveLights(0);

    return {
        update,
        setShow,
        setRoof,
        getShow: () => show,
        setPeople: audience.setPeople,
        setEnergy(bass, treble, kick) {
            energy.bass = bass;
            energy.treble = treble;
            energy.kick = kick;
        },
        setPhones(on) {
            phonesWanted = on ? 1 : 0;
        },
        wave() {
            waveAge = 0;
        },
        confetti() {
            confettiAge = 0;
            confetti.points.visible = true;
        },
    };
}

export function blockLabels(scene, blocks, seats) {
    const labels = [];

    blocks.forEach((indices, name) => {
        const canvas = document.createElement('canvas');
        canvas.width = 128;
        canvas.height = 64;

        const context = canvas.getContext('2d');
        context.fillStyle = 'rgba(10, 10, 12, .78)';
        context.beginPath();
        context.roundRect(2, 2, 124, 60, 10);
        context.fill();
        context.fillStyle = '#ffffff';
        context.font = `600 38px ${FONT}`;
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(name, 64, 34);

        const texture = new THREE.CanvasTexture(canvas);
        texture.colorSpace = THREE.SRGBColorSpace;

        const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, depthWrite: false, opacity: 0.9, fog: false }));
        const middle = indices[Math.floor(indices.length / 2)];
        sprite.position.set(seats.x[middle], seats.y[middle] + 2.6, seats.z[middle]);
        sprite.scale.set(4.4, 2.2, 1);
        scene.add(sprite);
        labels.push(sprite);
    });

    return labels;
}
