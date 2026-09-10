(function (window, $) {
    'use strict';
    window.AttendanceClock = {
        mount: function (id, config) {
            var form = document.getElementById(id);
            if (!form || form.dataset.mounted) return;
            form.dataset.mounted = '1';
            var $form = $(form), busy = false, needsReason = false, requestId, offset = 0;
            var storageKey = 'attendance-request-' + config.employeeId;
            function role(name) { return $form.find('[data-role="' + name + '"]'); }
            function readKey() { try { return sessionStorage.getItem(storageKey); } catch (e) { return null; } }
            function key() {
                if (!requestId) {
                    requestId = readKey() || (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2));
                    try { sessionStorage.setItem(storageKey, requestId); } catch (e) { /* In-memory key protects retries on this page. */ }
                }
                return requestId;
            }
            function message(text, ok) { role('result').removeClass('d-none alert-success alert-danger').addClass(ok ? 'alert-success' : 'alert-danger').text(text).trigger('focus'); }
            function state() { role('submit').prop('disabled', busy).text(busy ? 'กำลังดำเนินการ…' : needsReason ? 'ส่งลงเวลารออนุมัติ' : 'สแกนเวลา'); }
            function ajax(url, data) {
                if (window.yii) data[window.yii.getCsrfParam()] = window.yii.getCsrfToken();
                return $.ajax({url:url, type:'POST', data:data, dataType:'json', timeout:30000});
            }
            function locate() {
                role('gps').text('กำลังอ่าน GPS กรุณาอนุญาตตำแหน่งของเว็บไซต์');
                return new Promise(function (resolve, reject) {
                    if (!window.isSecureContext) { reject(new Error('GPS ต้องใช้เว็บไซต์ HTTPS กรุณาเปิดระบบผ่านที่อยู่ที่ผู้ดูแลกำหนด')); return; }
                    if (!navigator.geolocation) { reject(new Error('เบราว์เซอร์ไม่รองรับ GPS กรุณาใช้เบราว์เซอร์ที่รองรับ')); return; }
                    navigator.geolocation.getCurrentPosition(function (p) { resolve(p.coords); }, function (e) {
                        reject(new Error(e.code === 1 ? 'ไม่อนุญาต GPS กรุณาเปิดสิทธิ์ตำแหน่งของเว็บไซต์แล้วลองใหม่' : e.code === 3 ? 'อ่าน GPS หมดเวลา กรุณาเปิดตำแหน่งและลองใหม่' : 'หาตำแหน่งไม่ได้ กรุณาเปิด GPS แล้วลองใหม่'));
                    }, {enableHighAccuracy:true,timeout:20000,maximumAge:0});
                });
            }
            function clock() { role('clock').text(new Intl.DateTimeFormat('th-TH',{timeZone:'Asia/Bangkok',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date(Date.now()+offset))); }
            clock();
            var timer = window.setInterval(function () { if (!document.body.contains(form)) { clearInterval(timer); return; } clock(); }, 1000);
            $form.closest('.modal').one('hidden.bs.modal.attendance', function () { clearInterval(timer); });
            $.ajax({url:config.shiftsUrl,dataType:'json',timeout:15000}).done(function (r) {
                if (r.now) offset = new Date(r.now.replace(' ','T')+'+07:00').getTime()-Date.now();
                role('latest').text(r.latest ? 'บันทึกล่าสุด '+r.latest.at+' · '+r.latest.status : 'ยังไม่มีประวัติลงเวลา');
            }).fail(function () { role('latest').text('โหลดรายการล่าสุดไม่สำเร็จ เปิดประวัติเพื่อตรวจสอบได้'); });
            async function submit() {
                if (busy) return;
                var reason = $form.find('[name="out_of_location_reason"]').val().trim();
                if (needsReason && !reason) { message('กรุณาระบุเหตุผลลงเวลานอกพื้นที่'); $form.find('textarea').trigger('focus'); return; }
                busy = true; state();
                try {
                    var coords = await locate();
                    var data = {lat:coords.latitude,lng:coords.longitude,qr_token:$form.find('[name="qr_token"]').val()};
                    role('gps').text('กำลังตรวจพื้นที่ลงเวลา');
                    var position = await ajax(config.positionUrl, Object.assign({},data));
                    if (!position.success) { message(position.message || 'ตรวจพื้นที่ไม่สำเร็จ กรุณาลองใหม่'); return; }
                    role('gps').text((position.inside ? 'อยู่ในพื้นที่: ' : 'อยู่นอกพื้นที่: ')+(position.location || 'จุดลงเวลา'));
                    needsReason = !position.inside;
                    role('reason-panel').toggleClass('d-none', !needsReason);
                    if (needsReason && !reason) { message('อยู่นอกพื้นที่ กรุณาระบุเหตุผลแล้วส่งลงเวลารออนุมัติ'); $form.find('textarea').trigger('focus'); return; }
                    data.method = data.qr_token ? 'qrcode' : 'manual'; data.out_of_location_reason = reason; data.request_id = key();
                    role('gps').text('กำลังบันทึกเวลา กรุณารอผล');
                    var result = await ajax(config.saveUrl, data);
                    if (!result.success) {
                        message(result.message || 'บันทึกไม่สำเร็จ กรุณาลองใหม่');
                        if ((result.message || '').indexOf('เหตุผล') !== -1) { needsReason=true; role('reason-panel').removeClass('d-none'); }
                        return;
                    }
                    message(result.message+(result.location ? ' · '+result.location : ''), true);
                    role('latest').text('บันทึกล่าสุด '+result.checkin_at);
                    role('gps').text('ระบบรับเวลาแล้ว กดซ้ำภายใน 2 นาทีจะไม่สร้างรายการใหม่');
                    needsReason=false; role('reason-panel').addClass('d-none'); $form.find('textarea').val('');
                    try { sessionStorage.removeItem(storageKey); } catch (e) { /* Storage may be disabled. */ }
                    requestId=null;
                } catch (error) {
                    message(error.status === 401 || error.status === 403 ? 'เซสชันหมดอายุหรือไม่มีสิทธิ์ กรุณาเข้าสู่ระบบใหม่' : error.message || 'ยังยืนยันผลบันทึกไม่ได้ การเชื่อมต่อขัดข้อง กรุณาลองใหม่ ระบบจะตรวจรายการซ้ำให้');
                    role('gps').text('กรุณาตรวจข้อความแจ้งเตือน แล้วกดสแกนเวลาเพื่อลองใหม่');
                } finally { busy=false; state(); }
            }
            $form.on('submit',function(e){e.preventDefault();submit();});
            if (config.autoStart) submit();
        }
    };
})(window, window.jQuery);
