(function() {
    'use strict';

    var root = document.getElementById('avayaar-root');
    if (!root) return;

    var data = window.AvayaarData || {};
    var RHYTHM_ROUNDS = 4;

    var state = {
        rhythmIndex: 0,
        rhythmTier: 1,
        rhythmResults: [],
        earIndex: 0,
        earResults: [],
        goalsIndex: 0,
        goalsAnswers: {},
        audioCtx: null
    };

    // Web Audio API requires an AudioContext to be created or resumed
    // inside a real user-gesture handler (click/tap) — browsers block
    // audio that starts on page load or on a timer. That's why this is
    // only ever called from the "شروع" button click, never on its own.
    function getAudioCtx() {
        if (!state.audioCtx) {
            state.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (state.audioCtx.state === 'suspended') state.audioCtx.resume();
        return state.audioCtx;
    }

    function playClick(atTime) {
        var ctx = getAudioCtx();
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.frequency.value = 880;
        gain.gain.setValueAtTime(0.001, atTime);
        gain.gain.exponentialRampToValueAtTime(0.3, atTime + 0.005);
        gain.gain.exponentialRampToValueAtTime(0.001, atTime + 0.08);
        osc.connect(gain).connect(ctx.destination);
        osc.start(atTime);
        osc.stop(atTime + 0.09);
    }

    function playTone(freq, durationMs, delaySec) {
        var ctx = getAudioCtx();
        var startAt = ctx.currentTime + (delaySec || 0);
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, startAt);
        gain.gain.exponentialRampToValueAtTime(0.25, startAt + 0.02);
        gain.gain.setValueAtTime(0.25, startAt + durationMs / 1000 - 0.03);
        gain.gain.exponentialRampToValueAtTime(0.0001, startAt + durationMs / 1000);
        osc.connect(gain).connect(ctx.destination);
        osc.start(startAt);
        osc.stop(startAt + durationMs / 1000 + 0.02);
    }

    function render(html) { root.innerHTML = html; }

    function renderIntro() {
        render(
            '<div class="avayaar-card">' +
            '<h2>دستیار استعدادیابی آوایار</h2>' +
            '<p>در چند دقیقه، پروفایل موسیقایی خودتو کشف کن.</p>' +
            '<button id="avayaar-start" class="avayaar-btn-primary">شروع</button>' +
            '</div>'
        );
        document.getElementById('avayaar-start').addEventListener('click', function() {
            getAudioCtx();
            startRhythmRound();
        });
    }

    // ---------- Rhythm module ----------

    function pickRhythmPattern(tier) {
        var pool = data.rhythm[tier] || data.rhythm[1];
        return pool[Math.floor(Math.random() * pool.length)];
    }

    function startRhythmRound() {
        if (state.rhythmIndex >= RHYTHM_ROUNDS) return startEarRound();

        var pattern = pickRhythmPattern(state.rhythmTier);
        render(
            '<div class="avayaar-card">' +
            '<div class="avayaar-progress">ریتم ' + (state.rhythmIndex + 1) + ' از ' + RHYTHM_ROUNDS + '</div>' +
            '<p>گوش بده...</p>' +
            '<div id="avayaar-pulse" class="avayaar-pulse"></div>' +
            '</div>'
        );
        playPatternThenRecord(pattern);
    }

    function playPatternThenRecord(pattern) {
        var ctx = getAudioCtx();
        var beatMs = 60000 / pattern.bpm;
        var t = ctx.currentTime + 0.3;
        var pulse = document.getElementById('avayaar-pulse');

        pattern.pattern.forEach(function(units) {
            playClick(t);
            (function(delayMs) {
                setTimeout(function() {
                    if (!pulse) return;
                    pulse.classList.add('active');
                    setTimeout(function() { pulse.classList.remove('active'); }, 100);
                }, delayMs);
            })((t - ctx.currentTime) * 1000);
            t += units * (beatMs / 1000);
        });

        var totalMs = (t - ctx.currentTime) * 1000;
        setTimeout(function() { startRecordingPhase(pattern, beatMs); }, totalMs + 400);
    }

    function startRecordingPhase(pattern, beatMs) {
        render(
            '<div class="avayaar-card">' +
            '<div class="avayaar-progress">ریتم ' + (state.rhythmIndex + 1) + ' از ' + RHYTHM_ROUNDS + '</div>' +
            '<p>حالا نوبت توئه! همون ریتم رو بزن.</p>' +
            '<button id="avayaar-tap" class="avayaar-tap-btn">ضربه بزن</button>' +
            '</div>'
        );

        var expectedTimes = [];
        var cumulative = 0;
        pattern.pattern.forEach(function(units) {
            expectedTimes.push(cumulative);
            cumulative += units * beatMs;
        });

        var recordingStart = null;
        var taps = [];
        var done = false;
        var tapBtn = document.getElementById('avayaar-tap');

        tapBtn.addEventListener('click', function() {
            if (done) return;
            var now = performance.now();
            if (recordingStart === null) recordingStart = now;
            taps.push(now - recordingStart);
            if (taps.length >= expectedTimes.length) {
                done = true;
                finishRhythmQuestion(pattern, expectedTimes, taps);
            }
        });

        // If they under-tap, pad with worst-case deltas rather than hang forever.
        setTimeout(function() {
            if (done) return;
            done = true;
            while (taps.length < expectedTimes.length) taps.push((recordingStart || performance.now()) + 9999);
            finishRhythmQuestion(pattern, expectedTimes, taps);
        }, cumulative + 3000);
    }

    function finishRhythmQuestion(pattern, expectedTimes, taps) {
        var deltas = expectedTimes.map(function(expected, i) {
            return Math.max(-999, Math.min(999, taps[i] - expected));
        });

        state.rhythmResults.push({ id: pattern.id, tap_deltas_ms: deltas });

        var avgAbs = deltas.reduce(function(s, d) { return s + Math.abs(d); }, 0) / deltas.length;
        if (avgAbs < 120 && state.rhythmTier < 3) state.rhythmTier++;
        else if (avgAbs > 250 && state.rhythmTier > 1) state.rhythmTier--;

        state.rhythmIndex++;
        setTimeout(startRhythmRound, 300);
    }

    // ---------- Ear module ----------

    function startEarRound() {
        if (state.earIndex >= data.ear.length) return startGoalsRound();

        var q = data.ear[state.earIndex];
        var isLongShort = q.type === 'longer_or_shorter';

        render(
            '<div class="avayaar-card">' +
            '<div class="avayaar-progress">شنوایی ' + (state.earIndex + 1) + ' از ' + data.ear.length + '</div>' +
            '<button id="avayaar-play" class="avayaar-btn-primary">پخش صدا</button>' +
            '<div id="avayaar-ear-options" class="avayaar-options" style="display:none;">' +
            (isLongShort
                ? '<button data-ans="a_longer" class="avayaar-btn">صدای اول بلندتر بود</button><button data-ans="b_longer" class="avayaar-btn">صدای دوم بلندتر بود</button>'
                : '<button data-ans="same" class="avayaar-btn">یکسان بودن</button><button data-ans="different" class="avayaar-btn">متفاوت بودن</button>') +
            '</div></div>'
        );

        document.getElementById('avayaar-play').addEventListener('click', function() {
            var gapSec = (q.tone_a.duration_ms + 200) / 1000;
            playTone(q.tone_a.freq, q.tone_a.duration_ms, 0);
            playTone(q.tone_b.freq, q.tone_b.duration_ms, gapSec);
            document.getElementById('avayaar-ear-options').style.display = 'block';
        });

        Array.prototype.forEach.call(document.querySelectorAll('#avayaar-ear-options button'), function(btn) {
            btn.addEventListener('click', function() {
                state.earResults.push({ id: q.id, answer: btn.getAttribute('data-ans') });
                state.earIndex++;
                startEarRound();
            });
        });
    }

    // ---------- Goals module ----------

    function startGoalsRound() {
        if (state.goalsIndex >= data.goals.length) return renderLeadForm();

        var q = data.goals[state.goalsIndex];
        var optionsHtml = Object.keys(q.options).map(function(key) {
            return '<button data-val="' + key + '" class="avayaar-btn">' + q.options[key] + '</button>';
        }).join('');

        render(
            '<div class="avayaar-card">' +
            '<div class="avayaar-progress">سوال ' + (state.goalsIndex + 1) + ' از ' + data.goals.length + '</div>' +
            '<p>' + q.question + '</p>' +
            '<div class="avayaar-options">' + optionsHtml + '</div></div>'
        );

        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-options button'), function(btn) {
            btn.addEventListener('click', function() {
                state.goalsAnswers[q.id] = btn.getAttribute('data-val');
                state.goalsIndex++;
                startGoalsRound();
            });
        });
    }

    // ---------- Lead form + submit ----------

    function renderLeadForm() {
        render(
            '<div class="avayaar-card">' +
            '<h3>برای دریافت نتیجه، اطلاعات تماست رو وارد کن</h3>' +
            '<input type="text" id="avayaar-name" placeholder="نام و نام‌خانوادگی" class="avayaar-input" />' +
            '<input type="tel" id="avayaar-phone" placeholder="شماره تماس" class="avayaar-input" />' +
            '<button id="avayaar-submit" class="avayaar-btn-primary">مشاهده نتیجه</button>' +
            '<div id="avayaar-error" class="avayaar-error"></div></div>'
        );
        document.getElementById('avayaar-submit').addEventListener('click', submitAssessment);
    }

    function submitAssessment() {
        var name = document.getElementById('avayaar-name').value.trim();
        var phone = document.getElementById('avayaar-phone').value.trim();
        var errorEl = document.getElementById('avayaar-error');

        if (!name || !phone) {
            errorEl.textContent = 'لطفا نام و شماره تماس رو وارد کن.';
            return;
        }

        var payload = {
            action: 'avayaar_submit',
            nonce: data.nonce,
            full_name: name,
            phone: phone,
            answers: JSON.stringify({
                rhythm: state.rhythmResults,
                ear: state.earResults,
                goals: state.goalsAnswers
            })
        };

        var body = Object.keys(payload).map(function(k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(payload[k]);
        }).join('&');

        render('<div class="avayaar-card"><p>در حال محاسبه نتیجه...</p></div>');

        fetch(data.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.success) renderResult(json.data);
                else render('<div class="avayaar-card"><p>خطایی رخ داد. لطفا دوباره تلاش کن.</p></div>');
            })
            .catch(function() {
                render('<div class="avayaar-card"><p>خطا در ارتباط با سرور.</p></div>');
            });
    }

    function renderResult(result) {
        // Placeholder only — the real result screen (dials, recommended
        // instrument pills, share card) is built in Phase 5.
        render(
            '<div class="avayaar-card">' +
            '<h3>پروفایل موسیقایی تو: ' + result.archetype + '</h3>' +
            '<p>تیم آموزشی آوایار به‌زودی باهات تماس می‌گیره.</p></div>'
        );
    }

    renderIntro();
})();