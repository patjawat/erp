# PdfTemplate Module — Dynamic PDF Template System

This module provides resolution-independent PDF template positioning. Templates can define field placements, and the rendering engine fills values from **dynamic data sources** (model attributes, relations, JSON paths).

## Features

- **Dynamic field resolution**: Each field can use a **source path** (e.g. `officer_name`, `createdByEmp.fullname`, `data_json.location`). The engine resolves paths against the data payload at render time.
- **Data sources**: Modules register "data sources" that expose a list of available fields (path + label). The editor lets users pick a source and drag those fields onto the PDF.
- **Backward compatible**: Layouts without a `source` still use the legacy flat key lookup. Existing templates keep working.

## How to Register a Data Source (for other modules)

To expose your module’s data to PdfTemplate (so users can pick fields when designing a template):

### 1. Implement the contract

Create a class that implements `\app\modules\pdfTemplate\contracts\DataSourceInterface`:

```php
namespace app\modules\yourModule\pdfTemplate;

use app\modules\pdfTemplate\contracts\DataSourceInterface;

class YourDataSource implements DataSourceInterface
{
    public function getLabel(): string
    {
        return 'Your Document Type';
    }

    /**
     * @return array<int, array{source: string, label: string}>
     */
    public function getFieldDefinitions(): array
    {
        return [
            ['source' => 'title', 'label' => 'หัวข้อ'],
            ['source' => 'employee.fullname', 'label' => 'ชื่อพนักงาน'],
            ['source' => 'data_json.remark', 'label' => 'หมายเหตุ (จาก JSON)'],
        ];
    }
}
```

- **source**: Dot path used to get the value from the payload (e.g. `employee.fullname`, `data_json.remark`). The rendering engine supports nested arrays/objects and JSON paths.
- **label**: Display name shown in the template editor.

### 2. Register in application config

In your config (e.g. `config/params.php` or where you set `params`), add:

```php
'params' => [
    // ...
    'pdfTemplate.dataSources' => [
        'your.key' => [
            'id' => 'your.key',
            'label' => 'Your Document Type',
            'class' => \app\modules\yourModule\pdfTemplate\YourDataSource::class,
        ],
    ],
],
```

The PdfTemplate module merges this with the built-in `default` source, so the new source appears in the editor’s "แหล่งข้อมูล" dropdown (when more than one source exists).

### 3. Pass data when generating PDF

From your controller (or service), pass a payload that matches the paths you defined. You can pass a **flat** array or **nested** (e.g. model + relations):

```php
// Flat (current HR style) — paths like "officer_name", "topic" work
$data = [
    'officer_name' => $name,
    'topic' => $model->topic,
    'document_date' => $date,
];

// Or nested — paths like "createdByEmp.fullname", "data_json.location" work
$data = [
    'topic' => $model->topic,
    'createdByEmp' => $model->createdByEmp ? ['fullname' => $model->createdByEmp->fullname] : [],
    'data_json' => is_array($model->data_json) ? $model->data_json : json_decode($model->data_json, true) ?: [],
];
$pdfBinary = $service->generatePdfWithData($templateId, $data);
```

Missing segments in a path resolve to an empty string (no error).

## Example: ใบขอไปราชการ (built-in)

The module ships with `sources/DevelopmentDataSource.php` for "ใบขอไปราชการ". It is registered by default (see `Module::getDataSourcesConfig()`). It exposes flat keys (officer_name, topic, …) and nested paths (e.g. `createdByEmp.fullname`, `data_json.location`). To add more sources, use `params['pdfTemplate.dataSources']` as above.

## API (for editor UI)

- `GET /pdf-template/template/data-sources` — returns `[{ id, label }, ...]`.
- `GET /pdf-template/template/fields-for-source?template_id=1&source_id=default` — returns `{ fields: [{ source, label }, ...] }`.

Field definitions are cached per source for the request.

## Storage

- **Template**: `pdf_templates` (name, file_path, page size).
- **Layout**: `pdf_template_fields` per template; each row has `field_name` and `position_json`. The JSON can include **source** (dot path). If `source` is set, the engine uses it to resolve the value; otherwise the legacy key lookup is used.

No database schema change is required: `source` is stored inside the existing `position_json` column.
