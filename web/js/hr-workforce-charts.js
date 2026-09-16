(function () {
    'use strict';

    function init() {
        var root = document.querySelector('[data-hr-workforce-charts]');
        if (!root || root.dataset.chartsReady || typeof ApexCharts === 'undefined') return;
        var data;
        try { data = JSON.parse(root.dataset.hrWorkforceCharts); } catch (error) { return; }
        root.dataset.chartsReady = 'true';
        var styles = getComputedStyle(root);
        function token(name) { return styles.getPropertyValue(name).trim(); }
        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }
        var colors = ['--bs-primary', '--bs-warning', '--bs-info', '--bs-success', '--bs-secondary', '--bs-danger'].map(token);
        var number = new Intl.NumberFormat('th-TH');
        var charts = [];
        function options(type) {
            return {
                chart: {
                    type: type, height: 320, fontFamily: styles.fontFamily,
                    background: token('--bs-body-bg'),
                    foreColor: token('--bs-body-color'), toolbar: { show: false },
                    animations: { enabled: false }
                },
                colors: colors,
                grid: { borderColor: token('--bs-border-color') },
                stroke: { colors: [token('--bs-body-bg')] },
                theme: { mode: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light' },
                tooltip: { custom: function (context) {
                    var isBar = type === 'bar';
                    var index = isBar ? context.dataPointIndex : context.seriesIndex;
                    var label = isBar ? ['บรรจุใหม่', 'พ้นจากหน่วยงาน'][index] : data.reasonLabels[index];
                    var count = isBar ? data.movement[index] : data.reasonValues[index];
                    return '<div class="bg-body text-body border rounded-3 p-2">' + escapeHtml(label) +
                        ' · <strong>' + number.format(count) + ' คน</strong></div>';
                } }
            };
        }
        function render(element, config, onSuccess) {
            if (!element) return;
            element.hidden = false;
            function restoreFallback() {
                element.hidden = true;
                var fallback = root.querySelector('[data-hr-chart-fallback]');
                if (fallback) fallback.hidden = false;
                var table = root.querySelector('[data-hr-reason-table]');
                if (table) table.open = true;
            }
            try {
                var chart = new ApexCharts(element, config);
                charts.push(chart);
                chart.render().then(onSuccess).catch(restoreFallback);
            } catch (error) { restoreFallback(); }
        }

        if (Array.isArray(data.movement)) {
            var bar = options('bar');
            bar.series = [{ name: 'จำนวนคน', data: data.movement }];
            bar.plotOptions = { bar: { columnWidth: '42%', distributed: true, borderRadius: 4 } };
            bar.dataLabels = {
                enabled: true, formatter: function (value) { return number.format(value) + ' คน'; },
                style: { colors: [token('--bs-emphasis-color')] },
                background: { enabled: true, foreColor: token('--bs-body-bg'), borderWidth: 0 }
            };
            bar.legend = { show: false };
            bar.xaxis = { categories: ['บรรจุใหม่', 'พ้นจากหน่วยงาน'], axisBorder: { show: false }, axisTicks: { show: false } };
            bar.yaxis = { min: 0, forceNiceScale: true, decimalsInFloat: 0, title: { text: 'จำนวน (คน)' } };
            if (Math.max.apply(null, data.movement) <= 1) {
                bar.yaxis.max = 1;
                bar.yaxis.tickAmount = 1;
            }
            render(root.querySelector('[data-hr-chart="movement"]'), bar, function () {
                root.querySelector('[data-hr-chart-fallback]').hidden = true;
            });
        }

        if (data.reasonValues.length) {
            var pie = options('donut');
            pie.chart.height = Math.max(320, 240 + data.reasonValues.length * 28);
            pie.series = data.reasonValues;
            pie.labels = data.reasonLabels;
            pie.stroke.width = 2;
            pie.dataLabels = { enabled: false };
            pie.plotOptions = { pie: { donut: { size: '66%', labels: {
                show: true,
                name: { show: false },
                value: { formatter: function (value) { return number.format(Number(value)) + ' คน'; }, color: token('--bs-body-color') },
                total: { show: true, showAlways: true, label: 'รวม', color: token('--bs-body-color'), formatter: function () {
                    return number.format(data.reasonValues.reduce(function (sum, value) { return sum + value; }, 0)) + ' คน';
                } }
            } } } };
            var total = data.reasonValues.reduce(function (sum, value) { return sum + value; }, 0);
            pie.legend = {
                position: 'bottom', horizontalAlign: 'left',
                onItemClick: { toggleDataSeries: false },
                formatter: function (label, context) {
                    var count = data.reasonValues[context.seriesIndex];
                    return escapeHtml(label) + ' · ' + number.format(count) + ' คน (' + (count / total * 100).toFixed(1) + '%)';
                }
            };
            render(root.querySelector('[data-hr-chart="reasons"]'), pie, function () {
                root.querySelector('[data-hr-reason-table]').open = false;
            });
        }

        // Rebuild on a theme switch, using the same Bootstrap tokens as the page.
        var observer = new MutationObserver(function () {
            observer.disconnect();
            charts.forEach(function (chart) { chart.destroy(); });
            delete root.dataset.chartsReady;
            init();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
