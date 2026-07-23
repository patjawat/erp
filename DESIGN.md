# Design

Extracted from [PRODUCT.md](PRODUCT.md).

## Brand Personality

3 คำ: **เป็นทางการ · ใช้งานง่าย · เชื่อถือได้**

โทน: professional government enterprise ที่ไม่แข็งทื่อ — เน้นความชัดเจน ใช้สีน้ำเงิน (primary) เป็น anchor แสดงข้อมูลสถานะด้วย badge สีนุ่ม (subtle backgrounds) อ่านสบายตา ภาษาไทยล้วนที่ UI

อารมณ์ที่ต้องสร้างกับผู้ใช้: รู้ว่าตัวเองอยู่ตรงไหน, รู้ว่าต้องทำอะไรต่อ, ไว้ใจได้ว่าคลิกแล้วจะสำเร็จ ไม่ใช่ delight แต่เป็น confidence

**Enterprise tone = confidence ผ่าน restraint** — UI ควรหายไปกับ task ไม่ใช่เด่นแข่งกับ content; ความน่าเชื่อถือมาจาก consistency, ความหนาแน่นที่พอดี, และ feedback ที่แม่นยำ ไม่ใช่จาก decoration

## Anti-references

### Cross-register (ใช้ได้ทุกหน้า)

- **Consumer app สดใส (TikTok, IG, Shopee สไตล์)**: สีจัดจ้าน, gradient หนัก, gamification badges, micro-celebration animations
- **AI slop ERP grid**: card grid ไอคอน+title+description ซ้ำๆ ทั้งหน้า, eyebrow ตัวพิมพ์เล็กทุก section, hero-metric stats เป็น decoration
- **Glassmorphism / gradient text decorations**
- **Dense desktop table ที่ยัดลงจอเล็ก** (scroll 2 direction)

### Enterprise-specific (ban เพิ่ม)

- **Primary tint เป็น background decoration** — `rgba(primary, 0.025-0.06)` บน card/chip/panel/cart-item เป็น default background ห้ามใช้; primary สงวนสำหรับ action button, focus ring, current state เท่านั้น
- **`rounded-4` (16px) บน card/input** — consumer-app feel; ใช้ radius scale 10/8/6px (ดู Design Tokens)
- **`text-uppercase` + `letter-spacing` บน label/heading/KPI** — อ่านยาก และเป็น AI-eyebrow tell; sentence case ภาษาไทยเท่านั้น
- **Dropdown สำหรับตัวเลือก ≤5 ตัวที่ mutually exclusive** — ใช้ segmented chips แทน (scan ใน 1 คลิก)
- **Modal confirm สำหรับ action ที่ undo ได้** — ใช้ undo toast 5 วินาทีแทน; modal ใช้กับ destructive ที่ undo ไม่ได้เท่านั้น
- **Spinner กลางหน้าระหว่างโหลด content** — ใช้ skeleton ที่ match รูปร่าง content จริง
- **Animation ที่ animate layout properties** (width/height/margin) แทน transform/opacity

## Design Principles

1. **3-second rule** — ทุกหน้าผู้ใช้ต้องเข้าใจใน 3 วินาที: นี่คือหน้าอะไร, สถานะเป็นอย่างไร, ต้องกดอะไรต่อ
2. **Surface card ที่ไม่ซ้อนการ์ด** — `.surface-card` (white bg + `--radius` + soft shadow + 1px line) เป็น containment unit หลัก ห้าม card ซ้อน card
3. **สีมีความหมายเสมอ** — primary = action/focus/current state; success/warning/danger = สถานะจริง; ห้ามใช้สีเป็น decoration ล้วน; ห้าม tint background ด้วย primary
4. **Component vocabulary หนึ่งเดียว** — button, input, chip, badge ใช้ class ชุดเดียวกันทุกหน้า ตาม Component Vocabulary ด้านล่าง; reuse ก่อนสร้างใหม่
5. **State coverage ครบ** — ทุก interactive component ต้องมี default, hover, focus, active, disabled, loading, error
6. **Inline validation > modal** — ตรวจสอบทันทีและ disable action ตั้งแต่ user ยังไม่กด; modal ใช้กับ destructive irreversible เท่านั้น
7. **Undo > confirm** — สำหรับ action ที่ recover ได้ ใช้ undo toast แทน modal
8. **Motion convey state ไม่ใช่ decoration** — duration 120-280ms, ease-out, ใช้กับ state change/feedback/continuity; ห้าม orchestrated page-load
9. **Thai-first labels, English-only code** — ข้อความผู้ใช้เห็นต้องเป็นไทยกระชับ ตัวอักษร/class/ตัวแปรในโค้ดเป็นอังกฤษ
10. **Preserve over rewrite** — เพิ่ม/ปรับ UI ได้ ห้ามแตะ business logic, schema, route, permission, AJAX behavior ที่มีอยู่แล้ว

## Design Tokens

ยกจาก `modules/inventoryV2/views/sub-stock/issue.php` เป็นมาตรฐานกลาง ทุก surface ใหม่ใช้ token ชุดนี้:

### Ink (text)
```
--ink-1: #1a202c   /* body, heading */
--ink-2: #4a5568   /* labels, secondary */
--ink-3: #718096   /* muted, captions */
--ink-4: #a0aec0   /* subtle, placeholders, icon-btn default */
```

### Surface
```
--surface:   #ffffff   /* card body, primary surface */
--surface-2: #f7f9fc   /* selected panel, summary recap, surface card header */
--surface-3: #eef2f7   /* count pill default, skeleton, picker-state icon bg */
--surface-hover: #f1f5f9
```

### Line (borders)
```
--line:        rgba(15, 23, 42, 0.08)   /* card, separator, default border */
--line-strong: rgba(15, 23, 42, 0.14)   /* input, button-light, cart-item hover */
```

### Brand & Semantic
```
--primary:      #0d6efd
--primary-ink:  #0a58ca   /* hover state primary button */
--primary-soft: rgba(13, 110, 253, 0.08)   /* focus ring, segmented-active glow ONLY */
--primary-line: rgba(13, 110, 253, 0.22)

--success: #15803d   --success-soft: rgba(21,128,61,0.10)
--warning: #b45309   --warning-soft: rgba(180,83,9,0.10)
--danger:  #b91c1c   --danger-soft:  rgba(185,28,28,0.10)
```

> Bootstrap defaults (`#198754`, `#dc3545`, `#ffc107`) **เลิกใช้** — สีเหล่านี้ contrast กับ tinted bg ไม่พอ ใช้ token semantic ด้านบนแทน

### Radius
```
--radius:    10px   /* card, modal, large container */
--radius-sm: 8px    /* input, button, segmented container, qty-stepper */
--radius-xs: 6px    /* small chip, badge inside, skeleton, kbd */
999px (pill)        /* chip, badge, count-pill, recent-chip, ctx-chip, undo-toast */
```

`rounded-4` Bootstrap class **ห้ามใช้** บน card/input ใหม่

### Shadow
```
--shadow-1: 0 1px 2px rgba(15,23,42,0.04), 0 1px 1px rgba(15,23,42,0.03)  /* surface-card default */
--shadow-2: 0 6px 18px rgba(15,23,42,0.06), 0 2px 4px rgba(15,23,42,0.04) /* search results, fly-clone */
```

### Motion
```
--ease:    cubic-bezier(0.16, 1, 0.3, 1)   /* default, ease-out-expo */
--ease-in: cubic-bezier(0.7, 0, 0.84, 0)   /* FLIP fly, exit */
--t-fast:  120ms   /* hover, focus, color change */
--t-mid:   180ms   /* state change, panel reveal */
--t-slow:  240ms   /* entry, recap, card-item-pop */
```

## Component Vocabulary

Canonical classes — reference implementation อยู่ใน `modules/inventoryV2/views/sub-stock/issue.php`. หน้าใหม่ต้อง reuse ก่อนสร้างใหม่

### Container & Layout
| Class | ใช้เมื่อ |
|---|---|
| `.surface-card` + `.surface-card__head` + `.surface-card__title` + `.surface-card__body` | Container หลักของทุก section ในหน้า — head 0.85/1.1rem padding, body 1rem/1.1rem, ห้ามซ้อน |
| `.form-grid` + `.form-grid__row` + `.form-grid__label` | Form layout มาตรฐาน label-above-input gap 0.4rem; label `0.8rem fw-semibold ink-2` |
| `.empty-block` | "ไม่มีข้อมูล / ไม่มีสิทธิ์" full-card empty state — icon ใน rounded-3 tinted box |

### Input & Selection
| Class | spec |
|---|---|
| `.form-control-input` | min-h 42px, `--radius-sm`, `--line-strong` border, focus = `--primary` border + 3px `--primary-soft` ring |
| `.seg-control` + `.seg-control__item` | Segmented chips สำหรับ 2-5 options mutually exclusive; grid 4 col (มือถือ 2 col), padding `0.3rem`, active = `--primary` border + soft ring; รองรับ ← → keyboard nav |
| `.qty-stepper` + `.qty-stepper__btn` + `.qty-stepper__input` | Number stepper 42px; tabular-nums; focus-within = primary ring |
| `.search-input-wrap` + `.search-input__icon` + `.search-input__clear` | Search input กับ icon ซ้าย, clear ขวา; padding-left 2.5rem |

### Action
| Class | spec |
|---|---|
| `.btn-block` | Full-width button min-h 44px, `--radius-sm`, fw 600 |
| `.btn-primary` | `--primary` bg, hover `--primary-ink`, active `translateY(1px)`, focus ring 3px `--primary-soft`, disabled opacity 0.55 |
| `.btn-light` | `--surface-2` bg, `--line-strong` border, hover `--surface-hover` |
| `.btn-save` + `.btn-save__label` + `.btn-save__progress` | ปุ่ม submit ที่มี progress bar ภายในและ success state (เปลี่ยนเป็น `--success`) |
| `.icon-btn` | Icon-only ghost button 30px, hover = `--surface-hover` + `--ink-1` |

### Status & Display
| Class | spec |
|---|---|
| `.count-pill` | Soft pill นับจำนวน, default `--surface-3` + `--ink-2`, active = `--primary` + white |
| `.ctx-chip` | Sticky context bar chip — `#f1f5f9` bg, max-w 12rem ellipsis |
| `.recent-chip` | Quick re-pick chip — `--surface-2` + `--line`, hover = `--primary-soft` + `--primary-ink` |
| `.balance-hint` | Inline validation hint ใต้ input — `.is-ok` = `--success`, `.is-warn` = `--warning` |
| `.summary-recap` + `.summary-recap__row` + `.summary-totals` | DL-based context recap ใน order summary; ใช้ tabular-nums บน `__value` |

### Feedback
| Class | spec |
|---|---|
| `.picker-state` + `__icon` + `__title` + `__caption` | Empty/loading/error states ภายใน card; icon 56px rounded-14 tinted |
| `.skeleton-row` + `.skeleton-block` + `.skeleton-line` | Skeleton ที่ match รูปร่าง content (icon left + 2 lines + num right) — ห้ามใช้ generic bar |
| `.undo-toast` + `.undo-toast__btn` | Floating dark pill bottom-center มือถือ / bottom-right desktop; auto-hide 5s |
| `.kbd-hints` + `.kbd-inline` | Keyboard shortcut hint inline (desktop เท่านั้น) |
| `.flip-fly` | FLIP clone สำหรับ "เพิ่มของไปยังตะกร้า" animation |

### Stepper
| Class | spec |
|---|---|
| `.issue-stepper` + `.issue-step` + `__indicator` + `__num` + `__check` | Progress stepper 3 steps; state classes: `.is-active` (primary + soft ring), `.is-done` (success + check icon swap) |

## Typography Rules

- **Font**: ใช้ system-ui stack ที่มีอยู่; Thai font ต้องรองรับ สระบน/ล่างไม่ตัด
- **Heading scale**: ใช้ Bootstrap `h4-h6` กับ `fw-semibold` (ห้าม `fw-medium` บน heading); `.surface-card__title` = 0.95rem fw 600
- **Label**: `0.8rem fw 600 ink-2` — sentence case ภาษาไทยล้วน
- **Caption / meta**: `0.72-0.78rem ink-3`
- **ห้าม** — `text-uppercase`, `letter-spacing` บน body/label/heading; ALL CAPS ทุก context (ยกเว้น code abbreviation ≤4 ตัวอักษร เช่น "HN", "AJAX")
- **ตัวเลข**: ใช้ `font-variant-numeric: tabular-nums` บน table, cart qty, summary value, balance, totals — เพื่อ alignment คอลัมน์
- **Line height**: heading 1.2; body 1.5; meta 1.3
- **`text-wrap: balance`** ใน heading h1-h3 บน landing/empty surfaces

## State Coverage Checklist

ทุก interactive component ต้องตอบครบ 7 state ก่อน merge:

| State | Visual | Example |
|---|---|---|
| Default | base style จาก vocabulary | `.btn-primary` blue bg |
| Hover | สีเข้มขึ้น 1 step / surface-hover bg | `.btn-primary` → `--primary-ink` |
| Focus / focus-visible | 3px `--primary-soft` ring + base border `--primary` | `.form-control-input:focus` |
| Active (pressed) | `translateY(1px)` หรือ slight darkness | `.btn-primary:active` |
| Disabled | opacity 0.55, cursor not-allowed | `.btn-primary:disabled` |
| Loading | spinner ใน button หรือ skeleton แทน content | `.btn-save.is-saving` + progress bar |
| Error / warn | semantic color text + icon | `.balance-hint.is-warn` |

Selected (เพิ่ม): สำหรับ list/seg-control → `.is-active` class แทน `:focus`

## Motion Guidance

### Duration ladder
- **`--t-fast` 120ms** — hover, color/border transition, icon-btn feedback
- **`--t-mid` 180ms** — state change, panel reveal, recap enter, sticky-bar enter
- **`--t-slow` 240-280ms** — entry, cart-item-pop, success transition
- **FLIP fly**: 380-480ms (exception — ระยะทางไกล) ease-in
- **ห้ามเกิน 400ms** ใน UI ทั่วไป

### Easing
- `cubic-bezier(0.16, 1, 0.3, 1)` (ease-out-expo) — default, ทุก enter/state change
- `cubic-bezier(0.7, 0, 0.84, 0)` — exit, FLIP fly to target
- `linear` — progress bar fill
- **ห้าม** bounce, elastic, spring

### Where to use
- **ใช้**: state change (active/selected), feedback (save success), continuity (cart fly-to), reveal (panel/recap appear)
- **ห้ามใช้**: page load orchestration, decoration loop (ยกเว้น loading spinner/skeleton), hover scale > 1.02, animated layout properties (margin, width)

### Reduced motion
- `prefers-reduced-motion: reduce` → ปิด animation ทุกตัว, transition เหลือ 80ms opacity-only
- FLIP fly: skip ทั้งหมด
- Stepper indicator: instant swap
- Card pop: ไม่มี

## Interaction Patterns

### Validation
- **Inline ทันที** — disable action button ตอน user พิมพ์ ไม่ใช่รอ user กดถึงเด้ง modal
- **Hint แสดง state เสมอ** — `.balance-hint` `.is-ok` (เขียว) / `.is-warn` (เหลือง-ส้ม) / default (เทา); ห้ามใช้สีแดงบน body text เล็ก
- **Cumulative check** — สำหรับ cart/list ตรวจรวมกับสิ่งที่อยู่แล้ว ไม่ใช่แค่ input ปัจจุบัน (ดู `findCartIndex` ใน issue.php)

### Date input
- **ช่องกรอกวันที่ทุกช่องต้องใช้ widget `\app\widgets\datepicker\DatepickerThai` เสมอ** — ห้ามใช้ `<input type="date">` หรือ text input เปล่า (ผู้ใช้กรอก/อ่านเป็น พ.ศ. รูปแบบ วว/ดด/พ.ศ.)
```php
<?= \app\widgets\datepicker\DatepickerThai::widget([
    'name' => 'order_date',                                  // หรือ 'model' => $model, 'attribute' => 'order_date'
    'value' => \app\components\AppHelper::convertToThai(date('Y-m-d')),
    'options' => ['id' => 'order_date', 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
]) ?>
```
- **ค่า default / แสดงผล** = `AppHelper::convertToThai('Y-m-d')` (ค.ศ.→ไทย); **ตอนบันทึกฝั่ง server** แปลงกลับด้วย `AppHelper::convertToGregorian($input)` (ไทย→`Y-m-d`, คืน null ถ้า format ผิด) ก่อนเก็บลง DB เสมอ
- **Modal/partial ที่ inject ผ่าน AJAX** — widget ใช้ `registerJs` ซึ่งไม่รันใน content ที่ inject ต้องเรียก `thaiDatepicker('#id')` เองใน inline `<script>` ของ partial (thai.datepicker.js โหลด global ผ่าน AppAsset อยู่แล้ว) — ดู `stock-adjust/_adjust_modal.php`

### Destructive action
- **Reversible** (ลบ row จาก cart, ลบ draft) → undo toast 5 วินาที, ไม่ confirm
- **Irreversible** (ส่ง approval, ลบ entity จริง) → SweetAlert modal, ปุ่ม confirm สีตามความรุนแรง

### Keyboard shortcuts
- `/` → focus search field หลักของหน้า (เฉพาะตอน input ไม่ focused)
- `↑↓` → navigate result list
- `Enter` → execute primary action ของ context (เพิ่มเข้ารายการ, เลือก result)
- `Esc` → ปิด/ล้าง search หรือ blur
- `Ctrl+S` / `⌘+S` → submit form หลักของหน้า (ถ้า valid)
- ห้ามใช้ shortcut ที่ override browser default (`Ctrl+T`, `Ctrl+W`, ฯลฯ)

### Loading
- **Initial content load** → skeleton ที่ match รูปร่าง real (icon + lines + num)
- **Submit/action** → spinner ใน button + disable, ห้ามเด้ง full-page overlay
- **AJAX refresh ในส่วนที่ user เห็น** → opacity 0.6 + cursor wait, ไม่ replace ด้วย skeleton

### Export / ดาวน์โหลดไฟล์ (Excel/PDF)
มาตรฐานเดียวทุกปุ่ม export — flow **confirm → loading → success** ผ่าน SweetAlert2 (โหลด global แล้วผ่าน AppAsset)

**ปุ่ม:** `class="btn btn-sm btn-success"` (เขียวทึบมาตรฐาน radius โค้งปกติ **ห้ามใส่ border/สีเอง หรือทำเป็นเหลี่ยม**) + icon `bi-file-earmark-excel` + label sentence-case ไทย เช่น "Export Excel". disable จนกว่าจะมีข้อมูลให้ export

**Flow (3 สเต็ป):**
1. **confirm** — `Swal.fire` icon `question` (`iconColor` primary `#0d6efd`), แสดง context (ชื่อ item/คลัง), `confirmButtonText` มี icon excel + `confirmButtonColor: '#198754'` (เขียวแมตช์ปุ่ม), `cancelButtonText: 'ยกเลิก'`, `reverseButtons: false` (**ปุ่มยืนยันซ้าย · ปุ่มยกเลิกขวา** — เป็นมาตรฐานของทุกปุ่ม export)
2. **loading** — หลังยืนยัน `Swal.fire({ didOpen: () => Swal.showLoading(), allowOutsideClick:false, allowEscapeKey:false })` "กำลังสร้างไฟล์..."
3. **success** — icon `success` (เขียว), **auto-dismiss** `timer: 1800, timerProgressBar: true, showConfirmButton: false` แสดงชื่อไฟล์ · error → icon `error` + ข้อความ

**Technical (บังคับ):**
- ใช้ **`fetch()` + `response.blob()`** สร้าง object URL แล้ว `a.click()` ดาวน์โหลด — loading/success จึงผูกกับ **completion จริง** (ห้ามใช้ `window.location.href` + timer หลอก)
- parse ชื่อไฟล์จาก header `Content-Disposition` (fallback เป็นชื่อ default)
- **fallback**: ถ้า `!window.Swal` → ดาวน์โหลดตรงแบบเดิม (ไม่พัง)

**Restraint (enterprise):** success ต้อง auto-dismiss ไม่ค้างให้กดปิด · ห้าม confetti/celebration · ภาษาไทยล้วน · SweetAlert popup radius **12px** ปุ่ม **8px** (`customClass` — ไม่ใช้ default 16px)

**Reduced-motion:** ปิด `showClass`/`hideClass` ของ SweetAlert (ตั้งเป็น `{ popup: '' }`) + ปิด transition ปุ่ม เมื่อ `prefers-reduced-motion: reduce`

**ข้อยกเว้นที่จงใจ:** export เป็น action ปลอดภัย (undo ได้) แต่ **ใช้ confirm modal ได้** เพราะเป็นการ "สร้างไฟล์" ที่มี cost + ต้องการ success feedback ชัด — ต่างจาก toggle/ลบ row ที่ใช้ undo toast (ดูหลัก "Undo > confirm" / Destructive action) · reference: `modules/inventoryV2/views/report/_balance.php` → `exportExcel()`

### Empty states
- บอก **why** (ยังไม่มีข้อมูล / ยังไม่มีสิทธิ์ / คลังนี้ไม่มีพัสดุ) + **next action** (link หรือ button)
- ห้าม "No data" / "ไม่มีข้อมูล" loose

### AJAX modal (`.open-modal`) — canonical CRUD in-page

> **Trigger:** ผู้ใช้พูดว่า **"crud ajax"** / "ทำเป็น modal" → ใช้ pattern นี้เป็นค่าเริ่มต้นทันที (ดู PRODUCT.md → Table Conventions ข้อ 3)

มาตรฐานของ **create / edit / view-partial ที่ไม่ต้องเปลี่ยนหน้า** — โหลดฟอร์มเข้า `#main-modal` (global ใน `themes/v4/layouts/main.php`) ผ่าน AJAX แล้ว submit + reload เฉพาะตารางด้วย Pjax โดยไม่ full reload เอนจินอยู่ใน `web/js/erp.js` (`.open-modal` handler + `handleFormSubmit` + `erpReloadPjax`) — **reuse pattern นี้ก่อนเขียน modal เอง** reference: `modules/am/views/depreciation-profile/` + `DepreciationProfileController`

**1. ปุ่ม/ลิงก์ที่เปิด modal (view)**
```php
Html::a('<i data-lucide="plus"></i> เพิ่มเกณฑ์', ['create', 'title' => 'เพิ่มเกณฑ์ค่าเสื่อม'], [
    'class' => 'btn btn-primary btn-sm open-modal',
    'data'  => ['size' => 'modal-xl'],   // modal-sm|md|lg|xl|xxl
])
```
- `href` = GET url ที่โหลดเข้า modal · query `title` = หัว modal · `data-size` = ขนาด dialog
- ปุ่ม edit ในแถวตารางใช้ `open-modal` เหมือนกัน — ส่ง `id` + `title` ไปด้วย
- action ที่เป็น **หน้าเต็ม** (เช่น view ที่มี sub-form ต่อ) อย่าใส่ `open-modal`; ถ้าลิงก์นั้นอยู่ใน Pjax container ให้ใส่ `data-pjax="0"` กัน pjax ดักลิงก์

**2. ตารางต้องอยู่ใน Pjax container**
```php
<?php Pjax::begin(['id' => 'am-dp-container', 'enablePushState' => false]); ?>
    <?= GridView::widget([...]) /* หรือ custom table ตาม List page pattern */ ?>
<?php Pjax::end(); ?>
```
- id ของ container (`#am-dp-container`) คือ target ที่ controller ส่งกลับให้ reload หลัง save

**3. Controller — ตอบ JSON 2 จังหวะ** (คง fallback หน้าเต็มไว้เสมอ)
```php
public function actionCreate()
{
    $model = new Foo();
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        if (Yii::$app->request->isAjax) {                 // submit จาก modal
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'message' => 'บันทึกเรียบร้อย', 'container' => '#am-dp-container'];
        }
        return $this->redirect(['view', 'id' => $model->id]);   // fallback หน้าเต็ม
    }
    if (Yii::$app->request->isAjax) {                     // GET / validation-fail → ส่งฟอร์มเข้า modal
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['title' => Yii::$app->request->get('title'), 'content' => $this->renderAjax('_form', ['model' => $model])];
    }
    return $this->render('create', ['model' => $model]);
}
```
- GET → `{title, content}` (`content` มาจาก `renderAjax` ซึ่ง inline JS ที่ `registerJs` ไว้ให้รันใน modal) · optional `footer`, `initCallback` (ชื่อ global fn รันหลังเปิด modal)
- POST สำเร็จ → `{status:'success', container:'#...'}` · ผิดพลาด → `{status:'error', message:'...'}`
- validation ผ่าน ActiveForm ajax คืน error array ตามปกติ (erp.js เติมข้อความให้เอง)

**4. `_form` — ผูก `handleFormSubmit` (ทำงานทั้ง modal + หน้าเต็ม)**
```php
<?php $form = ActiveForm::begin(['id' => 'dp-form', 'options' => ['data-list-url' => Url::to(['index'])]]); ?>
    ... fields ...
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data' => ['bs-dismiss' => 'modal']]) ?>
    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end();
$this->registerJs(<<<JS
handleFormSubmit('#dp-form', null, async function (r) {
    var c = r && r.container;
    if (c && document.querySelector(c) && typeof erpReloadPjax === 'function' && erpReloadPjax(c)) return; // โหมด modal → reload เฉพาะตาราง
    var url = document.querySelector('#dp-form').getAttribute('data-list-url');
    url ? window.location.href = url : location.reload();  // fallback หน้าเต็ม
});
JS); ?>
```
- `handleFormSubmit(sel, actionUrl, successCallback)` = confirm (SweetAlert) → loading → ajax POST → ปิด modal + success toast แล้วเรียก `successCallback(response)` · ห้าม return `redirect_url` จาก controller เมื่ออยากใช้ pjax (มันจะ override การ reload)
- `_form` **ไม่ห่อ card เอง** (modal-body มี padding แล้ว) — หน้าเต็ม create/update เป็นฝั่งที่ห่อ `card > card-body`
- form 1 อันใช้ id คงที่ได้ (`handleFormSubmit` ใช้ `.off().on` กัน bind ซ้ำ) · ปุ่มยกเลิกใช้ `data-bs-dismiss="modal"`

**Restraint:** ใช้ `.open-modal` กับ CRUD form ที่ inline ในหน้า list เท่านั้น — destructive irreversible ยังใช้ SweetAlert (ดู Destructive action) · reversible ยังใช้ undo toast

## Data Display

### Desktop (≥992px)
- **Table** เป็น primary affordance สำหรับข้อมูลที่ต้องเทียบ (เปรียบเทียบ rows, sort, filter)
- Row 32-40px; padding 0.5-0.65rem; ห้ามใช้ row สูง 56px+ (เปลือง real estate)
- Column alignment: text **left**, number **right** + `tabular-nums`, date **right** + `tabular-nums`
- Header: sticky, `--surface-2` bg, `fw 600`, no all-caps
- Zebra striping: ใช้ `:nth-child(even) { background: rgba(0,0,0,0.012); }` เท่านั้น — เบาๆ

### Mobile (<992px)
- Table ที่กว้างเกินจอ → collapse เป็น card list (ดู `activity-feed` ใน `use_history.php`)
- ห้าม horizontal scroll table; ห้าม truncate column สำคัญ
- Card: avatar/icon ซ้าย, content กลาง, value/qty ขวา

### Number & status
- ตัวเลขจำนวนเงิน: ใช้ thousand separator + decimal คงที่ตาม unit (บาท: `0`, %: `1`, qty: `0-2`)
- Status badge: soft tinted bg + filled icon + ≤3 colors ต่อหน้า; ไม่มีกรอบ (ใช้ bg ตัวเองเป็น containment)
- "ไม่มีค่า": แสดง `—` (em dash) สีอ่อน `--ink-4` ไม่ใช่ `null` หรือ `-` ธรรมดา (em dash ในที่นี้คือ glyph ในข้อมูลแสดงผล ไม่เกี่ยวกับการห้ามใน body copy)

### List page pattern (canonical)

Reference implementation: [`modules/inventoryV2/views/requisition/index.php`](modules/inventoryV2/views/requisition/index.php) — ทุกหน้าแสดง list (ใบขอเบิก, ใบลา, ใบจอง, ใบแจ้งซ่อม, รายการพัสดุ, ฯลฯ) ต้อง reuse pattern นี้ก่อนสร้างใหม่

**Structural rules**
1. **ไม่ใช้ GridView** — เขียน markup เองด้วย `<table>` (desktop) + `<ul>` (mobile) เพื่อ control density และ semantic ครบ; ถ้ามี legacy page ที่ยังใช้ GridView อยู่ ต้องปิด pager/summary ของ GridView เอง (`'layout' => '{items}'`) แล้วใช้ `DataSummaryWidget` แทนตามข้อ 4
2. **Container** — `card shadow-sm` (Bootstrap) + `card-body p-0` รอบ table — ไม่ใช้ `.surface-card` ที่มีหัวการ์ดสำหรับ list (overkill); หัวเรื่องอยู่ที่ page-head ของหน้าแล้ว
3. **Layout** — desktop table `.d-none .d-lg-block` + mobile cards `.d-lg-none` แสดงคู่กัน ไม่ใช้ table responsive scroll
4. **Pager (mandatory)** — ทุกหน้า list ที่แบ่งหน้า ต้องใช้ `DataSummaryWidget` (`app\components\widgets\DataSummaryWidget::widget(['dataProvider' => $dataProvider])`) วางใน `card-footer` ใต้ table เป็น single source ของทั้ง summary text (`แสดง X ถึง Y จาก Z รายการ`) และ pager — ใช้เหมือนกันทั้ง GridView-based และ custom foreach `<table>`; ห้ามเขียน `LinkPager` + `.req-pager` เอง และห้ามปล่อยให้ GridView render pager ซ้ำ

**Content rules (anti-redundancy)**
1. **Status ใช้ของ model เท่านั้น** — เรียก `Model::getStatusBadgeConfigFor($status)` ตรง ๆ ห้ามสร้าง custom palette ในหน้า list
2. **ห้าม caption ที่ duplicate status** — เช่นเมื่อ badge แสดง "รอหัวหน้าอนุมัติ" อยู่แล้ว ห้ามมี text "รอกดอนุมัติ" ใต้ avatar; เมื่อ badge แสดง "ยกเลิก" ห้ามมี "ใบนี้ถูกยกเลิก"; status badge คือ single source of truth
3. **ห้าม decoration ที่ไม่ใช่ data** — ไม่มี dot indicator, pulse animation, revision/edit chip, "หัวหน้าปรับ" tag, count chip ที่ header เว้นแต่ผู้ใช้ขอ
4. **ไอคอนเฉพาะที่ scan ได้เร็วขึ้นจริง** — date column ไม่ต้องมี calendar icon, warehouse column ไม่ต้องมี warehouse icon; ไอคอนใช้กับ status badge และ action button (`bi-search`, `bi-pencil`) เท่านั้น
5. **Action button ใช้ Bootstrap default** — `btn btn-sm btn-outline-primary` (view) + `btn btn-sm btn-outline-secondary` (edit) + `btn-outline-secondary rounded-pill` (back) + `btn-success rounded-pill` (create); ห้ามสร้าง custom `req-icon-btn` หรือ ghost variant ใหม่

**Performance rule (mandatory)**
- **Batch prefetch ก่อน loop** — เก็บ `warehouseIds`, `empIds`, `userIds` จาก `$models` แล้วยิง query เดียวต่อ table indexBy('id') / indexBy('user_id') แทน `findOne()` ต่อ row; ตัด N+1 ที่ view-layer โดยไม่แตะ controller (ดู `requisition/index.php` บรรทัด prefetch ตอนต้น)

**Column rules**
- Desktop columns: `#` (38-42px center, `--ink-3`, tabular-nums) → doc-no (link primary-ink fw 600) → people (avatar + ชื่อ + ตำแหน่ง) → warehouse/dept (max-width 14rem, no harsh ellipsis, ใช้ `title=""` เพื่อ tooltip ชื่อเต็ม) → date (tabular-nums, ขวา) → status badge → action (right-align, gap 0.25rem)
- Cell padding `0.65rem 0.9rem`, font-size `0.88rem`, header `--surface-2` sticky + `--ink-2` fw 600 0.78rem
- Hover row → `--surface-hover` (transition fast); ห้าม row stagger / enter animation

**Mobile card rules**
- `<ul role="list">` + `<li class="req-card">` padding `0.6rem`, gap `0.5rem` ระหว่าง card
- Card head: doc-no ซ้าย + status badge ขวา
- Meta line: date · warehouse-from → warehouse-to (text + `·` separator, ไม่มีไอคอน)
- People section: `border-top: 1px dashed --line` แล้ว 2 แถว (ผู้ขอ, ผู้อนุมัติ) แต่ละแถวมี label เล็ก ๆ + person block
- Edit button อยู่นอก link (`req-card__actions`) เพื่อไม่ trap focus ใน main link

**Person block (shared)**
- Avatar 32px `border-radius: 50%` + `--surface-3` bg + `1px --line` border + lazyload
- ชื่อ `--ink-1` fw 600 `0.86rem`, ตำแหน่ง `--ink-3` `0.74rem`, ทั้งคู่ ellipsis ที่ `max-width: 14rem` + `title=""`
- ไม่มี caption ใต้ (ดูข้อ 2 ของ Content rules)
- Empty: `<span class="req-empty">—</span>` (`--ink-4`)

**Empty state**
- Padding `3.5rem 1.5rem`, center align
- ไม่มี icon decoration (text-only)
- Title fw 600 `1.05rem` + caption `--ink-3` `0.88rem` + CTA button หลัก (`btn-success rounded-pill`)

**Anti-pattern check (ก่อน merge)**
- ❌ caption text ใต้ avatar ที่อธิบาย status เดียวกับ badge → ลบ
- ❌ custom status palette / status dot / pulse → กลับไปใช้ `getStatusBadgeConfigFor()`
- ❌ "X ปรับ" / "Y แก้" indicator chip บน row → ตัด (UI สำหรับ revision อยู่ที่หน้า view ไม่ใช่ list)
- ❌ row stagger / fade-in animation → ลบ; transition เฉพาะ hover
- ❌ meta chip bar (รออนุมัติ X · อนุมัติแล้ว X) ที่ header card → ลบ; ถ้าต้องการ summary ให้ผู้ใช้ขอเฉพาะ
- ❌ custom `req-icon-btn` / `req-page-btn` → ใช้ Bootstrap `btn` ที่มี
- ❌ ไอคอน calendar/warehouse/arrow ใน cell → ลบ; text + `·` + `→` พอ
- ❌ N+1 ใน loop (`Model::findOne()` ใน foreach) → batch prefetch
- ❌ custom pager (`LinkPager` ตรงๆ, `.req-pager` มือเขียน) หรือ GridView pager ที่ไม่ปิด → ใช้ `DataSummaryWidget` แทนเสมอ

## The enterprise slop test

ก่อน merge — ถามตัวเองว่า "ผู้ใช้ที่คุ้นกับ Linear / Notion / Stripe / Figma นั่งใช้หน้านี้แล้วจะเชื่อมั่นในการกระทำ หรือพะวงทุก component?"

### Failure modes ที่ต้องไม่ผ่าน
1. **มี primary color เป็น background** บน card/chip/panel/cart-item ทั้งหน้า (decoration ไม่ใช่ state)
2. **มี `rounded-4` หรือ pill** บน card/input — consumer-app feel
3. **มี `text-uppercase` + letter-spacing** บน label/section heading
4. **Component vocabulary ไม่ตรงกัน** ระหว่าง section/page (button รูปร่างต่างกัน 2 จุด → 1 จุดผิด)
5. **Modal confirm** สำหรับ action ที่ user undo ได้ใน 1 คลิก
6. **Spinner กลางหน้า** ระหว่างโหลด list content
7. **ไม่มี state ครบ 7** — กดแล้วไม่เกิดอะไร, focus ring หาย, disabled ดูเหมือน default
8. **Validation รอกดถึงแจ้ง** — user กรอกผิดแล้วยังกดเพิ่มได้
9. **Decoration motion** — hover scale 1.1, icon bounce loop, animated gradient
10. **AI-eyebrow stack** — small uppercase tracked text เหนือทุก section

### Pass หมายถึง
- ทุก state interactive ทำงานจริง (default → hover → focus → active → disabled → loading → error)
- Component reuse ผ่าน vocabulary class ไม่ใช่ inline style
- สีนำสื่อ semantic เสมอ; tinted bg เฉพาะ recap/empty state
- Motion สื่อ state change/feedback/continuity, ไม่ใช่ decoration
- Validation/feedback inline ทันที, undo เมื่อ recoverable

## Accessibility & Inclusion

- **WCAG 2.1 AA** เป็นเส้นต่ำสุด ตามมาตรฐานเว็บภาครัฐไทย (TWCAG 2.0)
- **Color contrast**: body text ≥4.5:1, large text ≥3:1 — ระวัง muted gray บนพื้น tinted ขาว
- **Touch target ≥44×44px** — mobile module ใช้นิ้วโป้งกดระหว่างเดิน; `.btn-block` และ qty-stepper ครอบไว้แล้ว
- **Keyboard / screen reader**: ใช้ semantic HTML, ARIA label ที่ลิงก์/ปุ่ม icon-only; segmented control ใช้ `role="radiogroup"` + `aria-checked`
- **Reduced motion**: เคารพ `prefers-reduced-motion` กับ animation/transition ทุกตัว (ดู Motion Guidance § Reduced motion)
- **Focus management**: focus ring ต้องเห็นชัดทุก interactive (`--primary` 3px ring), ไม่ลบ `outline` โดยไม่มี alternative
- **Live region**: `aria-live="polite"` สำหรับ panel ที่ update แบบ async (selected item, balance hint, undo toast)
- **ภาษาไทย**: font ต้องรองรับสระบน/ล่างไม่ตัด, รองรับชื่อยาว (ตำแหน่งทางการแพทย์) ไม่ overflow; ใช้ `text-overflow: ellipsis` + `max-width` ที่ context ยาวได้

## Page Layout Blocks

ทุกหน้า view ที่มีหัวข้อหน้าและ action ด้านบนต้องส่งข้อมูลให้ layout ผ่าน block กลางของระบบ ห้ามวาง header/action ซ้ำใน body ของหน้าเอง

```php
<?php $this->beginBlock('page-title'); ?>
<?= Html::encode($this->title) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => '...']) ?>
<?php $this->endBlock(); ?>
```

- ใช้ `beginBlock('page-title')` สำหรับชื่อหน้าหรือ heading หลักของ layout เท่านั้น
- ใช้ `beginBlock('sub-title')` เมื่อต้องมีคำอธิบายสั้นใต้ชื่อหน้า
- ใช้ `beginBlock('page-action')` สำหรับเมนูหลัก ปุ่ม action หรือ partial เช่น `_menu_main`
- partial เมนู/action ไม่ควรครอบด้วย `.page-action` เอง ให้ layout เป็นผู้จัดตำแหน่ง
- หลีกเลี่ยงการสร้าง `<h1>` หรือ action bar ซ้ำใน content body เมื่อ layout มี block เหล่านี้อยู่แล้ว
