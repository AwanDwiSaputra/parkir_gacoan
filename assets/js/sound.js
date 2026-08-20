/* ===========================================================
   Parkir Gacoan — sound.js
   Efek suara login sukses/gagal, disintesis langsung via Web Audio API
   (tidak butuh file .mp3/.wav eksternal, jadi selalu bisa jalan offline)
   =========================================================== */

function gacoanPlayTone(freqSequence, type = 'sine') {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        const ctx = new AudioCtx();
        let t = ctx.currentTime;

        freqSequence.forEach(({ freq, duration, gain }) => {
            const osc = ctx.createOscillator();
            const gainNode = ctx.createGain();
            osc.type = type;
            osc.frequency.setValueAtTime(freq, t);
            gainNode.gain.setValueAtTime(0, t);
            gainNode.gain.linearRampToValueAtTime(gain, t + 0.02);
            gainNode.gain.exponentialRampToValueAtTime(0.001, t + duration);
            osc.connect(gainNode);
            gainNode.connect(ctx.destination);
            osc.start(t);
            osc.stop(t + duration);
            t += duration * 0.85;
        });

        setTimeout(() => ctx.close(), (t + 0.5) * 1000);
    } catch (e) {
        // Browser tidak mendukung Web Audio API — abaikan secara diam-diam
    }
}

// Nada sukses: dua nada naik, ceria (mirip "ting-ting!")
function gacoanPlaySuccess() {
    gacoanPlayTone([
        { freq: 740, duration: 0.14, gain: 0.18 },
        { freq: 988, duration: 0.22, gain: 0.2 },
    ], 'sine');
}

// Nada gagal: nada turun pendek, berat (mirip "duh")
function gacoanPlayError() {
    gacoanPlayTone([
        { freq: 320, duration: 0.16, gain: 0.16 },
        { freq: 220, duration: 0.26, gain: 0.18 },
    ], 'sawtooth');
}
