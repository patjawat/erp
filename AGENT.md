# AGENT.md — ERP UI/UX & Development Standard

> ใช้ไฟล์นี้เป็นกติกากลางสำหรับ AI Agent ทุกครั้งที่สร้างระบบใหม่ ปรับปรุง UI หรือ refactor โค้ดในระบบ ERP นี้

---

## 1. Core Development Rules

### Preserve Existing Logic

DO NOT change these unless explicitly requested:

- business logic
- database schema
- migrations
- controller flow
- routes
- permissions
- model relationships
- query behavior
- existing AJAX behavior
- existing widgets/components

Only improve:

- UI/UX
- layout
- spacing
- readability
- responsive behavior
- component structure
- visual hierarchy

---

## 2. UI/UX Design Direction

Use **clean card-based UI**.

Design style:

- white cards
- soft shadow
- rounded corners
- blue accent
- comfortable spacing
- clean typography
- minimal visual noise
- modern ERP dashboard style
- professional government enterprise style

Use:

- Bootstrap 5 utility classes
- Lucide icons
- Bootstrap Icons if needed
- FontAwesome only if already used in the existing module

Avoid:

- inline styles
- large custom CSS
- hardcoded colors
- excessive borders
- crowded layouts

Preferred Bootstrap classes:

- `shadow-sm`
- `rounded-4`
- `border-0`
- `gap-*`
- `text-muted`
- `small`
- `fw-semibold`
- `bg-primary bg-opacity-10`

---

## 3. Responsive Rules

All pages must support:

- desktop
- tablet
- mobile

Required patterns:

- `flex-column flex-sm-row`
- `flex-wrap`
- `.table-responsive`
- Bootstrap grid system
- responsive spacing
- responsive button grouping

Never create desktop-only layouts.

---

## 4. Index / Registry Page Standard

All registry/list/index pages should follow this structure:

1. Page Header / Registry Header
2. Filter/Search Section
3. KPI / Summary Cards, if needed
4. Main Data Card
5. Table or Card/Grid Content
6. Card Footer with `DataSummaryWidget`

Important rules:

- Use `DataSummaryWidget` only in the footer area of the main data card.
- Do not put `DataSummaryWidget` inside the filter/search section.
- Every index page that uses `ActiveDataProvider` should show `DataSummaryWidget` below the table/list/grid.
- Filter/search section should contain only search inputs, dropdown filters, date filters, reset buttons, and submit buttons.

---

## 5. Standard Registry Card Layout

Use this structure as the default layout pattern for index/list/registry pages.

```php
<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="px-4 py-3 border-bottom bg-body-tertiary d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">

        <div class="d-flex align-items-center gap-3">

            <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2">
                <i data-lucide="layout-dashboard"></i>
            </div>

            <div>
                <h5 class="m-0 fw-bold">ชื่อรายการ</h5>
                <div class="text-muted small">คำอธิบายเพิ่มเติมของหน้ารายการ</div>
            </div>

            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">
                <?= number_format($dataProvider->getTotalCount(), 0) ?> รายการ
            </span>

        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= Html::a('<i class="fa-solid fa-plus me-1"></i> สร้างใหม่', ['create'], [
                'class' => 'btn btn-primary rounded-3 fw-semibold',
                'data-pjax' => 0,
            ]) ?>
        </div>

    </div>

    <div class="card-body p-0">
        <?= $this->render('_list', [
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>

    <div class="card-footer bg-body border-top py-3 px-4">
        <?= DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]) ?>
    </div>

</div>
```

---

## 6. Filter / Search Section Standard

Filter/search UI must be separated from table footer and pagination.

The filter section should contain:

- search text input
- status filter
- type/category filter
- date range filter
- department filter
- submit/search button
- reset button

Do not include:

- `DataSummaryWidget`
- pagination
- total range display

Preferred filter layout:

```php
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">ค้นหา</label>
                <?= Html::textInput('q', $q ?? null, [
                    'class' => 'form-control',
                    'placeholder' => 'ค้นหาข้อมูล...',
                ]) ?>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label fw-semibold">สถานะ</label>
                <?= Html::dropDownList('status', $status ?? null, $statusItems ?? [], [
                    'class' => 'form-select',
                    'prompt' => 'ทั้งหมด',
                ]) ?>
            </div>

            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary rounded-3 fw-semibold">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> ค้นหา
                </button>
            </div>

            <div class="col-12 col-md-auto">
                <?= Html::a('ล้างค่า', ['index'], [
                    'class' => 'btn btn-outline-secondary rounded-3',
                ]) ?>
            </div>

        </div>
    </div>
</div>
```

---

## 7. Table Design Rules

Avoid old-style dense tables.

Use:

- readable spacing
- aligned content
- muted secondary text
- badges for statuses
- icons for quick scanning
- sticky action column if needed
- `.table-responsive`

Preferred:

- foreach + table layout
- table-responsive wrapper
- sticky action buttons if needed

Avoid:

- Yii2 GridView if project already uses custom table pattern
- overloaded columns
- inconsistent spacing

---

## 8. Card/Grid View Rules

Grid items should:

- use equal height cards
- contain clear hierarchy
- show status clearly
- have action buttons grouped together

Use:

- `h-100`
- `shadow-sm`
- `rounded-4`
- `card-body`
- `d-flex flex-column`

---

## 9. Form Design Rules

Forms should:

- group related fields
- use section cards
- use proper spacing
- support mobile layout
- reduce visual fatigue

Use:

- `row g-3`
- `col-md-*`
- `form-label fw-semibold`
- helper text using `small text-muted`

---

## 10. Modal Rules

All modals must:

- support responsive size
- use rounded corners
- use clean spacing
- have sticky footer actions if needed

Preferred:

- `modal-lg`
- `modal-xl`

Use:

- primary action on right
- cancel action on left

---

## 11. Button Rules

Primary action:

- `btn btn-primary`

Secondary action:

- `btn btn-outline-primary`

Danger action:

- `btn btn-outline-danger`

Avoid:

- too many colors
- too many button styles in one area

---

## 12. Status Display Rules

Statuses should use:

- badges
- soft background colors
- consistent color meanings

Preferred:

```php
<span class="badge bg-success-subtle text-success">
    ใช้งานอยู่
</span>
```

Color meaning:

- `success` = completed / good / active
- `warning` = pending / waiting / repair
- `danger` = error / rejected / damaged
- `secondary` = inactive / archived
- `primary` = main / default / total
- `info` = reference / money / additional info

---

## 13. Typography Rules

Use:

- clear hierarchy
- bold section titles
- muted secondary text

Preferred:

- `fw-bold`
- `fw-semibold`
- `small`
- `text-muted`

Avoid:

- too many font sizes
- overly decorative text

---

## 14. Icons

Preferred:

- Lucide Icons
- Bootstrap Icons
- FontAwesome only if already used

Icons should:

- improve scanning
- not overload UI
- use consistent sizing

---

## 15. AI Refactoring Rules

Before modifying files:

1. Analyze existing structure first
2. Understand current flow
3. Preserve existing functionality
4. Modify only necessary files
5. Avoid unrelated refactors
6. Avoid rewriting entire modules
7. Keep code maintainable
8. Follow existing naming conventions

---

## 16. ERP UX Philosophy

Users must understand within 3 seconds:

- what this page is
- current status
- what action to take next

UI should feel:

- modern
- lightweight
- organized
- enterprise-grade
- fast to scan

Avoid:

- clutter
- excessive text
- deeply nested layouts
- confusing action placement

---

## 17. Preferred Technology Direction

Frontend:

- Bootstrap 5
- Responsive-first
- Card-based UI

Backend:

- Yii2
- Preserve MVC structure
- Clean controller/service separation

Database:

- MySQL
- Preserve compatibility

---

## 18. Output Requirement

After every modification, provide summary:

- modified files
- UI improvements made
- preserved logic confirmation
- responsive improvements
- items needing testing

---

## 19. Language Rules

Technical instructions:

- use English

UI labels/content:

- use Thai language

System should support Thai government ERP workflows.

---

## 20. Preferred UI Keywords

Use these concepts consistently:

- clean card-based UI
- enterprise dashboard
- modern ERP
- responsive layout
- comfortable spacing
- visual hierarchy
- soft shadow
- rounded corners
- Bootstrap 5 utilities
- production-ready UI

---

## 21. KPI / Summary Card Standard

Use KPI cards for dashboard summaries, registry summaries, and important numeric indicators.

KPI cards should be clean, compact, readable, and consistent across modules.

Preferred classes:

- `card border-0 shadow-sm rounded-4 h-100`
- `card-body py-3`
- `d-flex align-items-center justify-content-between gap-3`
- `fw-bold fs-3`
- `small fw-semibold`
- `bg-primary bg-opacity-10 text-primary`
- `rounded-circle`
- `p-3`

Responsive grid:

```php
<div class="row g-3 mt-1">
    <div class="col-12 col-sm-6 col-xl-3">
        <!-- KPI Card -->
    </div>
</div>
```

KPI card example:

```php
<div class="col-12 col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between gap-3">

                <div class="min-w-0">
                    <div class="fw-bold fs-3 lh-sm">
                        <?= number_format($totalAssets ?? 0) ?>
                    </div>

                    <div class="text-primary small fw-semibold mt-2">
                        ทรัพย์สินทั้งหมด (รายการ)
                    </div>
                </div>

                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle flex-shrink-0">
                    <i data-lucide="package"></i>
                </div>

            </div>
        </div>
    </div>
</div>
```

---

## 22. DataSummaryWidget Standard

### Correct Widget Name

The correct widget name is:

```php
DataSummaryWidget
```

### Required Import

Add this at the top of the view file when using the widget:

```php
use app\components\widgets\DataSummaryWidget;
```

If the project uses a different namespace, search existing codebase for:

```php
DataSummaryWidget::widget
```

and reuse the same namespace.

### When To Use

Use `DataSummaryWidget` when the page has:

- `ActiveDataProvider`
- index/list/registry page
- table view
- grid/card view
- paginated data

### Where To Use

Use it only in the footer of the main data card.

Correct structure:

```text
Main Data Card
├── card-header
├── card-body
│   └── table / grid / list
└── card-footer
    └── DataSummaryWidget
```

Do not place it in:

```text
Filter/Search Card
Header Toolbar
KPI Card
Modal
Form Section
```

### Standard Usage

```php
<div class="card-footer bg-body border-top py-3 px-4">
    <?= DataSummaryWidget::widget([
        'dataProvider' => $dataProvider,
        'pagerOptions' => [],
    ]) ?>
</div>
```

### Full Example With Table

```php
<?php

use app\components\widgets\DataSummaryWidget;

?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-0">

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>รายการ</th>
                        <th>สถานะ</th>
                        <th class="text-end">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                        <tr>
                            <td><?= Html::encode($model->name ?? '-') ?></td>
                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    ใช้งานอยู่
                                </span>
                            </td>
                            <td class="text-end">
                                <?= Html::a('ดูข้อมูล', ['view', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-primary rounded-3',
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <div class="card-footer bg-body border-top py-3 px-4">
        <?= DataSummaryWidget::widget([
            'dataProvider' => $dataProvider,
            'pagerOptions' => [],
        ]) ?>
    </div>

</div>
```

### DataSummaryWidget Rules

Use:

- clean spacing
- readable pagination
- responsive layout
- footer separation from table content

Preferred footer classes:

- `bg-body`
- `border-top`
- `py-3`
- `px-4`

Avoid:

- cramped pagination
- pagination outside card
- inconsistent footer spacing
- placing `DataSummaryWidget` in filter/search card
- replacing `DataSummaryWidget` with custom pagination if the project already uses this widget

---

## 23. Final Checklist For AI Agent

Before finishing any UI task, verify:

- Bootstrap 5 classes are used
- UI is responsive
- business logic is preserved
- no database schema changed
- filter/search section does not contain `DataSummaryWidget`
- main data card footer contains `DataSummaryWidget` when `$dataProvider` exists
- table/list/grid is readable
- actions are easy to find
- Thai labels are preserved
- summary of modified files is provided
