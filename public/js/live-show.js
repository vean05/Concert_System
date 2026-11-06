// The song in the seat view. It plays the artist's previews through Web Audio, so
// the show can react to the music and the sound can change with the seat: further
// back it gets quieter, darker and more echoey, and it leans towards the nearer PA.

const FFT_SIZE = 1024;
const BAR_COUNT = 5;
const KICK_THRESHOLD = 0.14;
const VOLUME = 0.85;

function roomEcho(context) {
    const seconds = 2.4;
    const length = Math.floor(context.sampleRate * seconds);
    const impulse = context.createBuffer(2, length, context.sampleRate);

    for (let channel = 0; channel < 2; channel++) {
        const samples = impulse.getChannelData(channel);
        for (let i = 0; i < length; i++) {
            samples[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / length, 3.2);
        }
    }

    return impulse;
}

export class LiveShow {
    constructor(songs) {
        this.songs = songs;
        this.index = 0;
        this.playing = false;
        this.seat = { distance: 30, pan: 0 };
        this.levels = { bass: 0, treble: 0, kick: 0, bars: new Array(BAR_COUNT).fill(0) };
        this.slowBass = 0;
        this.meanBass = 0.6;
        this.onChange = () => {};
        this.onError = () => {};
    }

    get song() {
        return this.songs[this.index] ?? null;
    }

    /** The first play has to come from a click, which is when audio may start. */
    prepare() {
        if (this.context) {
            return;
        }

        this.audio = new Audio();
        this.audio.crossOrigin = 'anonymous';
        this.audio.preload = 'auto';
        this.audio.addEventListener('ended', () => this.next());
        this.audio.addEventListener('error', () => {
            this.playing = false;
            this.onError();
            this.onChange();
        });

        const AudioEngine = window.AudioContext || window.webkitAudioContext;
        const context = new AudioEngine();
        this.context = context;

        const source = context.createMediaElementSource(this.audio);
        this.analyser = context.createAnalyser();
        this.analyser.fftSize = FFT_SIZE;
        this.analyser.smoothingTimeConstant = 0.55;
        this.frequencies = new Uint8Array(this.analyser.frequencyBinCount);

        this.panner = context.createStereoPanner();
        this.filter = context.createBiquadFilter();
        this.filter.type = 'lowpass';
        this.dry = context.createGain();
        this.wet = context.createGain();
        const echo = context.createConvolver();
        echo.buffer = roomEcho(context);

        source.connect(this.analyser);
        source.connect(this.filter);
        this.filter.connect(this.panner);
        this.panner.connect(this.dry);
        this.panner.connect(echo);
        echo.connect(this.wet);
        this.dry.connect(context.destination);
        this.wet.connect(context.destination);

        this.applySeat();
    }

    setSeat(seat) {
        this.seat = seat;
        this.applySeat();
    }

    applySeat() {
        if (!this.context) {
            return;
        }

        const reach = Math.min(this.seat.distance / 110, 1);
        const now = this.context.currentTime;

        this.dry.gain.setTargetAtTime(VOLUME * (1 - reach * 0.5), now, 0.25);
        this.wet.gain.setTargetAtTime(0.1 + reach * 0.5, now, 0.25);
        this.filter.frequency.setTargetAtTime(19000 * Math.pow(0.16, reach), now, 0.25);
        this.panner.pan.setTargetAtTime(this.seat.pan, now, 0.25);
    }

    load() {
        this.audio.src = this.song.preview;
    }

    play() {
        if (!this.song) {
            return;
        }

        this.prepare();
        this.context.resume();

        if (!this.audio.src) {
            this.load();
        }

        this.playing = true;
        this.audio.play().catch(() => {
            this.playing = false;
            this.onError();
            this.onChange();
        });
        this.onChange();
    }

    pause() {
        this.playing = false;
        this.audio?.pause();
        this.onChange();
    }

    toggle() {
        if (this.playing) {
            this.pause();
        } else {
            this.play();
        }
    }

    next() {
        this.index = (this.index + 1) % this.songs.length;

        if (this.audio) {
            this.load();
        }

        this.play();
    }

    /** Reads the music once a frame and returns how loud each part is, from 0 to 1. */
    update(delta) {
        const levels = this.levels;

        if (!this.playing || !this.analyser) {
            levels.bass *= 0.9;
            levels.treble *= 0.9;
            levels.kick *= 0.85;
            levels.bars.fill(0);
            return levels;
        }

        this.analyser.getByteFrequencyData(this.frequencies);

        const average = (from, to) => {
            let sum = 0;
            for (let i = from; i < to; i++) {
                sum += this.frequencies[i];
            }
            return sum / (to - from) / 255;
        };

        const bass = average(1, 6);
        const treble = average(90, 220);

        // Masters are loud all the way through, so what matters is the pulse above
        // the song's own average, not the raw level.
        this.meanBass += (bass - this.meanBass) * Math.min(delta * 0.5, 1);
        const pulse = Math.min(Math.max(0.32 + (bass - this.meanBass) * 4.5, 0), 1);

        levels.bass += (pulse - levels.bass) * (pulse > levels.bass ? 0.6 : 0.12);
        levels.treble += (treble - levels.treble) * 0.3;

        // A kick is a jump above the recent bass, not simply loud bass.
        const rise = bass - this.slowBass;
        this.slowBass += (bass - this.slowBass) * Math.min(delta * 5, 1);
        levels.kick = rise > KICK_THRESHOLD ? 1 : levels.kick * 0.86;

        for (let i = 0; i < BAR_COUNT; i++) {
            levels.bars[i] = average(2 + i * i * 8, 8 + (i + 1) * (i + 1) * 12);
        }

        return levels;
    }
}
