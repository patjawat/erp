(function () {
    'use strict';
    function init() {
        document.querySelectorAll('[data-engagement-results]').forEach(function (root) {
            if (root.dataset.initialized || typeof ApexCharts === 'undefined') return;
            root.dataset.initialized = '1';
            var data = JSON.parse(root.dataset.engagementResults);
            var style = getComputedStyle(root);
            function token(name) { return style.getPropertyValue(name).trim(); }
            function escape(value) { var el = document.createElement('span'); el.textContent = String(value); return el.innerHTML; }
            var charts = [];
            function render(kind, labels, values, max) {
                var target = root.querySelector('[data-engagement-chart="' + kind + '"]');
                if (!target) return;
                target.hidden = false;
                var isBar = kind === 'dimensions';
                var chart = new ApexCharts(target, {
                    chart: { type: isBar ? 'bar' : 'line', height: isBar ? Math.max(260, labels.length * 48 + 60) : 280, background: token('--bs-body-bg'), foreColor: token('--bs-body-color'), fontFamily: style.fontFamily, animations: { enabled: false }, toolbar: { show: false } },
                    series: [{ name: isBar ? 'ค่าเฉลี่ย' : 'ดัชนี', data: values }],
                    colors: [token('--bs-primary')],
                    plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                    xaxis: isBar ? { categories: labels, min: 0, max: max } : { categories: labels },
                    yaxis: isBar ? { labels: { maxWidth: 130 } } : { min: 0, max: max },
                    dataLabels: { enabled: false },
                    stroke: { width: isBar ? 0 : 3, curve: 'straight' },
                    markers: { size: isBar ? 0 : 4 },
                    grid: { borderColor: token('--bs-border-color') },
                    tooltip: { custom: function (ctx) { var i = ctx.dataPointIndex; return '<div class="bg-body text-body border rounded p-2">' + escape(labels[i]) + ' · ' + (values[i] === null ? 'ปกปิด / ไม่มีข้อมูล' : Number(values[i]).toFixed(1)) + '</div>'; } }
                });
                charts.push(chart);
                chart.render().catch(function () { target.hidden = true; });
            }
            render('dimensions', data.labels, data.values, 5);
            render('trend', data.trendLabels, data.trendValues, 100);
            var observer = new MutationObserver(function () {
                observer.disconnect(); charts.forEach(function (c) { c.destroy(); }); delete root.dataset.initialized; init();
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
