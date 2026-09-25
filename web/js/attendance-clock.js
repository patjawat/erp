(function (window, $) {
    'use strict';
    window.AttendanceClock = {
        mount: function (id, config) {
            var form = document.getElementById(id);
            if (!form || form.dataset.mounted) return;
            form.dataset.mounted = '1';
            var $form = $(form), busy = false, needsReason = false, requestId, offset = 0, startedAt = 0;
            var photoBlob = null, photoPath = null, photoUrl = null; // รูปยืนยันตัวตน (เฉพาะนอกพื้นที่)
            var storageKey = 'attendance-request-' + config.employeeId;
            function role(name) { return $form.find('[data-role="' + name + '"]'); }
            function showDay(summary) {
                if (!summary) return;
                role('day-date').text(summary.date || '');
                role('time-in').text(summary.in ? summary.in.slice(11,16) : '--:--');
                role('time-out').text(summary.out ? summary.out.slice(11,16) : '--:--');
                role('pending-in').toggleClass('d-none', !summary.in || summary.in_status !== 'pending');
                role('pending-out').toggleClass('d-none', !summary.out || summary.out_status !== 'pending');
            }
            function readKey() { try { return sessionStorage.getItem(storageKey); } catch (e) { return null; } }
            function key() {
                if (!requestId) {
                    requestId = readKey() || (window.crypto && window.crypto.randomUUID ? window.crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2));
                    try { sessionStorage.setItem(storageKey, requestId); } catch (e) { /* In-memory key protects retries on this page. */ }
                }
                return requestId;
            }
            function message(text, ok) { role('result').removeClass('d-none alert-success alert-danger').addClass(ok ? 'alert-success' : 'alert-danger').text(text).trigger('focus'); }
            function state() { role('submit').prop('disabled', busy).text(busy ? 'กำลังดำเนินการ…' : needsReason ? 'ส่งลงเวลารอยืนยัน' : 'ลงเวลา'); }
            // The mobile module's global submit listener pops a full-screen "กำลังบันทึก…" overlay
            // that only hides on a real page load; this AJAX form never navigates, so dismiss it ourselves.
            function hideMobileLoader() { try { if (window.hideMobileLoader) window.hideMobileLoader(); } catch (e) { /* overlay not present outside mobile layout */ } }
            function ajax(url, data, timeout) {
                if (window.yii) data[window.yii.getCsrfParam()] = window.yii.getCsrfToken();
                return $.ajax({url:url, type:'POST', data:data, dataType:'json', timeout: timeout || 30000});
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
            // ย่อรูปบนเครื่องก่อนส่ง (ด้านยาว 640px, JPEG) — ส่งแค่ ~50KB แม้เน็ตมือถือช้า; เซิร์ฟเวอร์ย่อซ้ำ + ประทับเวลาอีกชั้น
            function drawToBlob(src, w, h, fallback) {
                var scale = Math.min(1, 640 / Math.max(w, h));
                var canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(w * scale)); canvas.height = Math.max(1, Math.round(h * scale));
                canvas.getContext('2d').drawImage(src, 0, 0, canvas.width, canvas.height);
                return new Promise(function (resolve) { canvas.toBlob(function (b) { resolve(b || fallback); }, 'image/jpeg', 0.8); });
            }
            function compressPhoto(file) {
                function draw(src, w, h) { return drawToBlob(src, w, h, file); }
                if (window.createImageBitmap) {
                    return createImageBitmap(file, {imageOrientation: 'from-image'}).then(function (bmp) { return draw(bmp, bmp.width, bmp.height); }).catch(function () { return file; });
                }
                return new Promise(function (resolve) {
                    var img = new Image(), url = URL.createObjectURL(file);
                    img.onload = function () { URL.revokeObjectURL(url); draw(img, img.naturalWidth, img.naturalHeight).then(resolve); };
                    img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
                    img.src = url;
                });
            }
            function resetPhoto() {
                photoBlob = null; photoPath = null;
                if (photoUrl) { URL.revokeObjectURL(photoUrl); photoUrl = null; }
                role('photo-preview').addClass('d-none').removeAttr('src');
                role('photo-label').text('เปิดกล้องถ่ายรูป');
                role('photo-input').val('');
                stopCamera();
            }
            function setPhoto(blob) {
                photoBlob = blob; photoPath = null; // รูปใหม่ต้องอัปโหลดใหม่
                if (photoUrl) URL.revokeObjectURL(photoUrl);
                photoUrl = URL.createObjectURL(blob);
                role('photo-preview').attr('src', photoUrl).removeClass('d-none');
                role('photo-label').text('ถ่ายใหม่');
            }
            role('photo-input').on('change', function () {
                var file = this.files && this.files[0];
                if (!file) return;
                stopCamera();
                role('photo-label').text('กำลังเตรียมรูป…');
                compressPhoto(file).then(setPhoto);
            });
            // กล้องในหน้า: Telegram (โดยเฉพาะ Android) มักไม่เปิดกล้องจาก <input capture> — ใช้ getUserMedia เป็นหลัก
            var stream = null;
            function stopCamera() {
                if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
                role('camera-panel').addClass('d-none');
                var v = role('camera-video')[0]; if (v) v.srcObject = null;
            }
            role('camera-open').on('click', function () {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    message('เครื่องนี้เปิดกล้องในหน้าไม่ได้ กรุณากด "แนบรูป" แล้วเลือกถ่ายรูปหรือรูปจากเครื่อง');
                    return;
                }
                stopCamera();
                navigator.mediaDevices.getUserMedia({video: {facingMode: 'user', width: {ideal: 1280}, height: {ideal: 960}}, audio: false})
                    .then(function (s) {
                        stream = s;
                        var v = role('camera-video')[0];
                        v.srcObject = s;
                        role('camera-panel').removeClass('d-none');
                        var p = v.play(); if (p && p.catch) p.catch(function () { /* autoplay+muted ควรเล่นได้ */ });
                    })
                    .catch(function (e) {
                        message(e && e.name === 'NotAllowedError'
                            ? 'ไม่ได้รับอนุญาตให้ใช้กล้อง กรุณาอนุญาตกล้องให้ Telegram/เบราว์เซอร์ หรือกด "แนบรูป"'
                            : 'เปิดกล้องไม่ได้ กรุณากด "แนบรูป" แล้วเลือกถ่ายรูปหรือรูปจากเครื่อง');
                    });
            });
            role('camera-shot').on('click', function () {
                var v = role('camera-video')[0];
                if (!v || !v.videoWidth) { message('กล้องยังไม่พร้อม กรุณารอสักครู่แล้วกดถ่ายอีกครั้ง'); return; }
                drawToBlob(v, v.videoWidth, v.videoHeight, null).then(function (blob) { stopCamera(); setPhoto(blob); });
            });
            role('camera-close').on('click', stopCamera);
            $form.closest('.modal').one('hidden.bs.modal.attendance-camera', stopCamera);
            window.addEventListener('pagehide', stopCamera);
            function uploadPhoto() {
                if (photoPath) return Promise.resolve(photoPath);
                var fd = new FormData();
                fd.append('file', photoBlob, 'checkin.jpg');
                if (window.yii) fd.append(window.yii.getCsrfParam(), window.yii.getCsrfToken());
                return $.ajax({url: config.uploadUrl, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json', timeout: 60000})
                    .then(function (r) {
                        if (!r || !r.url) throw new Error((r && r.error) || 'อัปโหลดรูปไม่สำเร็จ กรุณาลองใหม่');
                        photoPath = r.url;
                        return photoPath;
                    });
            }
            function clock() { role('clock').text(new Intl.DateTimeFormat('th-TH',{timeZone:'Asia/Bangkok',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date(Date.now()+offset))); }
            clock();
            var timer = window.setInterval(function () {
                if (!document.body.contains(form)) { clearInterval(timer); return; }
                clock();
                // Wall-clock watchdog: Telegram's in-app webview can freeze XHR/timers when backgrounded,
                // so a save may land on the server while the client stays stuck at busy. Date.now() reflects
                // real elapsed time once the interval resumes, so recover the true state then.
                if (busy && startedAt && Date.now() - startedAt > 35000) { startedAt = 0; reconcile(); }
            }, 1000);
            $form.closest('.modal').one('hidden.bs.modal.attendance', function () { clearInterval(timer); });
            function refreshDay(initial) {
                return $.ajax({url:config.shiftsUrl,dataType:'json',timeout:15000}).done(function (r) {
                    if (r.now) offset = new Date(r.now.replace(' ','T')+'+07:00').getTime()-Date.now();
                    showDay(r.day_summary);
                    role('latest').text(r.latest ? 'บันทึกล่าสุด '+r.latest.at : '');
                    role('pending-latest').toggleClass('d-none', !r.latest || r.latest.status_code !== 'pending');
                }).fail(function () { if (initial) role('latest').text(''); });
            }
            refreshDay(true);
            function reconcile() {
                // Pull the server's real state and clear any stuck spinner; confirm if the save actually landed.
                busy = false; startedAt = 0; state(); role('gps').text(''); hideMobileLoader();
                refreshDay().done(function (r) {
                    if (r && r.latest) message('ระบบตรวจสอบแล้ว บันทึกเวลาล่าสุด ' + r.latest.at, true);
                    else if (r && r.day_summary && (r.day_summary.in || r.day_summary.out)) message('ระบบตรวจสอบแล้ว มีการบันทึกเวลาวันนี้เรียบร้อย', true);
                });
            }
            // Recover the moment the webview returns to the foreground (visibility + Telegram 'activated').
            function onResume() { if (busy && document.body.contains(form)) reconcile(); }
            document.addEventListener('visibilitychange', function () { if (!document.hidden) onResume(); });
            try { if (window.Telegram && window.Telegram.WebApp && window.Telegram.WebApp.onEvent) window.Telegram.WebApp.onEvent('activated', onResume); } catch (e) { /* Telegram SDK optional */ }
            async function submit() {
                if (busy) return;
                var reason = $form.find('[name="out_of_location_reason"]').val().trim();
                if (needsReason && !reason) { message('กรุณาระบุเหตุผลลงเวลานอกพื้นที่'); $form.find('textarea').trigger('focus'); return; }
                if (needsReason && !photoBlob) { message('กรุณาถ่ายรูปยืนยันตัวตนก่อนส่งลงเวลา'); return; }
                busy = true; startedAt = Date.now(); state();
                try {
                    var coords = await locate();
                    var data = {lat:coords.latitude,lng:coords.longitude,qr_token:$form.find('[name="qr_token"]').val()};
                    role('gps').text('กำลังตรวจพื้นที่ลงเวลา');
                    var position = await ajax(config.positionUrl, Object.assign({},data));
                    if (!position.success) { message(position.message || 'ตรวจพื้นที่ไม่สำเร็จ กรุณาลองใหม่'); return; }
                    role('gps').text((position.inside ? 'อยู่ในพื้นที่: ' : 'อยู่นอกพื้นที่: ')+(position.location || 'จุดลงเวลา'));
                    needsReason = !position.inside;
                    role('reason-panel').toggleClass('d-none', !needsReason);
                    if (needsReason && (!reason || !photoBlob)) { message('อยู่นอกพื้นที่ กรุณาระบุเหตุผลและถ่ายรูปยืนยันตัวตน แล้วส่งลงเวลารอยืนยัน'); if (!reason) $form.find('textarea').trigger('focus'); return; }
                    data.method = data.qr_token ? 'qrcode' : 'manual'; data.out_of_location_reason = reason; data.request_id = key();
                    if (needsReason) {
                        role('gps').text('กำลังส่งรูปยืนยันตัวตน');
                        data.photo_path = await uploadPhoto();
                    }
                    role('gps').text('กำลังบันทึกเวลา กรุณารอผล');
                    var result = await ajax(config.saveUrl, data, 30000);
                    if (!result.success) {
                        message(result.message || 'บันทึกไม่สำเร็จ กรุณาลองใหม่');
                        if (/เหตุผล|ถ่ายรูป/.test(result.message || '')) { needsReason=true; role('reason-panel').removeClass('d-none'); }
                        return;
                    }
                    message(result.message+(result.location ? ' · '+result.location : ''), true);
                    role('latest').text('บันทึกล่าสุด '+result.checkin_at);
                    role('pending-latest').toggleClass('d-none', result.status !== 'pending');
                    showDay(result.day_summary);
                    role('gps').text('');
                    needsReason=false; role('reason-panel').addClass('d-none'); $form.find('textarea').val(''); resetPhoto();
                    try { sessionStorage.removeItem(storageKey); } catch (e) { /* Storage may be disabled. */ }
                    requestId=null;
                } catch (error) {
                    role('gps').text('');
                    if (error && (error.status === 401 || error.status === 403)) {
                        message('เซสชันหมดอายุหรือไม่มีสิทธิ์ กรุณาเข้าสู่ระบบใหม่');
                    } else if (error && error.message) {
                        // ข้อผิดพลาดจาก GPS/ตรวจสอบ (มีข้อความชัดเจน) — ไม่มีการบันทึก
                        message(error.message);
                    } else {
                        // ต่อไม่ติด/หมดเวลา (พบบ่อยในเว็บวิว Telegram) — รายการอาจถูกบันทึกแล้ว ดึงสถานะจริงมาแสดง
                        message('การเชื่อมต่อช้า ระบบกำลังตรวจสอบเวลาที่บันทึกให้ หากไม่แสดงกรุณาลองใหม่ (ระบบกันรายการซ้ำให้)');
                        reconcile();
                    }
                } finally { busy=false; startedAt=0; state(); hideMobileLoader(); }
            }
            $form.on('submit',function(e){e.preventDefault();hideMobileLoader();submit();});
            if (config.autoStart) submit();
        }
    };
})(window, window.jQuery);
