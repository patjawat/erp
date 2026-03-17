<?php

namespace app\modules\pdfTemplate\contracts;

/**
 * Contract for PDF template data sources.
 * Modules register a data source to expose dynamic fields (model attributes, relations, JSON paths).
 *
 * Each field definition must have:
 * - source: string  Dot path for value resolution (e.g. "officer_name", "createdByEmp.fullname", "data_json.location")
 * - label: string  Display label for the UI
 */
interface DataSourceInterface
{
    /**
     * Human-readable name for this source (e.g. "ใบขอไปราชการ").
     */
    public function getLabel(): string;

    /**
     * List of fields available for placement on the template.
     *
     * @return array<int, array{source: string, label: string}>
     */
    public function getFieldDefinitions(): array;
}
