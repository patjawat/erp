<?php
/**
 * เนื้อหาลงเวลาสำหรับเปิดใน modal (.open-modal) — คืนผ่าน actionCheckinModal
 * ตัวแปร: $geofences (CheckinLocation[]), $checkType ('in'|'out'), $saveUrl
 * NOTE: renderPartial ไม่รัน registerJs/registerCss — JS/CSS ต้อง inline ในไฟล์นี้
 */
$geofences = $geofences ?? [];
$checkType = in_array($checkType ?? 'in', ['in', 'out'], true) ? $checkType : 'in';
$geofencesForJs = [];
foreach ($geofences as $g) {
    $geofencesForJs[] = [
        'id' => (int)$g->id,
        'name' => $g->name,
        'lat' => (float)$g->lat,
        'lng' => (float)$g->lng,
        'radius_m' => (int)$g->radius_m,
    ];
}
$cfg = json_encode([
    'saveUrl' => $saveUrl,
    'geofences' => $geofencesForJs,
    'checkType' => $checkType,
], JSON_UNESCAPED_UNICODE);
?>
<div class="att-mck">
    <div class="att-mck__seg" role="radiogroup" aria-label="ประเภทการลงเวลา">
        <button type="button" class="att-mck__seg-item<?= $checkType === 'in' ? ' is-active' : '' ?>" role="radio" aria-checked="<?= $checkType === 'in' ? 'true' : 'false' ?>" data-check-type="in">
            <i class="bi bi-box-arrow-in-right att-mck__seg-icon" aria-hidden="true"></i>
            <span class="att-mck__seg-label">ลงเวลาเข้า</span>
            <span class="att-mck__seg-hint">เริ่มปฏิบัติงาน</span>
        </button>
        <button type="button" class="att-mck__seg-item<?= $checkType === 'out' ? ' is-active' : '' ?>" role="radio" aria-checked="<?= $checkType === 'out' ? 'true' : 'false' ?>" data-check-type="out">
            <i class="bi bi-box-arrow-right att-mck__seg-icon" aria-hidden="true"></i>
            <span class="att-mck__seg-label">ลงเวลาออก</span>
            <span class="att-mck__seg-hint">เลิกปฏิบัติงาน</span>
        </button>
    </div>

    <div class="att-mck__clock" aria-live="polite">
        <span class="att-mck__clock-label">เวลาที่จะบันทึก</span>
        <span class="att-mck__clock-time" data-role="clock">--:--:--</span>
        <span class="att-mck__clock-date" data-role="date"></span>
    </div>

    <div class="att-mck__loc">
        <p class="att-mck__coord"><i class="bi bi-geo-alt" aria-hidden="true"></i> <span data-role="coord">กำลังตรวจสอบสิทธิ์ตำแหน่ง...</span></p>
        <div class="att-mck__permission d-none" data-role="perm" role="region" aria-label="ขออนุญาตตำแหน่ง">
            <p class="att-mck__permission-text" data-role="perm-text"></p>
            <button type="button" class="att-mck__btn att-mck__btn--light att-mck__btn--block" data-role="perm-btn">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> อนุญาตใช้ตำแหน่ง
            </button>
            <p class="att-mck__permission-extra d-none" data-role="perm-extra"></p>
        </div>
        <div class="att-mck__fence d-none" data-role="fence" role="status" aria-live="polite"></div>
    </div>

    <div class="att-mck__result d-none" data-role="result" role="alert" aria-live="assertive"></div>

    <div class="att-mck__actions">
        <button type="button" class="att-mck__btn att-mck__btn--primary att-mck__btn--block att-mck__btn--lg" data-role="submit">
            <span class="att-mck__spinner" aria-hidden="true"></span>
            <i class="bi bi-check-circle att-mck__btn-icon" aria-hidden="true"></i>
            <span data-role="submit-label">ลงเวลาเข้า</span>
        </button>
        <button type="button" class="att-mck__btn att-mck__btn--light att-mck__btn--block" data-role="close">ปิด</button>
    </div>
</div>

<style>
.att-mck{--ink-1:#1a202c;--ink-2:#4a5568;--ink-3:#718096;--surface:#fff;--surface-2:#f7f9fc;--surface-3:#eef2f7;--surface-hover:#f1f5f9;--line:rgba(15,23,42,.08);--line-strong:rgba(15,23,42,.14);--primary:#0d6efd;--primary-ink:#0a58ca;--primary-soft:rgba(13,110,253,.08);--primary-line:rgba(13,110,253,.22);--success:#15803d;--success-soft:rgba(21,128,61,.1);--warning:#b45309;--warning-soft:rgba(180,83,9,.1);--danger:#b91c1c;--danger-soft:rgba(185,28,28,.1);--radius-sm:8px;--ease:cubic-bezier(.16,1,.3,1);display:flex;flex-direction:column;gap:.9rem;color:var(--ink-1)}
.att-mck__seg{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
.att-mck__seg-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.25rem;min-height:88px;padding:.8rem .5rem;border:1.5px solid var(--line-strong);border-radius:var(--radius-sm);background:var(--surface);color:var(--ink-2);cursor:pointer;transition:border-color 140ms var(--ease),background 140ms var(--ease),box-shadow 140ms var(--ease),color 140ms var(--ease)}
.att-mck__seg-icon{font-size:1.5rem;line-height:1;color:var(--ink-3);transition:color 140ms var(--ease)}
.att-mck__seg-label{font-size:.95rem;font-weight:700;color:var(--ink-1)}
.att-mck__seg-hint{font-size:.74rem;color:var(--ink-3)}
.att-mck__seg-item:hover{border-color:var(--primary-line);background:var(--surface-hover)}
.att-mck__seg-item:focus-visible{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
.att-mck__seg-item.is-active{border-color:var(--primary);background:var(--primary-soft);box-shadow:0 0 0 3px var(--primary-soft)}
.att-mck__seg-item.is-active .att-mck__seg-icon,.att-mck__seg-item.is-active .att-mck__seg-label{color:var(--primary-ink)}
.att-mck__clock{display:flex;flex-direction:column;align-items:center;gap:.1rem;padding:.9rem 1rem;border:1px solid var(--line);border-radius:var(--radius-sm);background:var(--surface-2)}
.att-mck__clock-label{font-size:.76rem;color:var(--ink-3)}
.att-mck__clock-time{font-size:2.3rem;font-weight:700;line-height:1.05;color:var(--ink-1);font-variant-numeric:tabular-nums;letter-spacing:.01em}
.att-mck__clock-date{font-size:.82rem;color:var(--ink-2);font-variant-numeric:tabular-nums}
.att-mck__coord{display:flex;align-items:center;gap:.4rem;margin:0 0 .5rem;font-size:.84rem;color:var(--ink-2)}
.att-mck__coord i{color:var(--ink-3)}
.att-mck__permission{border:1px solid var(--primary-line);border-radius:var(--radius-sm);background:var(--primary-soft);padding:.8rem;margin-bottom:.5rem}
.att-mck__permission-text{margin:0 0 .7rem;font-size:.82rem;color:var(--ink-2);line-height:1.5}
.att-mck__permission-extra{margin:.55rem 0 0;font-size:.77rem;color:var(--ink-3);line-height:1.45}
.att-mck__fence{display:flex;align-items:flex-start;gap:.45rem;padding:.55rem .7rem;border-radius:var(--radius-sm);font-size:.82rem;font-weight:600;line-height:1.45}
.att-mck__fence i{margin-top:.1rem;flex:none}
.att-mck__fence.is-ok{background:var(--success-soft);color:var(--success)}
.att-mck__fence.is-warn{background:var(--warning-soft);color:var(--warning)}
.att-mck__fence.is-error{background:var(--danger-soft);color:var(--danger)}
.att-mck__result{display:flex;align-items:center;gap:.5rem;padding:.75rem .9rem;border-radius:var(--radius-sm);font-size:.88rem;font-weight:600}
.att-mck__result.is-ok{background:var(--success-soft);color:var(--success)}
.att-mck__result.is-error{background:var(--danger-soft);color:var(--danger)}
.att-mck__actions{display:flex;flex-direction:column;gap:.55rem}
.att-mck__btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;min-height:44px;padding:.55rem 1rem;border:1px solid transparent;border-radius:var(--radius-sm);font-size:.94rem;font-weight:600;cursor:pointer;transition:background 140ms var(--ease),border-color 140ms var(--ease),color 140ms var(--ease),transform 80ms var(--ease)}
.att-mck__btn--block{width:100%}
.att-mck__btn--lg{min-height:52px;font-size:1.02rem}
.att-mck__btn--primary{background:var(--primary);color:#fff;border-color:var(--primary)}
.att-mck__btn--primary:hover{background:var(--primary-ink);border-color:var(--primary-ink);color:#fff}
.att-mck__btn--primary:active{transform:translateY(1px)}
.att-mck__btn--primary:focus-visible{outline:none;box-shadow:0 0 0 3px var(--primary-soft)}
.att-mck__btn--primary:disabled{opacity:.5;cursor:not-allowed}
.att-mck__btn--light{background:var(--surface-2);color:var(--ink-1);border-color:var(--line-strong)}
.att-mck__btn--light:hover{background:var(--surface-hover);color:var(--ink-1)}
.att-mck__btn--light:focus-visible{outline:none;box-shadow:0 0 0 3px var(--primary-soft)}
.att-mck__spinner{display:none;width:18px;height:18px;border:2px solid rgba(255,255,255,.5);border-top-color:#fff;border-radius:50%;animation:att-mck-spin .7s linear infinite}
.att-mck__btn.is-loading .att-mck__spinner{display:inline-block}
.att-mck__btn.is-loading .att-mck__btn-icon{display:none}
@keyframes att-mck-spin{to{transform:rotate(360deg)}}
@media (prefers-reduced-motion:reduce){.att-mck__seg-item,.att-mck__btn{transition:none}.att-mck__spinner{animation-duration:1.4s}}
</style>

<script>
(function(){
    var CFG = <?= $cfg ?>;
    var root = document.querySelector('#main-modal .att-mck');
    if (!root) return;
    var $root = $(root);
    var q = function(sel){ return root.querySelector(sel); };

    var geofences = CFG.geofences || [];
    var requireGeofence = !!geofences.length;
    var currentCheckType = CFG.checkType || 'in';
    var insideAllowed = null;

    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function haversineM(a,b,c,d){ var R=6371000,r=function(x){return x*Math.PI/180;},dLa=r(c-a),dLo=r(d-b),h=Math.sin(dLa/2)*Math.sin(dLa/2)+Math.cos(r(a))*Math.cos(r(c))*Math.sin(dLo/2)*Math.sin(dLo/2); return R*2*Math.atan2(Math.sqrt(h),Math.sqrt(1-h)); }

    // เคลียร์ interval ของครั้งก่อน (กัน leak เมื่อเปิด modal ซ้ำ)
    if (window.__attMckClock) { clearInterval(window.__attMckClock); }
    function updateClock(){
        var n=new Date(), p=function(x){return String(x).padStart(2,'0');};
        q('[data-role=clock]').textContent = p(n.getHours())+':'+p(n.getMinutes())+':'+p(n.getSeconds());
        q('[data-role=date]').textContent = n.getDate()+'/'+(n.getMonth()+1)+'/'+(n.getFullYear()+543);
    }
    updateClock();
    window.__attMckClock = setInterval(updateClock, 1000);
    // หยุดนาฬิกาเมื่อปิด modal
    $('#main-modal').one('hidden.bs.modal', function(){ if(window.__attMckClock){ clearInterval(window.__attMckClock); window.__attMckClock=null; } });

    function setFence(state, html){
        var el = q('[data-role=fence]');
        el.classList.remove('is-ok','is-warn','is-error','d-none');
        if (!state){ el.classList.add('d-none'); el.innerHTML=''; return; }
        el.classList.add(state); el.innerHTML = html;
    }
    function refreshFence(la, ln){
        if (!requireGeofence){ insideAllowed=true; setFence(null); updateSubmit(); return; }
        if (la==null || ln==null){ insideAllowed=false; setFence('is-error','<i class="bi bi-exclamation-triangle"></i>องค์กรกำหนดบริเวณลงเวลา ต้องได้รับพิกัด GPS ก่อนจึงจะลงเวลาได้'); updateSubmit(); return; }
        var inside=null,near=null,nearD=null;
        for (var i=0;i<geofences.length;i++){ var z=geofences[i], d=haversineM(la,ln,z.lat,z.lng); if(d<=z.radius_m){inside=z;break;} if(nearD===null||d<nearD){nearD=d;near=z;} }
        if (inside){ insideAllowed=true; setFence('is-ok','<i class="bi bi-check-circle"></i>อยู่ในบริเวณที่อนุญาต «'+esc(inside.name)+'» (รัศมี '+inside.radius_m+' ม.)'); }
        else if (near){ insideAllowed=false; setFence('is-warn','<i class="bi bi-geo-alt"></i>ยังไม่อยู่ในรัศมีที่กำหนด ห่างจาก «'+esc(near.name)+'» ~'+Math.round(nearD)+' ม. (อนุญาต '+near.radius_m+' ม.)'); }
        else { insideAllowed=false; setFence(null); }
        updateSubmit();
    }
    function updateSubmit(){
        var ok = requireGeofence ? (insideAllowed===true) : true;
        var b = q('[data-role=submit]');
        if (b.classList.contains('is-loading')) return;
        b.disabled = !ok;
    }

    // seg เข้า/ออก
    function paintSeg(){
        $root.find('.att-mck__seg-item').removeClass('is-active').attr('aria-checked','false');
        $root.find('.att-mck__seg-item[data-check-type="'+currentCheckType+'"]').addClass('is-active').attr('aria-checked','true');
        q('[data-role=submit-label]').textContent = currentCheckType==='in' ? 'ลงเวลาเข้า' : 'ลงเวลาออก';
    }
    $root.find('.att-mck__seg-item').on('click', function(){ currentCheckType = $(this).data('check-type'); paintSeg(); });
    paintSeg();

    var lat=null, lng=null;
    function setCoord(la, ln){
        lat=la; lng=ln;
        q('[data-role=coord]').textContent = (la!=null&&ln!=null) ? ('พิกัด '+la.toFixed(5)+', '+ln.toFixed(5)) : 'ยังไม่มีพิกัด กดปุ่มด้านล่างเพื่ออนุญาตตำแหน่ง';
        refreshFence(la, ln);
    }
    function showPerm(show, text, extra){
        var perm=q('[data-role=perm]'), ex=q('[data-role=perm-extra]');
        if (show){ perm.classList.remove('d-none'); q('[data-role=perm-text]').textContent=text||''; if(extra){ex.classList.remove('d-none');ex.textContent=extra;}else{ex.classList.add('d-none');ex.textContent='';} }
        else { perm.classList.add('d-none'); ex.classList.add('d-none'); ex.textContent=''; }
    }
    function fetchLoc(){
        var b=q('[data-role=perm-btn]'); b.disabled=true;
        q('[data-role=coord]').textContent='กำลังขอตำแหน่ง...';
        if (!navigator.geolocation){ setCoord(null,null); showPerm(true,'เบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง',''); return; }
        navigator.geolocation.getCurrentPosition(
            function(p){ b.disabled=false; showPerm(false); setCoord(p.coords.latitude, p.coords.longitude); },
            function(err){
                b.disabled=false; setCoord(null,null);
                var c=err&&err.code, msg='ยังไม่ได้รับตำแหน่ง กรุณากดปุ่ม «อนุญาตใช้ตำแหน่ง» แล้วเลือกอนุญาตเมื่อระบบถาม', extra='';
                if(c===1){ msg='ยังไม่อนุญาตให้ใช้ตำแหน่ง กรุณากดปุ่มด้านล่าง แล้วเลือกอนุญาตในหน้าต่างของเบราว์เซอร์'; extra='ถ้าเคยปฏิเสธไว้ ให้ไปที่ตั้งค่าเบราว์เซอร์ > ความเป็นส่วนตัว/ตำแหน่ง แล้วอนุญาตสำหรับเว็บไซต์นี้'; }
                else if(c===2){ msg='ระบบหาตำแหน่งไม่ได้ชั่วคราว ลองอีกครั้งในที่โล่งหรือเปิด GPS'; }
                else if(c===3){ msg='หมดเวลารอตำแหน่ง กรุณากดปุ่มเพื่อลองใหม่'; }
                showPerm(true, msg, extra);
            },
            { enableHighAccuracy:true, timeout:20000, maximumAge:0 }
        );
    }
    $(q('[data-role=perm-btn]')).on('click', fetchLoc);

    if (navigator.permissions && navigator.permissions.query){
        navigator.permissions.query({name:'geolocation'}).then(function(st){
            if (st.state==='denied'){ setCoord(null,null); showPerm(true,'การใช้ตำแหน่งถูกปิดไว้ในเบราว์เซอร์ กรุณาเปิดสิทธิ์ตำแหน่งในการตั้งค่า แล้วกดปุ่มด้านล่างเพื่อลองใหม่','iOS: ตั้งค่า > Safari > ตำแหน่ง — Android: ตั้งค่าแอปเบราว์เซอร์ > สิทธิ์ > ตำแหน่ง'); return; }
            st.onchange=function(){ if(st.state==='granted') fetchLoc(); };
            fetchLoc();
        }).catch(fetchLoc);
    } else { fetchLoc(); }

    function setResult(kind, html){
        var r=q('[data-role=result]');
        r.classList.remove('d-none','is-ok','is-error');
        r.classList.add(kind==='ok'?'is-ok':'is-error');
        r.innerHTML = html;
    }

    $(q('[data-role=submit]')).on('click', function(){
        var b=this;
        if (b.disabled) return;
        b.classList.add('is-loading'); b.disabled=true;
        $.post(CFG.saveUrl, { method:'manual', check_type:currentCheckType, lat:lat||null, lng:lng||null })
        .then(function(res){
            if (res.success){
                setResult('ok','<i class="bi bi-check-circle-fill"></i>'+esc(res.message||'บันทึกสำเร็จ'));
                // อัปเดตตัวนับบนหน้า /me ถ้ามี
                var cnt=document.getElementById('today-checkin-count');
                if (cnt){ var v=parseInt(cnt.textContent,10); cnt.textContent = isNaN(v)?'1':(v+1); }
            } else {
                setResult('error','<i class="bi bi-exclamation-triangle-fill"></i>'+esc(res.message||'เกิดข้อผิดพลาด'));
            }
        })
        .fail(function(){ setResult('error','<i class="bi bi-exclamation-triangle-fill"></i>เกิดข้อผิดพลาดในการเชื่อมต่อ'); })
        .always(function(){ b.classList.remove('is-loading'); updateSubmit(); });
    });

    $(q('[data-role=close]')).on('click', function(){
        if (typeof erpHideModal==='function') erpHideModal('#main-modal');
        else $('#main-modal').modal('hide');
    });
})();
</script>
