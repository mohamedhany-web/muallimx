{{--
  مزامنة مشهد Excalidraw لغرفة Classroom (معلم ↔ طلاب).
  الاستخدام: استدعاء window.MxClassroomWbSync.attach({...}) بعد توفر الـ API.
--}}
<script>
(function () {
    if (window.MxClassroomWbSync) return;

    function elementsFingerprint(elements) {
        try {
            return JSON.stringify(elements || []);
        } catch (e) {
            return '';
        }
    }

    window.MxClassroomWbSync = {
        attach: function (opts) {
            opts = opts || {};
            var getApi = opts.getApi;
            var getUrl = opts.getUrl;
            var postUrl = opts.postUrl;
            var csrfToken = opts.csrfToken || '';
            var getExtraBody = typeof opts.getExtraBody === 'function' ? opts.getExtraBody : function () { return {}; };
            var canWrite = typeof opts.canWrite === 'function' ? opts.canWrite : function () { return true; };
            var pollMs = opts.pollMs || 1600;
            var pushDebounceMs = opts.pushDebounceMs || 700;
            var onDenied = typeof opts.onDenied === 'function' ? opts.onDenied : null;

            var localVersion = 0;
            var lastAppliedFp = '';
            var lastPushedFp = '';
            var applyingRemote = false;
            var pushTimer = null;
            var pollTimer = null;
            var stopped = false;

            function headers() {
                return {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                };
            }

            function applyRemote(elements, version) {
                var api = typeof getApi === 'function' ? getApi() : null;
                if (!api || typeof api.updateScene !== 'function') return;
                var fp = elementsFingerprint(elements);
                if (fp === lastAppliedFp && version <= localVersion) return;
                applyingRemote = true;
                try {
                    api.updateScene({
                        elements: Array.isArray(elements) ? elements : [],
                        commitToHistory: false
                    });
                    lastAppliedFp = fp;
                    lastPushedFp = fp;
                    localVersion = Math.max(localVersion, version || 0);
                } catch (e) {
                    console.warn('[MxClassroomWbSync] applyRemote', e);
                }
                setTimeout(function () { applyingRemote = false; }, 80);
            }

            function pull() {
                if (stopped || !getUrl) return Promise.resolve();
                var url = getUrl;
                var extra = getExtraBody() || {};
                if (extra.token) {
                    url += (url.indexOf('?') >= 0 ? '&' : '?') + 'token=' + encodeURIComponent(extra.token);
                }
                return fetch(url, { headers: headers(), credentials: 'same-origin' })
                    .then(function (r) {
                        if (r.status === 422 || r.status === 403) {
                            return r.json().then(function (d) {
                                if (onDenied) onDenied(d || {});
                                return null;
                            }).catch(function () {
                                if (onDenied) onDenied({});
                                return null;
                            });
                        }
                        return r.ok ? r.json() : null;
                    })
                    .then(function (data) {
                        if (!data || !data.ok) return;
                        var ver = parseInt(data.version || 0, 10) || 0;
                        if (ver > localVersion) {
                            applyRemote(data.elements || [], ver);
                        }
                    })
                    .catch(function () {});
            }

            function pushNow() {
                if (stopped || !postUrl || !canWrite()) return;
                var api = typeof getApi === 'function' ? getApi() : null;
                if (!api || typeof api.getSceneElements !== 'function') return;
                if (applyingRemote) return;
                var elements = api.getSceneElements();
                var fp = elementsFingerprint(elements);
                if (fp === lastPushedFp) return;
                var body = Object.assign({ elements: elements }, getExtraBody() || {});
                fetch(postUrl, {
                    method: 'POST',
                    headers: headers(),
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                }).then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data || !data.ok) return;
                        lastPushedFp = fp;
                        lastAppliedFp = fp;
                        localVersion = Math.max(localVersion, parseInt(data.version || 0, 10) || 0);
                    }).catch(function () {});
            }

            function schedulePush() {
                if (stopped || applyingRemote || !canWrite()) return;
                if (pushTimer) clearTimeout(pushTimer);
                pushTimer = setTimeout(function () {
                    pushTimer = null;
                    pushNow();
                }, pushDebounceMs);
            }

            function onLocalChange() {
                if (applyingRemote) return;
                schedulePush();
            }

            function start() {
                stopped = false;
                pull();
                if (pollTimer) clearInterval(pollTimer);
                pollTimer = setInterval(pull, pollMs);
            }

            function stop() {
                stopped = true;
                if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
                if (pushTimer) { clearTimeout(pushTimer); pushTimer = null; }
            }

            return {
                start: start,
                stop: stop,
                pull: pull,
                pushNow: pushNow,
                schedulePush: schedulePush,
                onLocalChange: onLocalChange,
                getLocalVersion: function () { return localVersion; },
                setLocalVersion: function (v) { localVersion = Math.max(0, parseInt(v || 0, 10) || 0); }
            };
        }
    };
})();
</script>
