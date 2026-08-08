(function() {
    'use strict';

    var root = document.getElementById('avayaar-root');
    if (!root) return;

    root.innerHTML = '<div class="avayaar-grain" aria-hidden="true"></div><div id="avayaar-stage"></div>';
    var stage = document.getElementById('avayaar-stage');

    var data = window.AvayaarData || {};
    var RHYTHM_ROUNDS = 3;
    var TOTAL_STAGES = 5;

    var state = {
        stage: 0,
        xp: 0,
        rhythmIndex: 0,
        rhythmTier: 1,
        rhythmResults: [],
        earAnswers: {},
        styleAnswers: [],
        personalityAnswers: {},
        moodAnswer: null,
        audioCtx: null,
        user: { name: '', age: null, history: null, instruments: [], why: null }
    };

    function getAudioCtx() {
        if (!state.audioCtx) state.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (state.audioCtx.state === 'suspended') state.audioCtx.resume();
        return state.audioCtx;
    }

    function playClick(atTime) {
        var ctx = getAudioCtx();
        var osc = ctx.createOscillator(), gain = ctx.createGain();
        osc.frequency.value = 880;
        gain.gain.setValueAtTime(0.001, atTime);
        gain.gain.exponentialRampToValueAtTime(0.3, atTime + 0.005);
        gain.gain.exponentialRampToValueAtTime(0.001, atTime + 0.08);
        osc.connect(gain).connect(ctx.destination);
        osc.start(atTime); osc.stop(atTime + 0.09);
    }

    function playPattern(pattern, onPulse, onDone) {
        var ctx = getAudioCtx();
        var beatMs = 60000 / pattern.bpm;
        var t = ctx.currentTime + 0.3;
        pattern.pattern.forEach(function(units) {
            playClick(t);
            var delayMs = (t - ctx.currentTime) * 1000;
            setTimeout(function() { if (onPulse) onPulse(); }, delayMs);
            t += units * (beatMs / 1000);
        });
        setTimeout(onDone, (t - ctx.currentTime) * 1000 + 300);
    }

    function render(html) { stage.innerHTML = '<div class="avayaar-fade-in">' + html + '</div>'; }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function arrowIcon() {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
    }

    var ICONS = {
        headphones: '<path d="M4 13v-1a8 8 0 0 1 16 0v1"/><rect x="2" y="13" width="5" height="7" rx="2"/><rect x="17" y="13" width="5" height="7" rx="2"/>',
        mic: '<rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10v1a7 7 0 0 0 14 0v-1"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/>',
        piano: '<rect x="3" y="4" width="18" height="16" rx="1"/><line x1="7" y1="4" x2="7" y2="14"/><line x1="11" y1="4" x2="11" y2="14"/><line x1="15" y1="4" x2="15" y2="14"/><line x1="19" y1="4" x2="19" y2="14"/>',
        rewind: '<path d="M12 5a7 7 0 1 1-6.3 4"/><polyline points="5 3 5 9 11 9"/>',
        spark: '<path d="M12 2l1.8 5.6L19 9l-5.2 1.4L12 16l-1.8-5.6L5 9l5.2-1.4L12 2z"/>',
        help: '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.9.4-1.5 1-1.5 2.2"/><line x1="12" y1="17" x2="12" y2="17.1"/>',
        heart: '<path d="M12 20s-7-4.4-9.5-8.8C.7 8 2 4.6 5.4 4.1 7.6 3.8 9.7 5 12 8c2.3-3 4.4-4.2 6.6-3.9 3.4.5 4.7 3.9 2.9 7.1C19 15.6 12 20 12 20z"/>',
        cap: '<path d="M12 3 2 8l10 5 10-5-10-5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/>',
        briefcase: '<rect x="3" y="8" width="18" height="12" rx="2"/><path d="M9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>',
        target: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        note: '<circle cx="7" cy="17" r="2.5"/><circle cx="16" cy="15" r="2.5"/><path d="M9.5 17V4.5L18.5 3v12"/>'
    };

    function svgIcon(name, size) {
        size = size || 26;
        return '<svg width="' + size + '" height="' + size + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' + (ICONS[name] || ICONS.spark) + '</svg>';
    }

    function meetYouProgress(step, total) {
        var pct = Math.round((step / total) * 100);
        return '<div class="avayaar-chapter-head"><span class="avayaar-chapter-label">آشنایی با تو</span><div class="avayaar-chapter-track"><div class="avayaar-chapter-fill" style="width:' + pct + '%"></div></div></div>';
    }

    function playMicroSound() {
        try {
            var ctx = getAudioCtx();
            var t = ctx.currentTime;
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, t);
            osc.frequency.exponentialRampToValueAtTime(1320, t + 0.12);
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.25, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
            osc.connect(gain).connect(ctx.destination);
            osc.start(t);
            osc.stop(t + 0.4);
        } catch (e) { /* audio unavailable — never block navigation on sound */ }
    }

    function progressBar() {
        var pct = Math.round((state.stage / TOTAL_STAGES) * 100);
        return '<div class="avayaar-topbar"><div class="avayaar-progress-track"><div class="avayaar-progress-fill" style="width:' + pct + '%"></div></div><span class="avayaar-xp">⭐ ' + state.xp + ' XP</span></div>';
    }

    function stageComplete(next) {
        state.xp += 20;
        state.stage++;
        var toast = document.createElement('div');
        toast.className = 'avayaar-toast';
        toast.textContent = 'عالی بود! ✨';
        root.appendChild(toast);
        setTimeout(function() { next(); }, 500);
    }

    // ---------- Screen 00 — Entry ----------
    function renderEntryScreen() {
        render(
            '<div class="avayaar-entry">' +
            '<div class="avayaar-entry-logo">رودکی</div>' +
            '<div class="avayaar-entry-center">' +
            '<div class="avayaar-orb-wrap">' +
            '<div class="avayaar-orb-dot"></div>' +
            '<div class="avayaar-orb" id="avayaar-orb">' +
            '<div class="avayaar-orb-core"></div>' +
            '</div>' +
            '<svg class="avayaar-orb-rings" viewBox="0 0 180 180">' +
            '<circle class="ring-1" cx="90" cy="90" r="55"/>' +
            '<circle class="ring-2" cx="90" cy="90" r="70"/>' +
            '</svg>' +
            '<div class="avayaar-orb-particles"><span></span><span></span><span></span><span></span><span></span><span></span></div>' +
            '</div>' +
            '<div>' +
            '<h1 class="avayaar-entry-headline"><span class="line1">هر آدمی</span><span class="line2">یک ریتم دارد.</span></h1>' +
            '<p class="avayaar-entry-subtitle">یک سفر کوتاه به دنیای موسیقیایی تو.</p>' +
            '</div>' +
            '</div>' +
            '<div class="avayaar-entry-bottom">' +
            '<button id="avayaar-start" class="avayaar-entry-cta">' +
            '<span>شروع تجربه</span>' + arrowIcon() +
            '</button>' +
            '<div class="avayaar-entry-meta">۵–۷ دقیقه · تجربه صوتی</div>' +
            '</div>' +
            '</div>'
        );

        var orb = document.getElementById('avayaar-orb');
        setTimeout(function() { if (orb) orb.classList.add('avayaar-breathing'); }, 1400);
        orb.addEventListener('animationend', function(e) {
            if (e.animationName === 'avayaarOrbPulseHover') {
                orb.classList.remove('avayaar-pulse-hover');
                orb.classList.add('avayaar-breathing');
            }
        });

        var cta = document.getElementById('avayaar-start');
        cta.addEventListener('mouseenter', function() {
            orb.classList.remove('avayaar-breathing');
            orb.classList.add('avayaar-pulse-hover');
        });
        cta.addEventListener('click', function() {
            getAudioCtx();
            playMicroSound();
            renderTransitionScreen();
        });
    }

    // ---------- Screen 01 — Enter the Experience ----------
    function renderTransitionScreen() {
        render(
            '<div class="avayaar-transition">' +
            '<div class="avayaar-transition-visual">' +
            '<div class="avayaar-ripple"></div>' +
            '<svg class="avayaar-wave-morph" viewBox="0 0 300 60" preserveAspectRatio="none">' +
            '<path d="M0,30 C25,10 50,50 75,30 C100,10 125,50 150,30 C175,10 200,50 225,30 C250,10 275,50 300,30" stroke="#F16923" stroke-width="2" fill="none"/>' +
            '</svg>' +
            '</div>' +
            '<p class="avayaar-transition-text">این سفر درباره‌ی توست.</p>' +
            '<div class="avayaar-transition-steps">' +
            '<span class="avayaar-transition-step" data-i="1">گوش می‌دیم.</span>' +
            '<span class="avayaar-transition-step" data-i="2">انتخاب می‌کنیم.</span>' +
            '<span class="avayaar-transition-step" data-i="3">کشف می‌کنیم.</span>' +
            '</div>' +
            '</div>'
        );
        setTimeout(startUserInfoStage, 3400);
    }

    // ---------- Screen 02 — Meet You ----------
    function startUserInfoStage() { askName(); }

    function askName() {
        render(
            meetYouProgress(1, 5) +
            '<div class="avayaar-meet">' +
            '<h2 class="avayaar-meet-title">اول با خودت شروع کنیم.</h2>' +
            '<p class="avayaar-meet-sub">چند انتخاب ساده؛ فقط برای اینکه بهتر بشناسیمت.</p>' +
            '<div class="avayaar-meet-question">' +
            '<p class="avayaar-meet-question-text">اسمت چیه؟</p>' +
            '<div class="avayaar-editorial-input-wrap">' +
            '<input type="text" id="avayaar-name-input" class="avayaar-editorial-input" placeholder="اسم کوچیکت رو اینجا بنویس…" autocomplete="off" />' +
            '</div>' +
            '</div>' +
            '</div>'
        );
        var input = document.getElementById('avayaar-name-input');
        input.focus();
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var val = input.value.trim();
                if (!val) return;
                state.user.name = val;
                confirmName(val);
            }
        });
    }

    function confirmName(name) {
        render(
            meetYouProgress(1, 5) +
            '<div class="avayaar-meet avayaar-meet-confirm">' +
            '<p class="avayaar-meet-name-big">' + escapeHtml(name) + '</p>' +
            '<p class="avayaar-meet-welcome">خوش اومدی، ' + escapeHtml(name) + '.</p>' +
            '</div>'
        );
        setTimeout(askAge, 1400);
    }

    function askAge() {
        var ranges = data.ageRanges || [];
        var html = meetYouProgress(2, 5) +
            '<div class="avayaar-meet"><p class="avayaar-meet-question-text">کدوم بازه‌ی سنی به تو نزدیک‌تره؟</p><div class="avayaar-age-list">';
        ranges.forEach(function(r) {
            html += '<button type="button" class="avayaar-age-option" data-id="' + r.id + '">' + r.label + '</button>';
        });
        html += '</div></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-age-option'), function(btn) {
            btn.addEventListener('click', function() {
                Array.prototype.forEach.call(document.querySelectorAll('.avayaar-age-option'), function(b) { b.classList.remove('is-selected'); });
                btn.classList.add('is-selected');
                state.user.age = btn.getAttribute('data-id');
                setTimeout(askMusicHistory, 450);
            });
        });
    }

    var HISTORY_ICONS = { listener: 'headphones', sings: 'mic', plays: 'piano', played: 'rewind', starting: 'spark', unsure: 'help' };

    function askMusicHistory() {
        var opts = data.musicHistory || [];
        var html = meetYouProgress(3, 5) +
            '<div class="avayaar-meet"><p class="avayaar-meet-question-text">موسیقی تا حالا چه‌قدر توی زندگی‌ت بوده؟</p><div class="avayaar-choice-grid">';
        opts.forEach(function(o) {
            html += '<button type="button" class="avayaar-choice-card" data-id="' + o.id + '">' + svgIcon(HISTORY_ICONS[o.id] || 'spark') + '<span>' + o.label + '</span></button>';
        });
        html += '</div></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-choice-card'), function(btn) {
            btn.addEventListener('click', function() {
                state.user.history = btn.getAttribute('data-id');
                btn.classList.add('is-selected');
                setTimeout(function() {
                    if (state.user.history === 'plays' || state.user.history === 'played') askInstruments();
                    else askWhyMusic();
                }, 350);
            });
        });
    }

    function askInstruments() {
        var opts = data.instruments || [];
        var html = meetYouProgress(4, 5) +
            '<div class="avayaar-meet"><p class="avayaar-meet-question-text">با چه سازی آشنایی داری؟</p><div class="avayaar-instrument-grid">';
        opts.forEach(function(o) {
            html += '<button type="button" class="avayaar-instrument-card" data-id="' + o.id + '"><span class="avayaar-instrument-glyph">' + svgIcon('note', 22) + '</span><span>' + o.label + '</span></button>';
        });
        html += '</div><button id="avayaar-instruments-next" class="avayaar-entry-cta avayaar-meet-next"><span>ادامه</span>' + arrowIcon() + '</button></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-instrument-card'), function(btn) {
            btn.addEventListener('click', function() {
                var id = btn.getAttribute('data-id');
                var idx = state.user.instruments.indexOf(id);
                if (idx === -1) { state.user.instruments.push(id); btn.classList.add('is-selected'); }
                else { state.user.instruments.splice(idx, 1); btn.classList.remove('is-selected'); }
            });
        });
        document.getElementById('avayaar-instruments-next').addEventListener('click', askWhyMusic);
    }

    var WHY_ICONS = { self: 'heart', perform: 'mic', serious: 'cap', experience: 'spark', career: 'briefcase', focus: 'target', unsure: 'help' };

    function askWhyMusic() {
        var opts = data.whyMusic || [];
        var html = meetYouProgress(5, 5) +
            '<div class="avayaar-meet"><p class="avayaar-meet-question-text">اگر قرار باشه موسیقی رو جدی‌تر دنبال کنی، بیشتر برای چی می‌خوای؟</p><div class="avayaar-choice-grid">';
        opts.forEach(function(o) {
            html += '<button type="button" class="avayaar-choice-card" data-id="' + o.id + '">' + svgIcon(WHY_ICONS[o.id] || 'spark') + '<span>' + o.label + '</span></button>';
        });
        html += '</div></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-choice-card'), function(btn) {
            btn.addEventListener('click', function() {
                state.user.why = btn.getAttribute('data-id');
                btn.classList.add('is-selected');
                setTimeout(finishMeetYou, 350);
            });
        });
    }

    function finishMeetYou() {
        render('<div class="avayaar-transition"><p class="avayaar-transition-text">حالا که با تو آشنا شدیم…</p></div>');
        setTimeout(function() {
            render('<div class="avayaar-transition"><p class="avayaar-transition-text avayaar-transition-emphasis">ببینیم ریتمت چطوره.</p></div>');
            // Chapter 01 — Rhythm (Screens 03–13) is built next.
        }, 1600);
    }

    // ---------- Stage 1: Musical Ear ----------
    function startEarStage() {
        var ear = data.ear;
        render(progressBar() +
            '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۱ · گوش موسیقی 🎧</div>' +
            '<p>گوش کن...</p><div id="avayaar-pulse" class="avayaar-pulse"></div></div>'
        );
        playPattern(ear.tempo_clip, function() {
            var p = document.getElementById('avayaar-pulse');
            if (p) { p.classList.add('active'); setTimeout(function() { p.classList.remove('active'); }, 100); }
        }, function() { askTempo(ear); });
    }

    function askTempo(ear) {
        var opts = ear.tempo_question.options;
        var html = progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۱ · گوش موسیقی 🎧</div><p>' + ear.tempo_question.text + '</p><div class="avayaar-options">';
        Object.keys(opts).forEach(function(k) { html += '<button data-val="' + k + '" class="avayaar-btn">' + opts[k] + '</button>'; });
        html += '</div></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-options button'), function(btn) {
            btn.addEventListener('click', function() {
                state.earAnswers.tempo = btn.getAttribute('data-val');
                playRegularityClips(ear);
            });
        });
    }

    function playRegularityClips(ear) {
        render(progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۱ · گوش موسیقی 🎧</div><p>دو ریتم رو گوش کن...</p><div id="avayaar-pulse" class="avayaar-pulse"></div></div>');
        playPattern(ear.regularity_clips.a, pulseFx, function() {
            setTimeout(function() {
                playPattern(ear.regularity_clips.b, pulseFx, function() { askRegularity(ear); });
            }, 400);
        });
    }

    function pulseFx() {
        var p = document.getElementById('avayaar-pulse');
        if (p) { p.classList.add('active'); setTimeout(function() { p.classList.remove('active'); }, 100); }
    }

    function askRegularity(ear) {
        var opts = ear.regularity_question.options;
        var html = progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۱ · گوش موسیقی 🎧</div><p>' + ear.regularity_question.text + '</p><div class="avayaar-options">';
        Object.keys(opts).forEach(function(k) { html += '<button data-val="' + k + '" class="avayaar-btn">' + opts[k] + '</button>'; });
        html += '</div></div>';
        render(html);
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-options button'), function(btn) {
            btn.addEventListener('click', function() {
                state.earAnswers.regularity = btn.getAttribute('data-val');
                stageComplete(startRhythmStage);
            });
        });
    }

    // ---------- Stage 2: Rhythm Imitation ----------
    function startRhythmStage() {
        if (state.rhythmIndex >= RHYTHM_ROUNDS) return stageComplete(startStyleStage);
        var pool = data.rhythm[state.rhythmTier] || data.rhythm[1];
        var pattern = pool[Math.floor(Math.random() * pool.length)];
        render(progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۲ · تقلید ریتم 👏 (' + (state.rhythmIndex + 1) + '/' + RHYTHM_ROUNDS + ')</div><p>گوش بده...</p><div id="avayaar-pulse" class="avayaar-pulse"></div></div>');
        playPattern(pattern, pulseFx, function() { recordRhythm(pattern); });
    }

    function recordRhythm(pattern) {
        var beatMs = 60000 / pattern.bpm;
        var expected = [], cumulative = 0;
        pattern.pattern.forEach(function(u) { expected.push(cumulative); cumulative += u * beatMs; });

        render(progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۲ · تقلید ریتم 👏</div><p>حالا نوبت توئه!</p><button id="avayaar-tap" class="avayaar-tap-btn">بزن 👏</button></div>');

        var start = null, taps = [], done = false;
        document.getElementById('avayaar-tap').addEventListener('click', function() {
            if (done) return;
            var now = performance.now();
            if (start === null) start = now;
            taps.push(now - start);
            if (taps.length >= expected.length) { done = true; finishRhythm(pattern, expected, taps); }
        });
        setTimeout(function() {
            if (done) return;
            done = true;
            while (taps.length < expected.length) taps.push((start || performance.now()) + 9999);
            finishRhythm(pattern, expected, taps);
        }, cumulative + 3000);
    }

    function finishRhythm(pattern, expected, taps) {
        var deltas = expected.map(function(e, i) { return Math.max(-999, Math.min(999, taps[i] - e)); });
        state.rhythmResults.push({ id: pattern.id, tap_deltas_ms: deltas });
        var avgAbs = deltas.reduce(function(s, d) { return s + Math.abs(d); }, 0) / deltas.length;
        if (avgAbs < 120 && state.rhythmTier < 3) state.rhythmTier++;
        else if (avgAbs > 250 && state.rhythmTier > 1) state.rhythmTier--;
        state.rhythmIndex++;
        setTimeout(startRhythmStage, 300);
    }

    // ---------- Stage 3: Music Style ----------
    function startStyleStage() {
        var clips = data.style;
        var html = progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۳ · سبک موسیقی ❤️</div><p>هر کدوم رو دوست داشتی، قلب بزن. می‌تونی چندتا رو انتخاب کنی.</p><div class="avayaar-clip-list">';
        clips.forEach(function(c) {
            html += '<div class="avayaar-clip-row" data-id="' + c.id + '">' +
                '<button class="avayaar-btn avayaar-play-clip" data-file="' + c.file + '">▶ ' + c.label + '</button>' +
                '<button class="avayaar-like-btn" data-id="' + c.id + '">🤍</button></div>';
        });
        html += '</div><button id="avayaar-style-next" class="avayaar-btn-primary">ادامه</button></div>';
        render(html);

        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-play-clip'), function(btn) {
            btn.addEventListener('click', function() {
                var audio = new Audio(AvayaarData.audioBaseUrl + btn.getAttribute('data-file'));
                audio.play().catch(function() { /* file not uploaded yet — fails silently in dev */ });
            });
        });
        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-like-btn'), function(btn) {
            btn.addEventListener('click', function() {
                var id = btn.getAttribute('data-id');
                var i = state.styleAnswers.indexOf(id);
                if (i === -1) { state.styleAnswers.push(id); btn.textContent = '❤️'; btn.classList.add('liked'); }
                else { state.styleAnswers.splice(i, 1); btn.textContent = '🤍'; btn.classList.remove('liked'); }
            });
        });
        document.getElementById('avayaar-style-next').addEventListener('click', function() { stageComplete(startPersonalityStage); });
    }

    // ---------- Stage 4: Personality ----------
    function startPersonalityStage(idx) {
        idx = idx || 0;
        var questions = data.personality;
        if (idx >= questions.length) return stageComplete(startMoodStage);

        var q = questions[idx];
        var html = progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۴ · شخصیت 🎭</div><p>' + q.text + '</p><div class="avayaar-options">';
        Object.keys(q.options).forEach(function(k) { html += '<button data-val="' + k + '" class="avayaar-btn">' + q.options[k] + '</button>'; });
        html += '</div></div>';
        render(html);

        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-options button'), function(btn) {
            btn.addEventListener('click', function() {
                state.personalityAnswers[q.id] = btn.getAttribute('data-val');
                startPersonalityStage(idx + 1);
            });
        });
    }

    // ---------- Stage 5: Mood ----------
    function startMoodStage() {
        var moods = data.mood;
        var html = progressBar() + '<div class="avayaar-card"><div class="avayaar-stage-label">مرحله ۵ · حال‌وهوا 🌈</div><p>کدوم حس بیشتر بهت نزدیکه؟</p><div class="avayaar-mood-grid">';
        moods.forEach(function(m) { html += '<button class="avayaar-mood-tile avayaar-mood-' + m.id + '" data-id="' + m.id + '"><span>' + m.emoji + '</span>' + m.label + '</button>'; });
        html += '</div></div>';
        render(html);

        Array.prototype.forEach.call(document.querySelectorAll('.avayaar-mood-tile'), function(btn) {
            btn.addEventListener('click', function() {
                state.moodAnswer = btn.getAttribute('data-id');
                stageComplete(renderLeadForm);
            });
        });
    }

    // ---------- Lead form + submit ----------
    function renderLeadForm() {
        render('<div class="avayaar-card"><h3>یه قدم تا نتیجه! 🎉</h3><input type="text" id="avayaar-name" placeholder="نام و نام‌خانوادگی" class="avayaar-input" /><input type="tel" id="avayaar-phone" placeholder="شماره تماس" class="avayaar-input" /><button id="avayaar-submit" class="avayaar-btn-primary">نمایش نتیجه 🎁</button><div id="avayaar-error" class="avayaar-error"></div></div>');
        document.getElementById('avayaar-submit').addEventListener('click', submitAssessment);
    }

    function submitAssessment() {
        var name = document.getElementById('avayaar-name').value.trim();
        var phone = document.getElementById('avayaar-phone').value.trim();
        var errorEl = document.getElementById('avayaar-error');
        if (!name || !phone) { errorEl.textContent = 'لطفا نام و شماره تماس رو وارد کن.'; return; }

        var payload = {
            action: 'avayaar_submit', nonce: data.nonce, full_name: name, phone: phone,
            answers: JSON.stringify({
                rhythm: state.rhythmResults, ear: state.earAnswers, style: state.styleAnswers,
                personality: state.personalityAnswers, mood: state.moodAnswer
            })
        };
        var body = Object.keys(payload).map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(payload[k]); }).join('&');

        render('<div class="avayaar-card"><p>در حال ساخت پروفایل موسیقایی تو...</p></div>');

        fetch(data.ajaxUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
            .then(function(r) { return r.json(); })
            .then(function(json) { if (json.success) renderResult(json.data); else render('<div class="avayaar-card"><p>خطایی رخ داد. دوباره تلاش کن.</p></div>'); })
            .catch(function() { render('<div class="avayaar-card"><p>خطا در ارتباط با سرور.</p></div>'); });
    }

    // ---------- Result ----------
    function toPersianDigits(str) {
        var d = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        return String(str).replace(/[0-9]/g, function(x) { return d[x]; });
    }

    var familyLabels = { string: 'زهی', keys: 'کلاویه‌ای', wind: 'بادی', perc: 'کوبه‌ای', plucked: 'زهی مضرابی', vocal: 'آواز', iranian: 'سنتی ایرانی' };

    function renderResult(result) {
        var matchesHtml = Object.keys(result.top_families).map(function(fam) {
            return '<div class="avayaar-match-row"><span>' + familyLabels[fam] + '</span><div class="avayaar-match-bar"><div class="avayaar-match-fill" style="width:' + result.top_families[fam] + '%"></div></div><span>' + toPersianDigits(result.top_families[fam]) + '٪</span></div>';
        }).join('');

        var instrumentsHtml = result.instruments.map(function(i) { return '<a href="' + i.url + '" target="_blank" rel="noopener" class="avayaar-pill">' + i.title + '</a>'; }).join('');

        render(
            '<div class="avayaar-card avayaar-result">' +
            '<div class="avayaar-badge">' + result.badge.emoji + ' ' + result.badge.label + '</div>' +
            '<h3>بهترین تطبیق‌های تو</h3>' +
            '<div class="avayaar-matches">' + matchesHtml + '</div>' +
            '<div class="avayaar-instrument-pills">' + instrumentsHtml + '</div>' +
            '<p class="avayaar-roadmap">به نظر می‌رسه این سازها بیشترین همخونی رو با علایق و حس ریتم تو دارن. برای اطمینان کامل، یه جلسه ۲۰ دقیقه‌ای رایگان با مربی رزرو کن و عملی امتحانش کن.</p>' +
            '<a href="tel:' + result.phone_cta + '" class="avayaar-btn-primary avayaar-cta">📞 رزرو جلسه رایگان</a>' +
            '<button id="avayaar-share" class="avayaar-btn">دانلود کارت نتیجه</button>' +
            '</div>'
        );
        fireConfetti();
        document.getElementById('avayaar-share').addEventListener('click', function() { downloadShareCard(result); });
    }

    // ---------- Confetti (hand-rolled, no dependency) ----------
    function fireConfetti() {
        var canvas = document.createElement('canvas');
        canvas.className = 'avayaar-confetti-canvas';
        canvas.width = window.innerWidth; canvas.height = window.innerHeight;
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');
        var colors = ['#4F7CFF', '#9db4ff', '#ffd166', '#ef476f', '#06d6a0'];
        var pieces = [];
        for (var i = 0; i < 80; i++) {
            pieces.push({ x: Math.random() * canvas.width, y: -20, vx: (Math.random() - 0.5) * 4, vy: 2 + Math.random() * 3, size: 4 + Math.random() * 4, color: colors[i % colors.length], rot: Math.random() * 360 });
        }
        var frame = 0;
        function tick() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pieces.forEach(function(p) {
                p.x += p.vx; p.y += p.vy; p.rot += 6;
                ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot * Math.PI / 180);
                ctx.fillStyle = p.color; ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                ctx.restore();
            });
            frame++;
            if (frame < 90) requestAnimationFrame(tick);
            else document.body.removeChild(canvas);
        }
        tick();
    }

    // ---------- Share card ----------
    function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        var words = text.split(' '), line = '', lines = [];
        words.forEach(function(w) {
            var test = line ? line + ' ' + w : w;
            if (ctx.measureText(test).width > maxWidth && line) { lines.push(line); line = w; } else line = test;
        });
        if (line) lines.push(line);
        lines.forEach(function(l, i) { ctx.fillText(l, x, y + i * lineHeight); });
    }

    function downloadShareCard(result) {
        var canvas = document.createElement('canvas');
        canvas.width = 1080; canvas.height = 1080;
        var ctx = canvas.getContext('2d');
        var grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
        grad.addColorStop(0, '#10172A'); grad.addColorStop(1, '#1B2545');
        ctx.fillStyle = grad; ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.direction = 'rtl'; ctx.textAlign = 'center';
        ctx.fillStyle = '#4F7CFF'; ctx.font = 'bold 56px IRANYekanXAvayaar, sans-serif';
        ctx.fillText('آوایار', canvas.width / 2, 140);

        ctx.fillStyle = '#ffffff'; ctx.font = 'bold 64px IRANYekanXAvayaar, sans-serif';
        ctx.fillText(result.badge.emoji + ' ' + result.badge.label, canvas.width / 2, 260);

        var y = 400;
        Object.keys(result.top_families).forEach(function(fam) {
            ctx.font = 'bold 44px IRANYekanXAvayaar, sans-serif';
            ctx.fillStyle = '#ffffff';
            ctx.fillText(familyLabels[fam] + '  ·  ' + toPersianDigits(result.top_families[fam]) + '٪', canvas.width / 2, y);
            y += 90;
        });

        ctx.font = '28px IRANYekanXAvayaar, sans-serif';
        ctx.fillStyle = 'rgba(255,255,255,0.5)';
        ctx.fillText('roodaki-babol.ir', canvas.width / 2, canvas.height - 60);

        var link = document.createElement('a');
        link.download = 'avayaar-result.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    renderEntryScreen();
})();