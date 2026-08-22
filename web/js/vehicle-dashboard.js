/**
 * Vehicle dashboard charts.
 *
 * แต่ละกราฟประกาศตัวเองด้วย markup ไม่ใช่ inline script:
 *
 *   <div class="vd-chart" data-vehicle-chart>
 *       <script type="application/json">{ "kind": "bar", ... }</script>
 *   </div>
 *
 * ทำแบบนี้เพื่อให้ (1) สีอ่านจาก Bootstrap CSS variables จึงสลับ light/dark ได้จริง
 * (2) Pjax โหลดส่วนใหม่มาแล้ว mount ซ้ำได้ (3) ไม่ต้อง copy config ApexCharts ทุกไฟล์
 */
(function (window, document) {
    'use strict';

    // Pjax อาจส่งไฟล์นี้กลับมาอีกรอบ — บูตครั้งเดียว ที่เหลือแค่ mount ใหม่
    if (window.VehicleDashboard) {
        window.VehicleDashboard.mountAll();
        return;
    }

    var mounted = [];

    function reducedMotion() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function cssVar(name, fallback) {
        var value = getComputedStyle(document.documentElement).getPropertyValue(name);
        value = (value || '').trim();
        return value !== '' ? value : fallback;
    }

    function palette() {
        return {
            isDark: document.documentElement.getAttribute('data-bs-theme') === 'dark',
            ink: cssVar('--bs-body-color', '#212529'),
            muted: cssVar('--bs-secondary-color', '#6c757d'),
            line: cssVar('--bs-border-color', '#dee2e6'),
            track: cssVar('--bs-tertiary-bg', '#f8f9fa')
        };
    }

    /** ชื่อสีใน JSON อ้าง Bootstrap variable เสมอ ไม่มี hex ใน config */
    function resolveColors(names) {
        var fallbacks = {
            '--bs-primary': '#0d6efd',
            '--bs-teal': '#20c997',
            '--bs-orange': '#fd7e14',
            '--bs-indigo': '#6610f2',
            '--bs-cyan': '#0dcaf0',
            '--bs-pink': '#d63384',
            '--bs-gray-500': '#adb5bd'
        };
        return (names || ['--bs-primary']).map(function (name) {
            return cssVar(name, fallbacks[name] || '#0d6efd');
        });
    }

    function formatNumber(value, digits) {
        return Number(value || 0).toLocaleString('th-TH', {
            minimumFractionDigits: digits || 0,
            maximumFractionDigits: digits || 0
        });
    }

    function baseOptions(config, p) {
        var unit = config.unit || '';
        var digits = config.digits || 0;

        return {
            chart: {
                type: config.kind === 'area' ? 'area' : 'bar',
                height: config.height || 320,
                stacked: !!config.stacked,
                fontFamily: 'inherit',
                foreColor: p.muted,
                parentHeightOffset: 0,
                toolbar: { show: false },
                animations: {
                    enabled: !reducedMotion(),
                    easing: 'easeout',
                    speed: 240,
                    animateGradually: { enabled: false }
                }
            },
            colors: resolveColors(config.colors),
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    borderRadiusApplication: 'end',
                    columnWidth: config.stacked ? '55%' : '65%'
                }
            },
            dataLabels: { enabled: false },
            stroke: config.kind === 'area'
                ? { width: 2, curve: 'smooth' }
                : { show: true, width: 2, colors: ['transparent'] },
            fill: config.kind === 'area'
                ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } }
                : { opacity: 1 },
            grid: {
                borderColor: p.line,
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                padding: { top: 0, right: 8, left: 4, bottom: 0 }
            },
            legend: {
                show: (config.series || []).length > 1,
                position: 'top',
                horizontalAlign: 'left',
                offsetX: -8,
                fontSize: '13px',
                labels: { colors: p.ink },
                markers: { width: 10, height: 10, radius: 3 },
                itemMargin: { horizontal: 10, vertical: 2 }
            },
            xaxis: {
                categories: config.categories || [],
                tickPlacement: 'on',
                axisTicks: { show: false },
                axisBorder: { show: false },
                labels: {
                    // ชื่อเดือนไทยยาวกว่าอังกฤษ ในการ์ดแคบต้องยอมเอียงแทนให้ตัวอักษรชนกัน
                    rotate: -45,
                    rotateAlways: false,
                    hideOverlappingLabels: true,
                    trim: false,
                    style: { colors: p.muted, fontSize: '12px' }
                }
            },
            yaxis: {
                tickAmount: 4,
                labels: {
                    style: { colors: p.muted, fontSize: '12px' },
                    formatter: function (value) { return formatNumber(value, 0); }
                }
            },
            tooltip: {
                theme: p.isDark ? 'dark' : 'light',
                y: {
                    formatter: function (value) {
                        return formatNumber(value, digits) + (unit ? ' ' + unit : '');
                    }
                }
            },
            noData: { text: 'ไม่มีข้อมูล', style: { color: p.muted, fontSize: '13px' } },
            series: config.series || []
        };
    }

    function readConfig(host) {
        var holder = host.querySelector('script[type="application/json"]');
        if (!holder) {
            return null;
        }
        try {
            return JSON.parse(holder.textContent);
        } catch (e) {
            return null;
        }
    }

    function seriesTotal(series) {
        return (series || []).reduce(function (sum, s) {
            return sum + (s.data || []).reduce(function (a, b) { return a + Number(b || 0); }, 0);
        }, 0);
    }

    function render(host, config) {
        var target = host.querySelector('[data-vehicle-chart-canvas]');
        var empty = host.querySelector('[data-vehicle-chart-empty]');
        if (!target) {
            return null;
        }

        if (seriesTotal(config.series) === 0) {
            target.classList.add('d-none');
            if (empty) { empty.classList.remove('d-none'); }
            return null;
        }

        target.classList.remove('d-none');
        if (empty) { empty.classList.add('d-none'); }

        var chart = new window.ApexCharts(target, baseOptions(config, palette()));
        chart.render();
        return chart;
    }

    function destroyAll() {
        mounted.forEach(function (item) {
            if (item.chart) {
                item.chart.destroy();
            }
        });
        mounted = [];
    }

    function applyGroup(config, key) {
        var group = (config.groups || {})[key];
        if (!group) {
            return false;
        }
        config.series = group.series;
        config.colors = group.colors || config.colors;

        return true;
    }

    function mountAll() {
        if (!window.ApexCharts) {
            return;
        }
        destroyAll();

        Array.prototype.forEach.call(
            document.querySelectorAll('[data-vehicle-chart]'),
            function (host) {
                var config = readConfig(host);
                if (!config) {
                    return;
                }
                // จำกลุ่มที่ผู้ใช้เลือกไว้ เพื่อไม่ให้เด้งกลับตอน re-mount (เปลี่ยนธีม / Pjax)
                if (config.groups) {
                    applyGroup(config, host.dataset.activeGroup || config.defaultGroup);
                }
                mounted.push({ host: host, config: config, chart: render(host, config) });
            }
        );
    }

    /**
     * ปุ่มสลับชุดข้อมูลในกราฟเดียว (เช่น รถทั่วไป / รถฉุกเฉิน)
     *
     * ผูกแบบ delegation ครั้งเดียวที่ document เพราะ mountAll ถูกเรียกซ้ำได้
     * (เปลี่ยนธีม / Pjax) การผูกตรงปุ่มจะได้ listener ซ้อนกันหลายชั้น
     */
    function bindGroupSwitch() {
        document.addEventListener('click', function (event) {
            var button = event.target.closest && event.target.closest('[data-vehicle-chart-group]');
            if (!button) {
                return;
            }
            var card = button.closest('.card');
            var host = card ? card.querySelector('[data-vehicle-chart]') : null;
            var item = null;
            for (var i = 0; i < mounted.length; i++) {
                if (mounted[i].host === host) {
                    item = mounted[i];
                    break;
                }
            }
            if (!item) {
                return;
            }

            var key = button.getAttribute('data-vehicle-chart-group');
            if (!applyGroup(item.config, key)) {
                return;
            }
            host.dataset.activeGroup = key;

            Array.prototype.forEach.call(
                card.querySelectorAll('[data-vehicle-chart-group]'),
                function (other) {
                    var isActive = other === button;
                    other.classList.toggle('active', isActive);
                    other.setAttribute('aria-selected', isActive ? 'true' : 'false');
                }
            );

            if (item.chart) {
                item.chart.destroy();
                item.chart = null;
            }
            item.chart = render(host, item.config);
        });
    }

    function watchTheme() {
        if (!window.MutationObserver) {
            return;
        }
        var timer = null;
        new MutationObserver(function () {
            window.clearTimeout(timer);
            timer = window.setTimeout(mountAll, 60);
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme']
        });
    }

    function boot() {
        mountAll();
        bindGroupSwitch();
        watchTheme();
        if (window.jQuery) {
            window.jQuery(document).on('pjax:end', mountAll);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    window.VehicleDashboard = { mountAll: mountAll };
})(window, document);
