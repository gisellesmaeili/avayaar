(function() {
    'use strict';

    var root = document.getElementById('avayaar-root');
    if (!root) return;

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
        audioCtx: null
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

    function render(html) { root.innerHTML = '<div class="avayaar-fade-in">' + html + '</div>'; }

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

    // ---------- Intro ----------
    function renderIntro() {
        render(
            '<div class="avayaar-card avayaar-intro">' +
            '<h2>سفر موسیقی آوایار 🎵</h2>' +
            '<p>در ۵ مرحله کوتاه و باحال، ببین کدوم ساز باهات بیشتر جوره. هیچ جواب درست یا غلطی وجود نداره!</p>' +
            '<button id="avayaar-start" class="avayaar-btn-primary">بزن بریم 🚀</button>' +
            '</div>'
        );
        document.getElementById('avayaar-start').addEventListener('click', function() {
            getAudioCtx();
            startEarStage();
        });
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

    renderIntro();
})();