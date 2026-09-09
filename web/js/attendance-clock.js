(function (window, $) {
    'use strict';
    window.AttendanceClock = {
        mount: function (id, config) {
            var form = document.getElementById(id);
            if (!form || form.dataset.mounted) return;
            form.dataset.mounted = '1';
            var $form = $(form), busy = false, saved = false, ready = false, position = null, scanner = null, scanning = false;
            var requestId = window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
            function role(name) { return $form.find('[data-role="' + name + '"]'); }
            function value(name) { return $form.find('[name="' + name + '"]').val(); }
            function selected(name) { return $form.find('[name="' + name + '"]:checked').val(); }
            function message(text, ok) {
                role('result').removeClass('d-none alert-success alert-danger').addClass(ok ? 'alert-success' : 'alert-danger').text(text).trigger('focus');
            }
            function state() {
                role('submit').prop('disabled', busy || saved || !ready || !position).text(busy ? 'กำลังบันทึก...' : selected('check_type') === 'out' ? 'บันทึกเวลาออก' : 'บันทึกเวลาเข้า');
            }
            function locate() {
                position = null;
                state();
                role('gps-refresh').prop('disabled', true);
                role('gps').text('กำลังขอตำแหน่ง GPS...');
                return new Promise(function (resolve, reject) {
                    if (!navigator.geolocation) { reject(new Error('เบราว์เซอร์นี้ไม่รองรับ GPS')); return; }
                    navigator.geolocation.getCurrentPosition(function (p) {
                        position = p.coords;
                        role('gps').text('ได้รับตำแหน่งแล้ว (ความแม่นยำประมาณ ' + Math.round(p.coords.accuracy) + ' เมตร)');
                        resolve(position);
                    }, function (error) {
                        reject(new Error(error.code === 1 ? 'ยังไม่อนุญาต GPS กรุณาเปิดสิทธิ์ตำแหน่งของเว็บไซต์แล้วลองใหม่' : 'หาตำแหน่งไม่ได้ กรุณาเปิด GPS แล้วกดตรวจตำแหน่งอีกครั้ง'));
                    }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
                }).catch(function (error) { role('gps').text(error.message); throw error; }).finally(function () { role('gps-refresh').prop('disabled', false); state(); });
            }
            function loadShifts() {
                ready = false; state();
                role('reload-shifts').prop('disabled', true);
                $.ajax({ url: config.shiftsUrl, dataType: 'json', timeout: 15000 }).done(function (data) {
                    var select = $form.find('[name="roster_item_id"]').empty();
                    var shifts = data.shifts;
                    if (!Array.isArray(shifts)) { message('โหลดตารางเวรไม่สำเร็จ กรุณาลองใหม่'); return; }
                    select.append(new Option(shifts.length ? 'เลือกเวรที่จะลงเวลา' : 'ไม่พบเวรที่ประกาศใช้', ''));
                    shifts.forEach(function (s) { select.append(new Option(s.name + ' · ' + s.start.slice(0, 16) + ' ถึง ' + s.end.slice(0, 16), String(s.id))); });
                    if (shifts.length === 1) select.val(String(shifts[0].id));
                    select.prop('required', shifts.length > 1);
                    role('shift-help').text(shifts.length ? 'แสดงเวรของคุณตั้งแต่เมื่อวานถึงวันพรุ่งนี้ รวมเวรข้ามวัน' : 'ยังบันทึกเวลาเพื่อรอตรวจสอบได้ แต่จะไม่ประเมินสายจนกว่าจะระบุเวร');
                    ready = true;
                }).fail(function () { message('โหลดตารางเวรไม่สำเร็จ กรุณากดโหลดตารางเวรใหม่'); }).always(function () { role('reload-shifts').prop('disabled', false); state(); });
            }
            function stopCamera() {
                if (!scanner || !scanning) return Promise.resolve();
                scanning = false;
                return scanner.stop().catch(function () {}).then(function () { role('stop-scan').addClass('d-none'); role('scan').prop('disabled', false); });
            }
            role('scan').on('click', function () {
                if (!window.Html5Qrcode) { message('โหลดเครื่องสแกนไม่สำเร็จ ใช้กล้องมือถือสแกนป้ายแล้วเปิดลิงก์ได้'); return; }
                role('scan').prop('disabled', true);
                scanner = scanner || new window.Html5Qrcode(id + '-reader');
                scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 220, height: 220 } }, function (text) {
                    var token = text;
                    try {
                        var url = new URL(text);
                        if (url.origin !== window.location.origin || !url.searchParams.has('qr_token')) { message('QR นี้ไม่ใช่จุดลงเวลาของระบบ'); return; }
                        token = url.searchParams.get('qr_token');
                    } catch (ignore) { /* Existing printed QR codes contain the raw token. */ }
                    $form.find('[name="qr_token"]').val(token);
                    stopCamera();
                }, function () {}).then(function () {
                    scanning = true; role('stop-scan').removeClass('d-none');
                    if (!document.body.contains(form) || (form.closest('.modal') && !$(form.closest('.modal')).hasClass('show'))) stopCamera();
                }).catch(function () { role('scan').prop('disabled', false); message('เปิดกล้องไม่ได้ กรุณาอนุญาตกล้อง หรือใช้กล้องมือถือสแกนป้ายแล้วเปิดลิงก์'); });
            });
            role('stop-scan').on('click', stopCamera);
            $form.closest('.modal').on('hidden.bs.modal.attendance', stopCamera);
            window.addEventListener('pagehide', stopCamera, { once: true });
            $form.find('[name="method"]').on('change', function () { role('qr-panel').toggleClass('d-none', selected('method') !== 'qrcode'); stopCamera(); });
            $form.find('[name="check_type"]').on('change', state);
            role('gps-refresh').on('click', function () { locate().catch(function () {}); });
            role('reload-shifts').on('click', loadShifts);
            role('new').on('click', function () {
                saved = false; requestId = window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2);
                role('new').addClass('d-none'); role('result').addClass('d-none'); $form.find('input,select,textarea').prop('disabled', false); loadShifts();
            });
            $form.on('submit', function (event) {
                event.preventDefault();
                if (busy || saved || !ready) return;
                if (!form.reportValidity()) return;
                if (selected('method') === 'qrcode' && !value('qr_token').trim()) { message('กรุณาสแกน QR จุดลงเวลา'); return; }
                busy = true; state();
                // Refresh GPS at submission so a stale position cannot mark another location.
                locate().then(function (coords) {
                    var data = { method: selected('method'), check_type: selected('check_type'), roster_item_id: value('roster_item_id'),
                        qr_token: selected('method') === 'qrcode' ? value('qr_token').trim() : '',
                        out_of_location_reason: value('out_of_location_reason').trim(), lat: coords.latitude, lng: coords.longitude, request_id: requestId };
                    if (window.yii) data[window.yii.getCsrfParam()] = window.yii.getCsrfToken();
                    return $.ajax({ url: config.saveUrl, type: 'POST', data: data, dataType: 'json', timeout: 30000 });
                }).then(function (result) {
                    if (!result.success) { message(result.message || 'บันทึกไม่สำเร็จ'); return; }
                    saved = true;
                    message(result.message, true);
                    $form.find('input,select,textarea').prop('disabled', true);
                    role('new').removeClass('d-none'); stopCamera();
                }).catch(function (error) { message(error.message || 'การเชื่อมต่อขัดข้อง กดลองอีกครั้ง ระบบจะตรวจรายการซ้ำให้'); }).finally(function () { busy = false; state(); });
            });
            loadShifts(); state();
        }
    };
})(window, window.jQuery);
