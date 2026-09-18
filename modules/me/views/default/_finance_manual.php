<?php

use app\components\SiteHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * ป็อปอัปคู่มือการเงิน (survival guide)
 * ฝังไว้ในเมนู /me — เปิดจากปุ่ม "คู่มือ"
 * โครง render ด้วย JS จาก /finance/manual/data
 */
$siteInfo = SiteHelper::getInfo();
$externalManual = !empty($siteInfo['manual']) ? $siteInfo['manual'] : null;

$urls = [
    'data' => Url::to(['/finance/manual/data']),
    'itemSave' => Url::to(['/finance/manual/item-save']),
    'itemDelete' => Url::to(['/finance/manual/item-delete']),
    'itemMove' => Url::to(['/finance/manual/item-move']),
    'topicSave' => Url::to(['/finance/manual/topic-save']),
    'topicDelete' => Url::to(['/finance/manual/topic-delete']),
    'catSave' => Url::to(['/finance/manual/category-save']),
    'catDelete' => Url::to(['/finance/manual/category-delete']),
];
?>
<div class="modal fade fin-manual" id="finManualModal" tabindex="-1" aria-hidden="true"
     data-urls='<?= Html::encode(json_encode($urls, JSON_UNESCAPED_SLASHES)) ?>'>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header fin-manual__header text-white border-0">
                <div class="d-flex align-items-center gap-3">
                    <span class="fin-manual__logo"><i class="bi bi-journal-bookmark-fill"></i></span>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold">คู่มือการเงิน</h5>
                        <div class="small opacity-75">เตรียมเอกสารอย่างไร เมื่อจะเบิก–ยืมเงิน</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light fin-manual__edit-toggle d-none" id="finManualEditToggle">
                        <i class="bi bi-pencil-square me-1"></i>แก้ไข
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
            </div>

            <div class="fin-manual__searchbar">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="finManualSearch"
                           placeholder="ค้นหา เช่น ค่าเช่าบ้าน, เดินทางไปราชการ, ค่าน้ำมัน…" autocomplete="off">
                </div>
            </div>

            <div class="modal-body p-0">
                <div class="fin-manual__layout">
                    <aside class="fin-manual__menu" id="finManualMenu">
                        <div class="fin-manual__loading text-center text-muted py-5">
                            <div class="spinner-border spinner-border-sm me-2"></div>กำลังโหลดคู่มือ…
                        </div>
                    </aside>
                    <section class="fin-manual__detail" id="finManualDetail">
                        <div class="fin-manual__empty text-center text-muted">
                            <i class="bi bi-arrow-left-circle d-none d-md-inline fs-1 opacity-25"></i>
                            <i class="bi bi-arrow-up-circle d-md-none fs-1 opacity-25"></i>
                            <p class="mt-3 mb-0">เลือกหัวข้อทางซ้ายเพื่อดูรายการเอกสารที่ต้องเตรียม</p>
                        </div>
                    </section>
                </div>
            </div>

            <div class="modal-footer fin-manual__footer border-0 py-2">
                <div class="me-auto small text-muted">
                    <i class="bi bi-building me-1"></i>อ้างอิงคู่มือการเงินและบัญชี
                </div>
                <?php if ($externalManual): ?>
                    <a href="<?= Html::encode($externalManual) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>คู่มือภายนอก
                    </a>
                <?php endif; ?>
                <a href="<?= Url::to(['/me/guide']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-life-preserver me-1"></i>คู่มือใช้งานระบบ
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<CSS
.fin-manual .modal-content { border-radius: 1rem; overflow: hidden; }
.fin-manual__header {
    background: linear-gradient(135deg, #7a1f4f 0%, #a63a6f 55%, #c85c8e 100%);
    padding: 1rem 1.25rem;
    display: flex; align-items: center; justify-content: space-between;
}
.fin-manual__logo {
    width: 44px; height: 44px; border-radius: 12px; flex: 0 0 auto;
    background: rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
}
.fin-manual__searchbar { padding: .75rem 1.25rem; border-bottom: 1px solid var(--bs-border-color, #e9ecef); background: #fff; }
.fin-manual__searchbar .input-group-text, .fin-manual__searchbar .form-control { border-color: var(--bs-border-color, #e2e5ea); }
.fin-manual__searchbar .form-control:focus { box-shadow: none; }

.fin-manual__layout { display: flex; min-height: 60vh; }
.fin-manual__menu {
    width: 340px; flex: 0 0 340px; border-right: 1px solid var(--bs-border-color, #eef0f2);
    background: #faf9fb; overflow-y: auto; max-height: 68vh; padding: .5rem 0;
}
.fin-manual__detail { flex: 1 1 auto; overflow-y: auto; max-height: 68vh; padding: 1.25rem 1.5rem; }

.fin-manual__cat-title {
    font-size: .72rem; font-weight: 700; letter-spacing: .02em; text-transform: none;
    color: #7a1f4f; padding: .85rem 1rem .35rem; display: flex; align-items: center; gap: .5rem;
}
.fin-manual__cat-title .bi { opacity: .8; }
.fin-manual__topic {
    display: block; width: 100%; text-align: right; text-align: start;
    border: 0; background: transparent; color: #3a3a3a;
    padding: .5rem 1rem .5rem 2.35rem; font-size: .9rem; line-height: 1.35; position: relative; cursor: pointer;
    border-left: 3px solid transparent;
}
.fin-manual__topic::before {
    content: "\F135"; font-family: "bootstrap-icons"; position: absolute; left: 1rem; top: .55rem;
    color: #c85c8e; opacity: .55; font-size: .8rem;
}
.fin-manual__topic:hover { background: #f2e7ee; }
.fin-manual__topic.active { background: #fff; border-left-color: #a63a6f; color: #7a1f4f; font-weight: 600; }

.fin-manual__detail-title { color: #7a1f4f; font-weight: 700; margin-bottom: .25rem; }
.fin-manual__detail-intro { color: #555; font-size: .92rem; }
.fin-manual__count { font-size: .78rem; color: #888; }

.fin-manual__list { list-style: none; margin: 1rem 0 0; padding: 0; counter-reset: docn; }
.fin-manual__li { display: flex; gap: .65rem; padding: .6rem .25rem; border-bottom: 1px dashed #ececec; align-items: flex-start; }
.fin-manual__li:last-child { border-bottom: 0; }
.fin-manual__li .num {
    flex: 0 0 auto; width: 24px; height: 24px; border-radius: 50%;
    background: #a63a6f; color: #fff; font-size: .74rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; margin-top: .1rem;
}
.fin-manual__li .txt { flex: 1 1 auto; font-size: .93rem; line-height: 1.5; }
.fin-manual__li--warning { background: #fff5f5; border-radius: .5rem; border-bottom: 0; padding: .65rem .75rem; margin: .25rem 0; }
.fin-manual__li--warning .num { background: #dc3545; }
.fin-manual__li--warning .txt { color: #b02a37; }
.fin-manual__li--note { background: #f5f8ff; border-radius: .5rem; border-bottom: 0; padding: .65rem .75rem; margin: .25rem 0; }
.fin-manual__li--note .num { background: #0d6efd; }
.fin-manual__li--note .txt { color: #274b8f; }
.fin-manual__li .badge-kind { font-size: .68rem; }

.fin-manual__empty { padding: 3.5rem 1rem; }
.fin-manual__actions { display: flex; gap: .35rem; flex: 0 0 auto; }
.fin-manual__actions .btn { --bs-btn-padding-y: .1rem; --bs-btn-padding-x: .4rem; --bs-btn-font-size: .78rem; }
.fin-manual.edit-on .fin-manual__topic { padding-right: 2rem; }
.fin-manual__editrow { margin-top: .5rem; }
.fin-manual__addbtn { --bs-btn-font-size: .82rem; }

/* mobile */
@media (max-width: 767.98px) {
    .fin-manual__layout { flex-direction: column; min-height: auto; }
    .fin-manual__menu { width: 100%; flex: none; max-height: none; border-right: 0; border-bottom: 1px solid #eef0f2; }
    .fin-manual__detail { max-height: none; }
    .fin-manual.detail-open .fin-manual__menu { display: none; }
    .fin-manual:not(.detail-open) .fin-manual__detail { display: none; }
}
CSS);

$this->registerJs(<<<'JS'
(function () {
    var modalEl = document.getElementById('finManualModal');
    if (!modalEl || modalEl.dataset.finInit) return;
    modalEl.dataset.finInit = '1';

    var URLS = {};
    try { URLS = JSON.parse(modalEl.getAttribute('data-urls') || '{}'); } catch (e) {}

    var menuEl = document.getElementById('finManualMenu');
    var detailEl = document.getElementById('finManualDetail');
    var searchEl = document.getElementById('finManualSearch');
    var editToggle = document.getElementById('finManualEditToggle');
    var layoutEl = modalEl.querySelector('.fin-manual__layout');

    var DATA = null, KINDS = {}, canEdit = false, loaded = false, editing = false;
    var activeTopicId = null;

    function csrfToken() {
        if (window.yii && typeof yii.getCsrfToken === 'function') return yii.getCsrfToken();
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function csrfParam() {
        if (window.yii && typeof yii.getCsrfParam === 'function') return yii.getCsrfParam();
        var m = document.querySelector('meta[name="csrf-param"]');
        return m ? m.getAttribute('content') : '_csrf';
    }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function post(url, params) {
        var body = new FormData();
        body.append(csrfParam(), csrfToken());
        Object.keys(params || {}).forEach(function (k) { body.append(k, params[k]); });
        return fetch(url, { method: 'POST', body: body, credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); });
    }

    function load() {
        fetch(URLS.data, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                DATA = res.categories || [];
                KINDS = res.kinds || {};
                canEdit = !!res.canEdit;
                editToggle.classList.toggle('d-none', !canEdit);
                renderMenu();
                if (activeTopicId) { var t = findTopic(activeTopicId); if (t) renderDetail(t); }
            })
            .catch(function () {
                menuEl.innerHTML = '<div class="text-center text-danger py-5">โหลดคู่มือไม่สำเร็จ</div>';
            });
    }

    function findTopic(id) {
        for (var i = 0; i < DATA.length; i++) {
            for (var j = 0; j < DATA[i].topics.length; j++) {
                if (DATA[i].topics[j].id === id) return DATA[i].topics[j];
            }
        }
        return null;
    }
    function findCatOfTopic(id) {
        for (var i = 0; i < DATA.length; i++) {
            for (var j = 0; j < DATA[i].topics.length; j++) {
                if (DATA[i].topics[j].id === id) return DATA[i];
            }
        }
        return null;
    }

    // ---- เมนูซ้าย ----------------------------------------------------------
    function renderMenu() {
        var q = (searchEl.value || '').trim().toLowerCase();
        var html = '';
        DATA.forEach(function (cat) {
            var topics = cat.topics;
            if (q) {
                topics = topics.filter(function (t) {
                    if (t.title.toLowerCase().indexOf(q) >= 0) return true;
                    return t.items.some(function (it) { return it.content.toLowerCase().indexOf(q) >= 0; });
                });
            }
            if (!topics.length && q) return;
            html += '<div class="fin-manual__cat">';
            html += '<div class="fin-manual__cat-title"><i class="bi ' + esc(cat.icon || 'bi-folder2-open') + '"></i>'
                + '<span class="flex-grow-1">' + esc(cat.title) + '</span>';
            if (editing) {
                html += '<span class="fin-manual__actions">'
                    + '<button class="btn btn-outline-secondary" data-act="cat-edit" data-id="' + cat.id + '" title="แก้ไขหมวด"><i class="bi bi-pencil"></i></button>'
                    + '<button class="btn btn-outline-danger" data-act="cat-del" data-id="' + cat.id + '" title="ลบหมวด"><i class="bi bi-trash"></i></button>'
                    + '</span>';
            }
            html += '</div>';
            topics.forEach(function (t) {
                html += '<button type="button" class="fin-manual__topic' + (t.id === activeTopicId ? ' active' : '')
                    + '" data-topic="' + t.id + '">' + highlight(t.title, q) + '</button>';
            });
            if (editing) {
                html += '<div class="px-3 py-1"><button class="btn btn-sm btn-outline-primary fin-manual__addbtn" data-act="topic-add" data-cat="' + cat.id + '"><i class="bi bi-plus-lg me-1"></i>เพิ่มเรื่อง</button></div>';
            }
            html += '</div>';
        });
        if (!html) {
            html = '<div class="text-center text-muted py-5"><i class="bi bi-search fs-4 d-block mb-2 opacity-50"></i>ไม่พบหัวข้อที่ค้นหา</div>';
        }
        if (editing) {
            html += '<div class="px-3 py-3 border-top mt-2"><button class="btn btn-sm btn-primary fin-manual__addbtn w-100" data-act="cat-add"><i class="bi bi-plus-lg me-1"></i>เพิ่มหมวดใหม่</button></div>';
        }
        menuEl.innerHTML = html;
    }

    function highlight(text, q) {
        var safe = esc(text);
        if (!q) return safe;
        try {
            var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'ig');
            return safe.replace(re, '<mark class="px-0">$1</mark>');
        } catch (e) { return safe; }
    }

    // ---- รายละเอียดขวา -----------------------------------------------------
    function renderDetail(topic) {
        var cat = findCatOfTopic(topic.id);
        var docCount = topic.items.filter(function (i) { return i.kind === 'doc'; }).length;
        var h = '';
        h += '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">';
        h += '<div><button class="btn btn-sm btn-link p-0 mb-1 d-md-none text-decoration-none" data-act="back"><i class="bi bi-arrow-left me-1"></i>หัวข้อทั้งหมด</button>';
        h += '<div class="fin-manual__count">' + esc(cat ? cat.title : '') + '</div>';
        h += '<h5 class="fin-manual__detail-title" id="finManualDetailTitle">' + esc(topic.title) + '</h5>';
        if (topic.intro) h += '<div class="fin-manual__detail-intro">' + esc(topic.intro) + '</div>';
        h += '</div>';
        h += '<div class="text-end fin-manual__actions">';
        h += '<button class="btn btn-outline-secondary" data-act="print" title="พิมพ์"><i class="bi bi-printer"></i></button>';
        if (editing) {
            h += '<button class="btn btn-outline-secondary" data-act="topic-edit" data-id="' + topic.id + '" title="แก้ไขเรื่อง"><i class="bi bi-pencil"></i></button>';
            h += '<button class="btn btn-outline-danger" data-act="topic-del" data-id="' + topic.id + '" title="ลบเรื่อง"><i class="bi bi-trash"></i></button>';
        }
        h += '</div></div>';

        h += '<div class="fin-manual__count mt-2"><i class="bi bi-check2-square me-1"></i>เอกสารที่ต้องเตรียม ' + docCount + ' รายการ</div>';

        h += '<ul class="fin-manual__list" id="finManualItems">';
        var n = 0;
        topic.items.forEach(function (it) {
            var cls = 'fin-manual__li';
            var label = '';
            if (it.kind === 'warning') { cls += ' fin-manual__li--warning'; label = '<i class="bi bi-exclamation-triangle-fill"></i>'; }
            else if (it.kind === 'note') { cls += ' fin-manual__li--note'; label = '<i class="bi bi-info-circle-fill"></i>'; }
            var badge;
            if (it.kind === 'doc') { n++; badge = '<span class="num">' + n + '</span>'; }
            else { badge = '<span class="num">' + label + '</span>'; }
            h += '<li class="' + cls + '" data-item="' + it.id + '">';
            h += badge;
            h += '<span class="txt">' + esc(it.content) + '</span>';
            if (editing) {
                h += '<span class="fin-manual__actions">'
                    + '<button class="btn btn-outline-secondary" data-act="item-up" data-id="' + it.id + '" title="เลื่อนขึ้น"><i class="bi bi-arrow-up"></i></button>'
                    + '<button class="btn btn-outline-secondary" data-act="item-down" data-id="' + it.id + '" title="เลื่อนลง"><i class="bi bi-arrow-down"></i></button>'
                    + '<button class="btn btn-outline-secondary" data-act="item-edit" data-id="' + it.id + '" title="แก้ไข"><i class="bi bi-pencil"></i></button>'
                    + '<button class="btn btn-outline-danger" data-act="item-del" data-id="' + it.id + '" title="ลบ"><i class="bi bi-trash"></i></button>'
                    + '</span>';
            }
            h += '</li>';
        });
        h += '</ul>';
        if (editing) {
            h += '<div class="mt-2"><button class="btn btn-sm btn-outline-primary fin-manual__addbtn" data-act="item-add" data-topic="' + topic.id + '"><i class="bi bi-plus-lg me-1"></i>เพิ่มรายการเอกสาร</button></div>';
        }
        detailEl.innerHTML = h;
    }

    // ---- editor helpers ----------------------------------------------------
    function kindOptions(sel) {
        return Object.keys(KINDS).map(function (k) {
            return '<option value="' + k + '"' + (k === sel ? ' selected' : '') + '>' + esc(KINDS[k]) + '</option>';
        }).join('');
    }
    function itemEditor(topicId, item) {
        var isNew = !item;
        var box = document.createElement('div');
        box.className = 'card card-body bg-light border fin-manual__editrow';
        box.innerHTML =
            '<textarea class="form-control mb-2" rows="2" placeholder="ข้อความรายการ">' + esc(item ? item.content : '') + '</textarea>'
            + '<div class="d-flex gap-2 align-items-center">'
            + '<select class="form-select form-select-sm" style="max-width:180px">' + kindOptions(item ? item.kind : 'doc') + '</select>'
            + '<button class="btn btn-sm btn-primary" data-save="1"><i class="bi bi-check-lg me-1"></i>บันทึก</button>'
            + '<button class="btn btn-sm btn-outline-secondary" data-cancel="1">ยกเลิก</button>'
            + '<span class="text-danger small ms-auto err"></span>'
            + '</div>';
        var ta = box.querySelector('textarea'), sel = box.querySelector('select'), err = box.querySelector('.err');
        box.querySelector('[data-cancel]').onclick = function () { load2(); };
        box.querySelector('[data-save]').onclick = function () {
            var content = ta.value.trim();
            if (!content) { err.textContent = 'กรุณากรอกข้อความ'; return; }
            var params = { content: content, kind: sel.value };
            if (isNew) params.topic_id = topicId; else params.id = item.id;
            post(URLS.itemSave, params).then(function (res) {
                if (res.ok) load2(); else err.textContent = (res.errors || ['บันทึกไม่สำเร็จ']).join(', ');
            });
        };
        return box;
    }
    // reload keeping active topic
    function load2() { load(); }

    function promptTopic(catId, topic) {
        var isNew = !topic;
        var title = window.prompt(isNew ? 'ชื่อเรื่องใหม่' : 'แก้ชื่อเรื่อง', topic ? topic.title : '');
        if (title === null) return;
        title = title.trim(); if (!title) return;
        var params = { title: title };
        if (isNew) params.category_id = catId; else { params.id = topic.id; params.intro = topic.intro || ''; params.note = topic.note || ''; }
        post(URLS.topicSave, params).then(function (res) { if (res.ok) { if (isNew) activeTopicId = res.id; load(); } });
    }
    function promptCategory(cat) {
        var isNew = !cat;
        var title = window.prompt(isNew ? 'ชื่อหมวดใหม่' : 'แก้ชื่อหมวด', cat ? cat.title : '');
        if (title === null) return;
        title = title.trim(); if (!title) return;
        var params = { title: title, icon: cat ? (cat.icon || '') : 'bi-folder2-open', description: cat ? (cat.description || '') : '' };
        if (!isNew) params.id = cat.id;
        post(URLS.catSave, params).then(function (res) { if (res.ok) load(); });
    }

    // ---- events ------------------------------------------------------------
    menuEl.addEventListener('click', function (e) {
        var topicBtn = e.target.closest('[data-topic]');
        if (topicBtn) {
            activeTopicId = parseInt(topicBtn.getAttribute('data-topic'), 10);
            renderMenu();
            var t = findTopic(activeTopicId);
            if (t) { renderDetail(t); modalEl.classList.add('detail-open'); }
            return;
        }
        var act = e.target.closest('[data-act]');
        if (!act) return;
        var a = act.getAttribute('data-act');
        if (a === 'cat-add') promptCategory(null);
        else if (a === 'cat-edit') promptCategory(cat(act.getAttribute('data-id')));
        else if (a === 'cat-del') { if (confirm('ลบหมวดนี้และเรื่องทั้งหมดในหมวด?')) post(URLS.catDelete, { id: act.getAttribute('data-id') }).then(function(r){ if(r.ok){ activeTopicId=null; detailEl.innerHTML=''; load(); }}); }
        else if (a === 'topic-add') promptTopic(act.getAttribute('data-cat'), null);
    });

    detailEl.addEventListener('click', function (e) {
        var act = e.target.closest('[data-act]');
        if (!act) return;
        var a = act.getAttribute('data-act');
        var topic = findTopic(activeTopicId);
        if (a === 'back') { modalEl.classList.remove('detail-open'); }
        else if (a === 'print') printTopic(topic);
        else if (a === 'topic-edit') promptTopic(null, topic);
        else if (a === 'topic-del') { if (confirm('ลบเรื่องนี้ทั้งหมด?')) post(URLS.topicDelete, { id: topic.id }).then(function(r){ if(r.ok){ activeTopicId=null; detailEl.innerHTML=''; modalEl.classList.remove('detail-open'); load(); }}); }
        else if (a === 'item-add') { var box = itemEditor(topic.id, null); act.parentNode.insertBefore(box, act); box.querySelector('textarea').focus(); }
        else if (a === 'item-edit') {
            var it = itemById(topic, act.getAttribute('data-id'));
            var li = act.closest('.fin-manual__li');
            var box = itemEditor(topic.id, it); li.parentNode.insertBefore(box, li.nextSibling); li.style.display='none'; box.querySelector('textarea').focus();
        }
        else if (a === 'item-del') { if (confirm('ลบรายการนี้?')) post(URLS.itemDelete, { id: act.getAttribute('data-id') }).then(function(r){ if(r.ok) load(); }); }
        else if (a === 'item-up') post(URLS.itemMove, { id: act.getAttribute('data-id'), dir: 'up' }).then(function(r){ if(r.ok) load(); });
        else if (a === 'item-down') post(URLS.itemMove, { id: act.getAttribute('data-id'), dir: 'down' }).then(function(r){ if(r.ok) load(); });
    });

    function cat(id) { id = parseInt(id, 10); return DATA.filter(function (c) { return c.id === id; })[0]; }
    function itemById(topic, id) { id = parseInt(id, 10); return topic.items.filter(function (i) { return i.id === id; })[0]; }

    editToggle.addEventListener('click', function () {
        editing = !editing;
        modalEl.classList.toggle('edit-on', editing);
        editToggle.classList.toggle('btn-light', !editing);
        editToggle.classList.toggle('btn-warning', editing);
        editToggle.innerHTML = editing ? '<i class="bi bi-check2 me-1"></i>เสร็จสิ้น' : '<i class="bi bi-pencil-square me-1"></i>แก้ไข';
        renderMenu();
        var t = findTopic(activeTopicId); if (t) renderDetail(t);
    });

    var searchTimer;
    searchEl.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(renderMenu, 150);
    });

    function printTopic(topic) {
        if (!topic) return;
        var cat = findCatOfTopic(topic.id);
        var rows = '', n = 0;
        topic.items.forEach(function (it) {
            var lead;
            if (it.kind === 'doc') { n++; lead = n + '.'; }
            else if (it.kind === 'warning') lead = '⚠';
            else lead = '•';
            rows += '<tr><td style="vertical-align:top;padding:4px 8px;white-space:nowrap;font-weight:bold">' + lead + '</td>'
                + '<td style="padding:4px 8px">' + esc(it.content) + '</td></tr>';
        });
        var w = window.open('', '_blank');
        w.document.write('<html><head><meta charset="utf-8"><title>' + esc(topic.title) + '</title>'
            + '<style>body{font-family:"TH Sarabun New",Tahoma,sans-serif;font-size:16px;padding:24px;color:#222}'
            + 'h2{margin:0 0 2px}h4{margin:0 0 12px;color:#555;font-weight:normal}'
            + 'table{border-collapse:collapse;width:100%}td{border-bottom:1px solid #eee}</style></head><body>'
            + '<h4>' + esc(cat ? cat.title : '') + '</h4>'
            + '<h2>' + esc(topic.title) + '</h2>'
            + (topic.intro ? '<p>' + esc(topic.intro) + '</p>' : '')
            + '<table>' + rows + '</table>'
            + '<p style="margin-top:24px;color:#999;font-size:13px">เอกสารที่ต้องเตรียมทั้งหมด ' + n + ' รายการ · พิมพ์จากคู่มือการเงิน</p>'
            + '</body></html>');
        w.document.close();
        setTimeout(function () { w.focus(); w.print(); }, 300);
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        if (!loaded) { loaded = true; load(); }
    });
})();
JS);
