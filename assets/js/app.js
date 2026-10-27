document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('mobileBackdrop');

    const closeSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        document.body.classList.remove('sidebar-open');
    };

    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open', sidebar.classList.contains('open'));
        });
    }

    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    const profileMenu = document.getElementById('profileMenu');
    const profileTrigger = document.getElementById('profileTrigger');

    const closeProfileMenu = () => {
        if (!profileMenu || !profileTrigger) return;
        profileMenu.classList.remove('open');
        profileTrigger.setAttribute('aria-expanded', 'false');
    };

    if (profileMenu && profileTrigger) {
        profileTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const isOpen = profileMenu.classList.toggle('open');
            profileTrigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        profileMenu.addEventListener('click', (event) => event.stopPropagation());
        document.addEventListener('click', closeProfileMenu);
    }

    document.querySelectorAll('.menu a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 920) closeSidebar();
        });
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeSidebar();
            closeProfileMenu();
        }
    });

    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm') || 'Lanjutkan tindakan ini?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
});

// Live IoT monitor untuk melihat request Wokwi/ESP32 masuk ke web.
document.addEventListener('DOMContentLoaded', () => {
    const config = window.IOT_MONITOR_CONFIG;
    if (!config) return;

    const $ = (id) => document.getElementById(id);
    const apiStatus = $('apiStatus');
    const apiResponse = $('apiResponse');
    const liveLogsBody = $('liveLogsBody');

    const setStatus = (text, type) => {
        if (!apiStatus) return;
        apiStatus.textContent = text;
        apiStatus.className = 'badge badge-' + type;
    };

    const showResponse = (data) => {
        if (!apiResponse) return;
        apiResponse.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
    };

    const copyInput = async (inputId) => {
        const input = $(inputId);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            await navigator.clipboard.writeText(input.value);
        } catch (e) {
            document.execCommand('copy');
        }
    };

    const callJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                'ngrok-skip-browser-warning': 'true',
                'Accept': 'application/json'
            },
            cache: 'no-store'
        });
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Response bukan JSON. Isi awal response: ' + text.slice(0, 180));
        }
    };

    const testScan = async (uid) => {
        try {
            setStatus('Mengirim', 'warning');
            const url = config.apiUrl + '?uid=' + encodeURIComponent(uid) + '&gate_id=1&area_id=1&_=' + Date.now();
            const data = await callJson(url);
            showResponse(data);
            setStatus(data.success ? 'API OK' : 'API Menolak', data.success ? 'success' : 'danger');
            await refreshAll();
        } catch (error) {
            showResponse(error.message);
            setStatus('API Error', 'danger');
        }
    };

    const pingApi = async () => {
        try {
            setStatus('Mengecek', 'warning');
            const data = await callJson(config.pingUrl + '?_=' + Date.now());
            showResponse(data);
            setStatus('API Online', 'success');
        } catch (error) {
            showResponse(error.message);
            setStatus('API Error', 'danger');
        }
    };

    const renderLogs = (rows) => {
        if (!liveLogsBody) return;
        if (!rows || rows.length === 0) {
            liveLogsBody.innerHTML = '<tr><td colspan="8" class="empty">Belum ada log scan.</td></tr>';
            return;
        }
        liveLogsBody.innerHTML = rows.map((row) => {
            const statusClass = row.status_akses === 'Diterima' ? 'badge-success' : 'badge-danger';
            return '<tr>' +
                '<td>' + escapeHtml(formatDateTime(row.waktu_akses)) + '</td>' +
                '<td><b>' + escapeHtml(row.uid_kartu || '-') + '</b></td>' +
                '<td>' + escapeHtml(row.nama_user || '-') + '<br><small>' + escapeHtml(row.kode_user || '-') + '</small></td>' +
                '<td>' + escapeHtml(row.nomor_polisi || '-') + '</td>' +
                '<td>' + escapeHtml(row.jenis_akses || '-') + '</td>' +
                '<td><span class="badge ' + statusClass + '">' + escapeHtml(row.status_akses || '-') + '</span></td>' +
                '<td>' + escapeHtml(row.nama_gate || '-') + '</td>' +
                '<td>' + escapeHtml(row.alasan || '-') + '</td>' +
            '</tr>';
        }).join('');
    };

    const refreshStats = async () => {
        const data = await callJson(config.statsUrl + '?_=' + Date.now());
        if (!data.success) return;
        if ($('statLogs')) $('statLogs').textContent = data.data.logs;
        if ($('statAccepted')) $('statAccepted').textContent = data.data.accepted_today;
        if ($('statRejected')) $('statRejected').textContent = data.data.rejected_today;
    };

    const refreshLogs = async () => {
        const data = await callJson(config.latestUrl + '&_=' + Date.now());
        if (data.success) renderLogs(data.data);
    };

    const refreshAll = async () => {
        try {
            await Promise.all([refreshStats(), refreshLogs()]);
        } catch (error) {
            setStatus('Refresh Error', 'danger');
        }
    };

    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatDateTime = (value) => {
        if (!value) return '-';
        return String(value).replace('T', ' ').slice(0, 19);
    };

    if ($('copyApiBtn')) $('copyApiBtn').addEventListener('click', () => copyInput('apiUrlInput'));
    if ($('copyTestBtn')) $('copyTestBtn').addEventListener('click', () => copyInput('testUrlInput'));
    if ($('pingBtn')) $('pingBtn').addEventListener('click', pingApi);
    if ($('testValidBtn')) $('testValidBtn').addEventListener('click', () => testScan(config.validUid));
    if ($('testInvalidBtn')) $('testInvalidBtn').addEventListener('click', () => testScan(config.invalidUid));

    pingApi();
    refreshAll();
    setInterval(refreshAll, 2500);
});
