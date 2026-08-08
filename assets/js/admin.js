(function() {
    'use strict';

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.avayaar-mark-called');
        if (!btn) return;

        btn.disabled = true;
        var body = 'action=avayaar_mark_called&nonce=' + encodeURIComponent(AvayaarAdmin.nonce) +
            '&id=' + encodeURIComponent(btn.getAttribute('data-id')) +
            '&status=' + encodeURIComponent(btn.getAttribute('data-status'));

        fetch(AvayaarAdmin.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (json.success) {
                    // Remove the row instead of relabeling it — it no longer
                    // belongs on this tab, and relabeling would leave the
                    // waiting/called counts visibly stale until reload.
                    var row = btn.closest('tr');
                    if (row) row.parentNode.removeChild(row);
                } else {
                    btn.disabled = false;
                    alert('خطا در بروزرسانی وضعیت.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                alert('خطا در ارتباط با سرور.');
            });
    });
})();