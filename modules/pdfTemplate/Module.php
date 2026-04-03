<?php

namespace app\modules\pdfTemplate;

use app\modules\pdfTemplate\services\DataSourceRegistry;
use Yii;

/**
 * PDF Template module — resolution-independent positioning for PDF layouts.
 * Used for "Template รายงานขอไปราชการ" and similar document templates.
 *
 * Data sources: configure via params['pdfTemplate.dataSources']. See README in module.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\pdfTemplate\controllers';

    /** @var DataSourceRegistry|null */
    public $dataSourceRegistry;

    /** เข้า /pdf-template แล้วไปที่ template/index */
    public $defaultRoute = 'template';

    public function init()
    {
        parent::init();
        $this->dataSourceRegistry = new DataSourceRegistry($this->getDataSourcesConfig());
    }

    /**
     * Data source config for registry: [ id => ['id'=>id, 'label'=>..., 'class'=>FQCN], ... ]
     * @return array<string, array{id: string, label: string, class: string}>
     */
    protected function getDataSourcesConfig(): array
    {
        $default = [
            'hr.development' => [
                'id' => 'hr.development',
                'label' => (new \app\modules\pdfTemplate\sources\DevelopmentDataSource())->getLabel(),
                'class' => \app\modules\pdfTemplate\sources\DevelopmentDataSource::class,
            ],
            'leave' => [
                'id' => 'leave',
                'label' => (new \app\modules\pdfTemplate\sources\LeaveDataSource())->getLabel(),
                'class' => \app\modules\pdfTemplate\sources\LeaveDataSource::class,
            ],
            'helpdesk2.repair' => [
                'id' => 'helpdesk2.repair',
                'label' => (new \app\modules\pdfTemplate\sources\HelpdeskDataSource())->getLabel(),
                'class' => \app\modules\pdfTemplate\sources\HelpdeskDataSource::class,
            ],
            'booking.vehicle.central' => [
                'id' => 'booking.vehicle.central',
                'label' => (new \app\modules\pdfTemplate\sources\BookingVehicleDataSource())->getLabel(),
                'class' => \app\modules\pdfTemplate\sources\BookingVehicleDataSource::class,
            ],
        ];
        $fromParams = Yii::$app->params['pdfTemplate.dataSources'] ?? null;
        if (is_array($fromParams) && !empty($fromParams)) {
            return array_merge($default, $fromParams);
        }
        return $default;
    }
}
