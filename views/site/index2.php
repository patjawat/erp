<?php
use yii\helpers\Url;
use yii\helpers\Html;
use bs\Flatpickr\FlatpickrWidget;
use app\components\SiteHelper;
$this->title = 'Dashbroad';
$this->params['breadcrumbs'][] = $this->title;
$companyName = SiteHelper::getInfo()["company_name"];
?>
<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ยินดีต้อนรับ <?=$companyName?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>

<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?=Html::a('บุคลกร',['/hr'],['class' => 'text-muted text-uppercase fs-6'])?>
                        <h2 class="mb-0 mt-1" id="totalEmployees"></h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-rev" style="min-height: 45px;">
                            <div id="apexchartsdlqwjkgl"
                                class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                style="width: 90px; height: 45px;"><svg id="SvgjsSvg1001" width="90" height="45"
                                    xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <g id="SvgjsG1003" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(0, 0)">
                                        <defs id="SvgjsDefs1002">
                                            <clipPath id="gridRectMaskdlqwjkgl">
                                                <rect id="SvgjsRect1008" width="96" height="47" x="-3" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMaskdlqwjkgl">
                                                <rect id="SvgjsRect1009" width="92" height="47" x="-1" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <linearGradient id="SvgjsLinearGradient1015" x1="0" y1="0" x2="0" y2="1">
                                                <stop id="SvgjsStop1016" stop-opacity="0.45"
                                                    stop-color="rgba(120,89,244,0.45)" offset="0.45"></stop>
                                                <stop id="SvgjsStop1017" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                <stop id="SvgjsStop1018" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                            </linearGradient>
                                        </defs>
                                        <line id="SvgjsLine1007" x1="0" y1="0" x2="0" y2="45" stroke="#b6b6b6"
                                            stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0" width="1"
                                            height="45" fill="#b1b9c4" filter="none" fill-opacity="0.9"
                                            stroke-width="1"></line>
                                        <g id="SvgjsG1021" class="apexcharts-xaxis" transform="translate(0, 0)">
                                            <g id="SvgjsG1022" class="apexcharts-xaxis-texts-g"
                                                transform="translate(0, 2.75)"></g>
                                        </g>
                                        <g id="SvgjsG1024" class="apexcharts-grid">
                                            <g id="SvgjsG1025" class="apexcharts-gridlines-horizontal"
                                                style="display: none;">
                                                <line id="SvgjsLine1027" x1="0" y1="0" x2="90" y2="0" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1028" x1="0" y1="9" x2="90" y2="9" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1029" x1="0" y1="18" x2="90" y2="18" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1030" x1="0" y1="27" x2="90" y2="27" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1031" x1="0" y1="36" x2="90" y2="36" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1032" x1="0" y1="45" x2="90" y2="45" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                            </g>
                                            <g id="SvgjsG1026" class="apexcharts-gridlines-vertical"
                                                style="display: none;"></g>
                                            <line id="SvgjsLine1034" x1="0" y1="45" x2="90" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                            <line id="SvgjsLine1033" x1="0" y1="1" x2="0" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                        </g>
                                        <g id="SvgjsG1011" class="apexcharts-area-series apexcharts-plot-series">
                                            <g id="SvgjsG1012" class="apexcharts-series" seriesName="seriesx1"
                                                data:longestSeries="true" rel="1" data:realIndex="0">
                                                <path id="SvgjsPath1019"
                                                    d="M0 45L0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 45M90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="url(#SvgjsLinearGradient1015)" fill-opacity="1"
                                                    stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                    stroke-dasharray="0" class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMaskdlqwjkgl)"
                                                    pathTo="M 0 45L 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C 90 20.700000000000003 90 20.700000000000003 90 45M 90 20.700000000000003z"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <path id="SvgjsPath1020"
                                                    d="M0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="none" fill-opacity="1" stroke="#7859f4" stroke-opacity="1"
                                                    stroke-linecap="butt" stroke-width="2" stroke-dasharray="0"
                                                    class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMaskdlqwjkgl)"
                                                    pathTo="M 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <g id="SvgjsG1013" class="apexcharts-series-markers-wrap">
                                                    <g class="apexcharts-series-markers">
                                                        <circle id="SvgjsCircle1040" r="0" cx="0" cy="0"
                                                            class="apexcharts-marker wt33hga52 no-pointer-events"
                                                            stroke="#ffffff" fill="#7859f4" fill-opacity="1"
                                                            stroke-width="2" stroke-opacity="0.9"
                                                            default-marker-size="0"></circle>
                                                    </g>
                                                </g>
                                            </g>
                                            <g id="SvgjsG1014" class="apexcharts-datalabels"></g>
                                        </g>
                                        <line id="SvgjsLine1035" x1="0" y1="0" x2="90" y2="0" stroke="#b6b6b6"
                                            stroke-dasharray="0" stroke-width="1" class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine1036" x1="0" y1="0" x2="90" y2="0" stroke-dasharray="0"
                                            stroke-width="0" class="apexcharts-ycrosshairs-hidden"></line>
                                        <g id="SvgjsG1037" class="apexcharts-yaxis-annotations"></g>
                                        <g id="SvgjsG1038" class="apexcharts-xaxis-annotations"></g>
                                        <g id="SvgjsG1039" class="apexcharts-point-annotations"></g>
                                    </g>
                                    <rect id="SvgjsRect1006" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                        fill="#fefefe" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0">
                                    </rect>
                                    <g id="SvgjsG1023" class="apexcharts-yaxis" rel="0" transform="translate(-21, 0)">
                                    </g>
                                </svg>
                                <div class="apexcharts-legend"></div>
                                <div class="apexcharts-tooltip apexcharts-theme-dark">
                                    <div class="apexcharts-tooltip-series-group"><span class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(120, 89, 244);"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label"></span><span
                                                    class="apexcharts-tooltip-text-value"></span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-success fw-bold fs-13">
                            <i class="bx bx-up-arrow-alt"></i> 10.21%
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted text-uppercase fs-6">พัสดุ</span>
                        <h2 class="mb-0 mt-1">0</h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-order" style="min-height: 45px;">
                            <div id="apexchartsp2hfcwcw"
                                class="apexcharts-canvas apexchartsp2hfcwcw apexcharts-theme-light"
                                style="width: 90px; height: 45px;"><svg id="SvgjsSvg1041" width="90" height="45"
                                    xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <g id="SvgjsG1043" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(0, 0)">
                                        <defs id="SvgjsDefs1042">
                                            <clipPath id="gridRectMaskp2hfcwcw">
                                                <rect id="SvgjsRect1048" width="96" height="47" x="-3" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMaskp2hfcwcw">
                                                <rect id="SvgjsRect1049" width="92" height="47" x="-1" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <linearGradient id="SvgjsLinearGradient1055" x1="0" y1="0" x2="0" y2="1">
                                                <stop id="SvgjsStop1056" stop-opacity="0.45"
                                                    stop-color="rgba(247,126,83,0.45)" offset="0.45"></stop>
                                                <stop id="SvgjsStop1057" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                <stop id="SvgjsStop1058" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                            </linearGradient>
                                        </defs>
                                        <line id="SvgjsLine1047" x1="0" y1="0" x2="0" y2="45" stroke="#b6b6b6"
                                            stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0" width="1"
                                            height="45" fill="#b1b9c4" filter="none" fill-opacity="0.9"
                                            stroke-width="1"></line>
                                        <g id="SvgjsG1061" class="apexcharts-xaxis" transform="translate(0, 0)">
                                            <g id="SvgjsG1062" class="apexcharts-xaxis-texts-g"
                                                transform="translate(0, 2.75)"></g>
                                        </g>
                                        <g id="SvgjsG1064" class="apexcharts-grid">
                                            <g id="SvgjsG1065" class="apexcharts-gridlines-horizontal"
                                                style="display: none;">
                                                <line id="SvgjsLine1067" x1="0" y1="0" x2="90" y2="0" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1068" x1="0" y1="9" x2="90" y2="9" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1069" x1="0" y1="18" x2="90" y2="18" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1070" x1="0" y1="27" x2="90" y2="27" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1071" x1="0" y1="36" x2="90" y2="36" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1072" x1="0" y1="45" x2="90" y2="45" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                            </g>
                                            <g id="SvgjsG1066" class="apexcharts-gridlines-vertical"
                                                style="display: none;"></g>
                                            <line id="SvgjsLine1074" x1="0" y1="45" x2="90" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                            <line id="SvgjsLine1073" x1="0" y1="1" x2="0" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                        </g>
                                        <g id="SvgjsG1051" class="apexcharts-area-series apexcharts-plot-series">
                                            <g id="SvgjsG1052" class="apexcharts-series" seriesName="seriesx1"
                                                data:longestSeries="true" rel="1" data:realIndex="0">
                                                <path id="SvgjsPath1059"
                                                    d="M0 45L0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 45M90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="url(#SvgjsLinearGradient1055)" fill-opacity="1"
                                                    stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                    stroke-dasharray="0" class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMaskp2hfcwcw)"
                                                    pathTo="M 0 45L 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C 90 20.700000000000003 90 20.700000000000003 90 45M 90 20.700000000000003z"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <path id="SvgjsPath1060"
                                                    d="M0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="none" fill-opacity="1" stroke="#f77e53" stroke-opacity="1"
                                                    stroke-linecap="butt" stroke-width="2" stroke-dasharray="0"
                                                    class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMaskp2hfcwcw)"
                                                    pathTo="M 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <g id="SvgjsG1053" class="apexcharts-series-markers-wrap">
                                                    <g class="apexcharts-series-markers">
                                                        <circle id="SvgjsCircle1080" r="0" cx="0" cy="0"
                                                            class="apexcharts-marker wbxzo5p8t no-pointer-events"
                                                            stroke="#ffffff" fill="#f77e53" fill-opacity="1"
                                                            stroke-width="2" stroke-opacity="0.9"
                                                            default-marker-size="0"></circle>
                                                    </g>
                                                </g>
                                            </g>
                                            <g id="SvgjsG1054" class="apexcharts-datalabels"></g>
                                        </g>
                                        <line id="SvgjsLine1075" x1="0" y1="0" x2="90" y2="0" stroke="#b6b6b6"
                                            stroke-dasharray="0" stroke-width="1" class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine1076" x1="0" y1="0" x2="90" y2="0" stroke-dasharray="0"
                                            stroke-width="0" class="apexcharts-ycrosshairs-hidden"></line>
                                        <g id="SvgjsG1077" class="apexcharts-yaxis-annotations"></g>
                                        <g id="SvgjsG1078" class="apexcharts-xaxis-annotations"></g>
                                        <g id="SvgjsG1079" class="apexcharts-point-annotations"></g>
                                    </g>
                                    <rect id="SvgjsRect1046" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                        fill="#fefefe" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0">
                                    </rect>
                                    <g id="SvgjsG1063" class="apexcharts-yaxis" rel="0" transform="translate(-21, 0)">
                                    </g>
                                </svg>
                                <div class="apexcharts-legend"></div>
                                <div class="apexcharts-tooltip apexcharts-theme-dark">
                                    <div class="apexcharts-tooltip-series-group"><span class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(247, 126, 83);"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label"></span><span
                                                    class="apexcharts-tooltip-text-value"></span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-danger fw-bold fs-13">
                            <i class="bx bx-down-arrow-alt"></i> 1234,443,344.50 บาท
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <?=Html::a('ทรัพย์สินย์',['/am'],['class' => 'text-muted text-uppercase fs-6'])?>
                        <h2 class="mb-0 mt-1" id="totalAsset"></h2>
                    </div>
                    <div class="text-center" style="position: relative;">
                        <div id="t-visitor" style="min-height: 45px;">
                            <div id="apexchartsnzoqi0g3"
                                class="apexcharts-canvas apexchartsnzoqi0g3 apexcharts-theme-light"
                                style="width: 90px; height: 45px;"><svg id="SvgjsSvg1121" width="90" height="45"
                                    xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs"
                                    class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                    style="background: transparent;">
                                    <g id="SvgjsG1123" class="apexcharts-inner apexcharts-graphical"
                                        transform="translate(0, 0)">
                                        <defs id="SvgjsDefs1122">
                                            <clipPath id="gridRectMasknzoqi0g3">
                                                <rect id="SvgjsRect1128" width="96" height="47" x="-3" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <clipPath id="gridRectMarkerMasknzoqi0g3">
                                                <rect id="SvgjsRect1129" width="92" height="47" x="-1" y="-1" rx="0"
                                                    ry="0" fill="#ffffff" opacity="1" stroke-width="0" stroke="none"
                                                    stroke-dasharray="0"></rect>
                                            </clipPath>
                                            <linearGradient id="SvgjsLinearGradient1135" x1="0" y1="0" x2="0" y2="1">
                                                <stop id="SvgjsStop1136" stop-opacity="0.45"
                                                    stop-color="rgba(255,190,11,0.45)" offset="0.45"></stop>
                                                <stop id="SvgjsStop1137" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                                <stop id="SvgjsStop1138" stop-opacity="0.05"
                                                    stop-color="rgba(255,255,255,0.05)" offset="1"></stop>
                                            </linearGradient>
                                        </defs>
                                        <line id="SvgjsLine1127" x1="0" y1="0" x2="0" y2="45" stroke="#b6b6b6"
                                            stroke-dasharray="3" class="apexcharts-xcrosshairs" x="0" y="0" width="1"
                                            height="45" fill="#b1b9c4" filter="none" fill-opacity="0.9"
                                            stroke-width="1"></line>
                                        <g id="SvgjsG1141" class="apexcharts-xaxis" transform="translate(0, 0)">
                                            <g id="SvgjsG1142" class="apexcharts-xaxis-texts-g"
                                                transform="translate(0, 2.75)"></g>
                                        </g>
                                        <g id="SvgjsG1144" class="apexcharts-grid">
                                            <g id="SvgjsG1145" class="apexcharts-gridlines-horizontal"
                                                style="display: none;">
                                                <line id="SvgjsLine1147" x1="0" y1="0" x2="90" y2="0" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1148" x1="0" y1="9" x2="90" y2="9" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1149" x1="0" y1="18" x2="90" y2="18" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1150" x1="0" y1="27" x2="90" y2="27" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1151" x1="0" y1="36" x2="90" y2="36" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                                <line id="SvgjsLine1152" x1="0" y1="45" x2="90" y2="45" stroke="#e0e0e0"
                                                    stroke-dasharray="0" class="apexcharts-gridline"></line>
                                            </g>
                                            <g id="SvgjsG1146" class="apexcharts-gridlines-vertical"
                                                style="display: none;"></g>
                                            <line id="SvgjsLine1154" x1="0" y1="45" x2="90" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                            <line id="SvgjsLine1153" x1="0" y1="1" x2="0" y2="45" stroke="transparent"
                                                stroke-dasharray="0"></line>
                                        </g>
                                        <g id="SvgjsG1131" class="apexcharts-area-series apexcharts-plot-series">
                                            <g id="SvgjsG1132" class="apexcharts-series" seriesName="seriesx1"
                                                data:longestSeries="true" rel="1" data:realIndex="0">
                                                <path id="SvgjsPath1139"
                                                    d="M0 45L0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 45M90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="url(#SvgjsLinearGradient1135)" fill-opacity="1"
                                                    stroke-opacity="1" stroke-linecap="butt" stroke-width="0"
                                                    stroke-dasharray="0" class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMasknzoqi0g3)"
                                                    pathTo="M 0 45L 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C 90 20.700000000000003 90 20.700000000000003 90 45M 90 20.700000000000003z"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <path id="SvgjsPath1140"
                                                    d="M0 33.75C3.1500000000000004 33.75 5.85 15.3 9 15.3C12.15 15.3 14.85 26.55 18 26.55C21.15 26.55 23.85 6.75 27 6.75C30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C39.15 16.650000000000002 41.85 33.75 45 33.75C48.15 33.75 50.85 25.2 54 25.2C57.15 25.2 59.85 39.6 63 39.6C66.15 39.6 68.85 28.8 72 28.8C75.15 28.8 77.85 40.95 81 40.95C84.15 40.95 86.85 20.700000000000003 90 20.700000000000003C90 20.700000000000003 90 20.700000000000003 90 20.700000000000003 "
                                                    fill="none" fill-opacity="1" stroke="#ffbe0b" stroke-opacity="1"
                                                    stroke-linecap="butt" stroke-width="2" stroke-dasharray="0"
                                                    class="apexcharts-area" index="0"
                                                    clip-path="url(#gridRectMasknzoqi0g3)"
                                                    pathTo="M 0 33.75C 3.15 33.75 5.85 15.3 9 15.3C 12.15 15.3 14.85 26.55 18 26.55C 21.15 26.55 23.85 6.75 27 6.75C 30.15 6.75 32.85 16.650000000000002 36 16.650000000000002C 39.15 16.650000000000002 41.85 33.75 45 33.75C 48.15 33.75 50.85 25.2 54 25.2C 57.15 25.2 59.85 39.6 63 39.6C 66.15 39.6 68.85 28.8 72 28.8C 75.15 28.8 77.85 40.95 81 40.95C 84.15 40.95 86.85 20.700000000000003 90 20.700000000000003"
                                                    pathFrom="M -1 45L -1 45L 9 45L 18 45L 27 45L 36 45L 45 45L 54 45L 63 45L 72 45L 81 45L 90 45">
                                                </path>
                                                <g id="SvgjsG1133" class="apexcharts-series-markers-wrap">
                                                    <g class="apexcharts-series-markers">
                                                        <circle id="SvgjsCircle1160" r="0" cx="0" cy="0"
                                                            class="apexcharts-marker wus6kntpu no-pointer-events"
                                                            stroke="#ffffff" fill="#ffbe0b" fill-opacity="1"
                                                            stroke-width="2" stroke-opacity="0.9"
                                                            default-marker-size="0"></circle>
                                                    </g>
                                                </g>
                                            </g>
                                            <g id="SvgjsG1134" class="apexcharts-datalabels"></g>
                                        </g>
                                        <line id="SvgjsLine1155" x1="0" y1="0" x2="90" y2="0" stroke="#b6b6b6"
                                            stroke-dasharray="0" stroke-width="1" class="apexcharts-ycrosshairs"></line>
                                        <line id="SvgjsLine1156" x1="0" y1="0" x2="90" y2="0" stroke-dasharray="0"
                                            stroke-width="0" class="apexcharts-ycrosshairs-hidden"></line>
                                        <g id="SvgjsG1157" class="apexcharts-yaxis-annotations"></g>
                                        <g id="SvgjsG1158" class="apexcharts-xaxis-annotations"></g>
                                        <g id="SvgjsG1159" class="apexcharts-point-annotations"></g>
                                    </g>
                                    <rect id="SvgjsRect1126" width="0" height="0" x="0" y="0" rx="0" ry="0"
                                        fill="#fefefe" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0">
                                    </rect>
                                    <g id="SvgjsG1143" class="apexcharts-yaxis" rel="0" transform="translate(-21, 0)">
                                    </g>
                                </svg>
                                <div class="apexcharts-legend"></div>
                                <div class="apexcharts-tooltip apexcharts-theme-dark">
                                    <div class="apexcharts-tooltip-series-group"><span class="apexcharts-tooltip-marker"
                                            style="background-color: rgb(255, 190, 11);"></span>
                                        <div class="apexcharts-tooltip-text"
                                            style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                            <div class="apexcharts-tooltip-y-group"><span
                                                    class="apexcharts-tooltip-text-label"></span><span
                                                    class="apexcharts-tooltip-text-value"></span></div>
                                            <div class="apexcharts-tooltip-z-group"><span
                                                    class="apexcharts-tooltip-text-z-label"></span><span
                                                    class="apexcharts-tooltip-text-z-value"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="text-danger fw-bold fs-13">
                            <i class="bx bx-down-arrow-alt"></i> 234,443,344.50
                        </span>
                        <div class="resize-triggers">
                            <div class="expand-trigger">
                                <div style="width: 91px; height: 70px;"></div>
                            </div>
                            <div class="contract-trigger"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=$this->render('./row2')?>
<?=$this->render('./row3')?>
<?=$this->render('./row5')?>
<?php
use yii\web\View;

$urlSummary = Url::to(['/summary']);
$js = <<< JS
loadSummary()

async function loadSummary(){
    await $.ajax({
        type: "get",
        url: "$urlSummary",
        dataType: "json",
        success: function (res) {
            console.log(res);
            $('#totalEmployees').text(res.totalEmployee.total);
            $('#totalAsset').text(res.totalAssetPrice.total);
        }
    });
}
JS;
$this->registerJS($js,View::POS_END);
?>