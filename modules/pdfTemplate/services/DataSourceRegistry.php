<?php

namespace app\modules\pdfTemplate\services;

use app\modules\pdfTemplate\contracts\DataSourceInterface;
use Yii;

/**
 * Registry of PDF template data sources. Resolves sources from config and caches field definitions.
 */
class DataSourceRegistry
{
    /** @var array<string, array{id: string, label: string, class: string}> */
    private array $sources = [];

    /** @var array<string, array<int, array{source: string, label: string}>> */
    private array $fieldCache = [];

    public function __construct(array $sourcesConfig = [])
    {
        $this->sources = $sourcesConfig;
    }

    /**
     * List available sources for UI (id + label).
     *
     * @return array<int, array{id: string, label: string}>
     */
    public function getSources(): array
    {
        $out = [];
        foreach ($this->sources as $id => $config) {
            $out[] = [
                'id' => is_array($config) ? ($config['id'] ?? $id) : $id,
                'label' => is_array($config) ? ($config['label'] ?? $id) : $this->getSourceLabel($id, $config),
            ];
        }
        return $out;
    }

    private function getSourceLabel(string $id, $config): string
    {
        $instance = $this->resolveInstance($id, $config);
        return $instance ? $instance->getLabel() : $id;
    }

    /**
     * Get field definitions for a source (cached).
     *
     * @return array<int, array{source: string, label: string}>
     */
    public function getFieldDefinitions(string $sourceId): array
    {
        if (isset($this->fieldCache[$sourceId])) {
            return $this->fieldCache[$sourceId];
        }
        $config = $this->sources[$sourceId] ?? null;
        if ($config === null) {
            return [];
        }
        $instance = $this->resolveInstance($sourceId, $config);
        $fields = $instance ? $instance->getFieldDefinitions() : [];
        $this->fieldCache[$sourceId] = $fields;
        return $fields;
    }

    /**
     * @param string $sourceId
     * @param array|string $config
     */
    private function resolveInstance(string $sourceId, $config): ?DataSourceInterface
    {
        $class = is_array($config) ? ($config['class'] ?? null) : $config;
        if (!$class || !is_string($class)) {
            return null;
        }
        if (!is_subclass_of($class, DataSourceInterface::class)) {
            return null;
        }
        try {
            $instance = new $class();
            return $instance instanceof DataSourceInterface ? $instance : null;
        } catch (\Throwable $e) {
            Yii::warning("PdfTemplate DataSource {$sourceId}: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
