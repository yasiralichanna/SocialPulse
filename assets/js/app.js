/* ═══════════════════════════════════════════
   SocialPulse — Frontend (Real API calls)
   ═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    // ─── Toast System ───
    const showToast = (msg, type = 'info') => {
        const c = document.getElementById('toastContainer');
        if (!c) return;
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
        t.innerHTML = `<i class="fas fa-${icons[type] || icons.info}"></i> ${msg}`;
        c.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(40px)'; setTimeout(() => t.remove(), 300); }, 4000);
    };

    // ─── Sidebar Toggle ───
    const sidebar = document.getElementById('sidebar');
    document.getElementById('menuToggle')?.addEventListener('click', () => sidebar?.classList.toggle('open'));

    // ─── Theme Toggle ───
    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn) {
        const saved = localStorage.getItem('sp_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', saved);
        themeBtn.innerHTML = saved === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        themeBtn.addEventListener('click', () => {
            const cur = document.documentElement.getAttribute('data-theme');
            const nxt = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', nxt);
            localStorage.setItem('sp_theme', nxt);
            themeBtn.innerHTML = nxt === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

    // ─── Helper: Get selected platforms ───
    const getSelectedPlatforms = () => {
        return Array.from(document.querySelectorAll('.platform-check.active')).map(el => el.dataset.platform);
    };

    // ─── Helper: API call ───
    const api = async (url, method = 'GET', body = null) => {
        const opts = { method, headers: { 'Content-Type': 'application/json' } };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        return res.json();
    };

    // ═══════════════════════════════════════
    //  COMPOSER PAGE — Real post creation
    // ═══════════════════════════════════════
    const postContent = document.getElementById('postContent');
    const charCount = document.getElementById('charCount');
    const previewContent = document.getElementById('previewContent');

    if (postContent) {
        // Live preview & char count
        postContent.addEventListener('input', () => {
            const text = postContent.value;
            charCount.textContent = text.length;
            charCount.style.color = text.length > 2000 ? 'var(--danger)' : 'var(--text-muted)';
            previewContent.innerHTML = text ? text.replace(/\n/g, '<br>') : 'Your post preview will appear here...';
        });

        // Platform toggle
        document.querySelectorAll('.platform-check').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.classList.contains('active') && document.querySelectorAll('.platform-check.active').length === 1) {
                    return showToast('Select at least one platform', 'error');
                }
                btn.classList.toggle('active');
            });
        });

        // Preview tabs
        document.querySelectorAll('.preview-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.preview-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });

        // Emoji & Hashtag
        document.getElementById('addEmoji')?.addEventListener('click', () => { postContent.value += '🚀'; postContent.dispatchEvent(new Event('input')); });
        document.getElementById('addHashtag')?.addEventListener('click', () => { postContent.value += ' #'; postContent.focus(); });

        // ─── PUBLISH NOW (real) ───
        document.getElementById('publishNow')?.addEventListener('click', async () => {
            const content = postContent.value.trim();
            if (!content) return showToast('Write something first!', 'error');
            const platforms = getSelectedPlatforms();

            showToast('Publishing...', 'info');
            const res = await api('api/posts.php', 'POST', { content, platforms, status: 'published' });
            if (res.success) {
                showToast('Post published successfully!', 'success');
                setTimeout(() => window.location.href = 'index.php', 1200);
            } else {
                showToast(res.error || 'Failed to publish', 'error');
            }
        });

        // ─── SAVE DRAFT (real) ───
        document.getElementById('saveDraft')?.addEventListener('click', async () => {
            const content = postContent.value.trim();
            if (!content) return showToast('Write something first!', 'error');
            const platforms = getSelectedPlatforms();

            const res = await api('api/posts.php', 'POST', { content, platforms, status: 'draft' });
            if (res.success) {
                showToast('Draft saved!', 'success');
            } else {
                showToast(res.error || 'Failed to save', 'error');
            }
        });

        // ─── SCHEDULE (real) ───
        const schedModal = document.getElementById('scheduleModal');
        document.getElementById('schedulePostBtn')?.addEventListener('click', () => {
            if (!postContent.value.trim()) return showToast('Write something first!', 'error');
            schedModal.classList.add('active');
        });
        document.getElementById('closeScheduleModal')?.addEventListener('click', () => schedModal.classList.remove('active'));
        document.getElementById('cancelSchedule')?.addEventListener('click', () => schedModal.classList.remove('active'));

        document.getElementById('confirmSchedule')?.addEventListener('click', async () => {
            const date = document.getElementById('schedDate').value;
            const time = document.getElementById('schedTime').value;
            if (!date || !time) return showToast('Pick a date and time', 'error');

            const content = postContent.value.trim();
            const platforms = getSelectedPlatforms();
            const repeat = document.getElementById('schedRepeat').value;
            const timezone = document.getElementById('schedTimezone').value;
            const scheduled_at = `${date} ${time}:00`;

            const res = await api('api/posts.php', 'POST', { content, platforms, status: 'scheduled', scheduled_at, repeat_type: repeat, timezone });
            if (res.success) {
                schedModal.classList.remove('active');
                showToast(`Scheduled for ${date} at ${time}!`, 'success');
                setTimeout(() => window.location.href = 'scheduler.php', 1200);
            } else {
                showToast(res.error || 'Failed to schedule', 'error');
            }
        });
    }

    // ═══════════════════════════════════════
    //  SCHEDULER / CALENDAR PAGE
    // ═══════════════════════════════════════
    const calGrid = document.getElementById('calendarGrid');
    if (calGrid && window.calendarPosts) {
        const calLabel = document.getElementById('calMonth');
        let curDate = new Date();

        const render = () => {
            calGrid.innerHTML = '';
            ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].forEach(d => calGrid.innerHTML += `<div class="cal-header">${d}</div>`);
            const y = curDate.getFullYear(), m = curDate.getMonth();
            calLabel.textContent = new Date(y, m).toLocaleString('default', { month: 'long', year: 'numeric' });
            const first = new Date(y, m, 1).getDay();
            const days = new Date(y, m + 1, 0).getDate();
            const today = new Date();

            for (let i = 0; i < first; i++) calGrid.innerHTML += `<div class="cal-day other-month"></div>`;
            for (let i = 1; i <= days; i++) {
                const isToday = (i === today.getDate() && m === today.getMonth() && y === today.getFullYear()) ? 'today' : '';
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                const dp = window.calendarPosts.filter(p => p.date && p.date.startsWith(ds));
                let dots = '';
                dp.forEach(p => { const pl = p.platforms[0] || 'facebook'; dots += `<div class="cal-dot ${pl}" title="${p.content}">${p.content}</div>`; });
                calGrid.innerHTML += `<div class="cal-day ${isToday}"><div class="day-num">${i}</div><div class="day-posts">${dots}</div></div>`;
            }
        };
        render();
        document.getElementById('calPrev')?.addEventListener('click', () => { curDate.setMonth(curDate.getMonth() - 1); render(); });
        document.getElementById('calNext')?.addEventListener('click', () => { curDate.setMonth(curDate.getMonth() + 1); render(); });

        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const isMonth = btn.dataset.view === 'month';
                calGrid.style.display = isMonth ? 'grid' : 'none';
                document.getElementById('listView').style.display = isMonth ? 'none' : 'block';
                document.getElementById('calPrev').style.display = isMonth ? '' : 'none';
                document.getElementById('calNext').style.display = isMonth ? '' : 'none';
                document.getElementById('calMonth').style.display = isMonth ? '' : 'none';
            });
        });

        // Cancel scheduled post (real)
        document.querySelectorAll('.cancel-post').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Cancel this scheduled post?')) return;
                const id = btn.dataset.id;
                const res = await api(`api/posts.php?id=${id}`, 'DELETE');
                if (res.success) {
                    btn.closest('tr').remove();
                    showToast('Post cancelled', 'success');
                }
            });
        });
    }

    // ═══════════════════════════════════════
    //  REPORTS PAGE — Real CSV export
    // ═══════════════════════════════════════
    document.getElementById('exportCSV')?.addEventListener('click', () => {
        const type = document.querySelector('[name="type"]')?.value || 'engagement';
        const range = document.querySelector('[name="range"]')?.value || '30d';
        const platform = document.querySelector('[name="platform"]')?.value || 'all';
        window.location.href = `api/reports.php?action=export_csv&type=${type}&range=${range}&platform=${platform}`;
        showToast('Downloading CSV...', 'success');
    });

    // ═══════════════════════════════════════
    //  DASHBOARD & ANALYTICS — Canvas Charts
    // ═══════════════════════════════════════
    const drawLineChart = (canvas, datasets) => {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const w = canvas.parentElement.clientWidth || 500;
        const h = parseInt(canvas.getAttribute('height')) || 280;
        canvas.width = w; canvas.height = h;
        const pad = { top: 20, right: 20, bottom: 30, left: 50 };
        const cw = w - pad.left - pad.right, ch = h - pad.top - pad.bottom;

        // Background
        const bg = getComputedStyle(document.documentElement).getPropertyValue('--surface').trim();
        const textCol = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
        ctx.fillStyle = 'transparent'; ctx.fillRect(0, 0, w, h);

        // Grid lines
        ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || 'rgba(255,255,255,0.06)';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 5; i++) {
            const y = pad.top + (ch / 5) * i;
            ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(w - pad.right, y); ctx.stroke();
        }

        datasets.forEach(ds => {
            if (!ds.values.length) return;
            const max = Math.max(...ds.values) || 1;
            const min = Math.min(...ds.values);
            const range = max - min || 1;
            ctx.beginPath();
            ctx.strokeStyle = ds.color;
            ctx.lineWidth = 2.5;
            ctx.lineJoin = 'round';
            ds.values.forEach((v, i) => {
                const x = pad.left + (i / (ds.values.length - 1 || 1)) * cw;
                const y = pad.top + ch - ((v - min) / range) * ch;
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            });
            ctx.stroke();

            // Glow
            ctx.beginPath();
            ctx.fillStyle = ds.color.replace(')', ',0.08)').replace('rgb', 'rgba');
            ds.values.forEach((v, i) => {
                const x = pad.left + (i / (ds.values.length - 1 || 1)) * cw;
                const y = pad.top + ch - ((v - min) / range) * ch;
                i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            });
            ctx.lineTo(pad.left + cw, pad.top + ch);
            ctx.lineTo(pad.left, pad.top + ch);
            ctx.closePath(); ctx.fill();
        });

        // Y-axis labels
        if (datasets[0]?.values.length) {
            const max = Math.max(...datasets[0].values) || 1;
            ctx.fillStyle = textCol; ctx.font = '10px Inter'; ctx.textAlign = 'right';
            for (let i = 0; i <= 5; i++) {
                const val = Math.round((max / 5) * (5 - i));
                ctx.fillText(val.toLocaleString(), pad.left - 8, pad.top + (ch / 5) * i + 4);
            }
        }
    };

    const drawDonut = (canvas, segments) => {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const w = canvas.parentElement.clientWidth || 200;
        const h = parseInt(canvas.getAttribute('height')) || 200;
        canvas.width = w; canvas.height = h;
        const cx = w / 2, cy = h / 2, r = Math.min(w, h) / 2.5;
        const total = segments.reduce((s, seg) => s + seg.value, 0) || 1;
        let angle = -Math.PI / 2;

        segments.forEach(seg => {
            const slice = (seg.value / total) * Math.PI * 2;
            ctx.beginPath(); ctx.arc(cx, cy, r, angle, angle + slice);
            ctx.arc(cx, cy, r * 0.6, angle + slice, angle, true);
            ctx.closePath();
            ctx.fillStyle = seg.color; ctx.fill();
            angle += slice;
        });

        // Center text
        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim();
        ctx.font = 'bold 18px Inter'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.fillText(total.toLocaleString(), cx, cy - 8);
        ctx.font = '11px Inter';
        ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
        ctx.fillText('Followers', cx, cy + 12);
    };

    const drawBarChart = (canvas, data) => {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const w = canvas.parentElement.clientWidth || 400;
        const h = parseInt(canvas.getAttribute('height')) || 250;
        canvas.width = w; canvas.height = h;
        const pad = { top: 20, right: 20, bottom: 40, left: 50 };
        const cw = w - pad.left - pad.right, ch = h - pad.top - pad.bottom;
        const max = Math.max(...data.map(d => d.value)) || 1;
        const barW = (cw / data.length) * 0.6;
        const gap = (cw / data.length) * 0.4;

        data.forEach((d, i) => {
            const x = pad.left + i * (barW + gap) + gap / 2;
            const barH = (d.value / max) * ch;
            const y = pad.top + ch - barH;

            // Gradient bar
            const grad = ctx.createLinearGradient(x, y, x, y + barH);
            grad.addColorStop(0, d.color);
            grad.addColorStop(1, d.color.replace(')', ',0.3)').replace('rgb', 'rgba'));
            ctx.fillStyle = grad;
            ctx.beginPath();
            ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]);
            ctx.fill();

            // Label
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim();
            ctx.font = '10px Inter'; ctx.textAlign = 'center';
            ctx.fillText(d.label, x + barW / 2, h - pad.bottom + 15);
            ctx.fillText(d.value + '%', x + barW / 2, y - 6);
        });
    };

    // Fetch real chart data and render
    const initCharts = async () => {
        // Use window.analyticsData if available (analytics.php injects it), else fetch
        let chartData = window.analyticsData || null;

        if (!chartData) {
            try {
                const res = await fetch('api/reports.php?action=chart_data&range=30d');
                const json = await res.json();
                chartData = [];
                Object.entries(json.data || {}).forEach(([platform, rows]) => {
                    rows.forEach(r => chartData.push(r));
                });
            } catch (e) { chartData = []; }
        }

        // Group by platform
        const grouped = {};
        (Array.isArray(chartData) ? chartData : []).forEach(row => {
            if (!grouped[row.platform]) grouped[row.platform] = [];
            grouped[row.platform].push(row);
        });

        const colors = { facebook: 'rgb(24, 119, 242)', twitter: 'rgb(29, 161, 242)', instagram: 'rgb(228, 64, 95)' };

        // Main engagement chart (dashboard)
        drawLineChart(document.getElementById('mainChart'), Object.entries(grouped).map(([p, rows]) => ({
            color: colors[p] || '#6c5ce7',
            values: rows.map(r => parseFloat(r.engagement_rate))
        })));

        // Analytics line chart
        drawLineChart(document.getElementById('analyticsLineChart'), Object.entries(grouped).map(([p, rows]) => ({
            color: colors[p] || '#6c5ce7',
            values: rows.map(r => parseInt(r.total_reach))
        })));

        // Donut chart
        const followerTotals = Object.entries(grouped).map(([p, rows]) => ({
            color: colors[p] || '#6c5ce7',
            value: rows.length ? parseInt(rows[rows.length - 1].followers) : 0
        }));
        drawDonut(document.getElementById('donutChart'), followerTotals);

        // Demographics bar chart
        drawBarChart(document.getElementById('demographicsChart'), [
            { label: '18-24', value: 32, color: 'rgb(108,92,231)' },
            { label: '25-34', value: 45, color: 'rgb(0,206,201)' },
            { label: '35-44', value: 28, color: 'rgb(228,64,95)' },
            { label: '45-54', value: 18, color: 'rgb(29,161,242)' },
            { label: '55+',   value: 12, color: 'rgb(253,203,110)' },
        ]);
    };

    setTimeout(initCharts, 50);
    window.addEventListener('resize', () => setTimeout(initCharts, 100));

    // ─── Heatmap (analytics page) ───
    const heatmapGrid = document.getElementById('heatmapGrid');
    if (heatmapGrid) {
        const days = ['', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const hours = ['6am', '9am', '12pm', '3pm', '6pm', '9pm'];
        // Header row
        hours.forEach(h => heatmapGrid.innerHTML += `<div class="heatmap-label">${h}</div>`);
        // Data cells (6 hours x 7 days = 42)
        for (let i = 0; i < 42; i++) {
            const intensity = Math.random();
            const alpha = (0.1 + intensity * 0.9).toFixed(2);
            heatmapGrid.innerHTML += `<div class="heatmap-cell" style="background:rgba(108,92,231,${alpha})" title="Engagement: ${Math.round(intensity*100)}%">${intensity > 0.75 ? '🔥' : ''}</div>`;
        }
    }
});
