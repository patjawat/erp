<?php
use yii\bootstrap5\Html;
?>

<div class="row">
    <div class="col-xl-4">
        <div class="overflow-hidden card mb-4 border-0">
            <div class="bg-primary-subtle">
                <div class="row">
                    <div class="col-7">
                        <div class="text-primary p-3">
                            <h5 class="text-primary">Welcome Back !</h5>
                            <p>Skote Dashboard</p>
                        </div>
                    </div>
                    <div class="align-self-end col-5">
                        
                        <img src="https://skote-v-light.react.themesbrand.com/static/media/profile-img.43b59e598ba15abe6eab.png"
                            alt="" class="img-fluid"></div>
                </div>
            </div>
            <div class="pt-0 card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="avatar-md profile-user-wid mb-4">
                        <?=Html::img('@web/avatar/patjwat2.png',['class' => 'object-fit-cover img-thumbnail rounded-circle'])?>    
                        <!-- <img
                                src="https://skote-v-light.react.themesbrand.com/static/media/avatar-1.3921191a8acf79d3e907.jpg" alt=""
                                class="img-thumbnail rounded-circle"> -->
                            
                            </div>
                        <h5 class="font-size-15 text-truncate">Henry Price</h5>
                        <p class="text-muted mb-0 text-truncate">UI/UX Designer</p>
                    </div>
                    <div class="col-sm-8">
                        <div class="pt-4">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="font-size-15">125</h5>
                                    <p class="text-muted mb-0">Projects</p>
                                </div>
                                <div class="col-6">
                                    <h5 class="font-size-15">$1245</h5>
                                    <p class="text-muted mb-0">Revenue</p>
                                </div>
                            </div>
                            <div class="mt-4"><a class="btn btn-primary  btn-sm" href="/dashboard">View Profile <i
                                        class="mdi mdi-arrow-right ms-1"></i></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="mb-4 card-title">Monthly Earning</div>
                <div class="row">
                    <div class="col-sm-6">
                        <p class="text-muted">This month</p>
                        <h3>$34,252</h3>
                        <p class="text-muted"><span class="text-success me-2"> 12% <i class="mdi mdi-arrow-up"></i>
                            </span> From previous period</p>
                        <div class="mt-4"><a class="btn btn-primary waves-effect waves-light btn-sm"
                                href="/dashboard">View More <i class="mdi mdi-arrow-right ms-1"></i></a></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="mt-4 mt-sm-0">
                            <div options="[object Object]" series="67" type="radialBar" height="200" class="apex-charts"
                                width="100%" style="min-height: 168.525px;">
                                <div id="apexchartsqfoi78g7"
                                    class="apexcharts-canvas apexchartsqfoi78g7 apexcharts-theme-light"
                                    style="width: 150px; height: 168.525px;"><svg id="SvgjsSvg1422" width="150"
                                        height="168.52499999999998" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev"
                                        class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                        style="background: transparent;">
                                        <foreignObject x="0" y="0" width="150" height="168.52499999999998">
                                            <div class="apexcharts-legend" xmlns="http://www.w3.org/1999/xhtml"></div>
                                        </foreignObject>
                                        <g id="SvgjsG1424" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(-12, 0)">
                                            <defs id="SvgjsDefs1423">
                                                <clipPath id="gridRectMaskqfoi78g7">
                                                    <rect id="SvgjsRect1425" width="182" height="200" x="-3" y="-1"
                                                        rx="0" ry="0" opacity="1" stroke-width="0" stroke="none"
                                                        stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                                <clipPath id="forecastMaskqfoi78g7"></clipPath>
                                                <clipPath id="nonForecastMaskqfoi78g7"></clipPath>
                                                <clipPath id="gridRectMarkerMaskqfoi78g7">
                                                    <rect id="SvgjsRect1426" width="180" height="202" x="-2" y="-2"
                                                        rx="0" ry="0" opacity="1" stroke-width="0" stroke="none"
                                                        stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                                <linearGradient id="SvgjsLinearGradient1431" x1="1" y1="0" x2="0"
                                                    y2="1">
                                                    <stop id="SvgjsStop1432" stop-opacity="1"
                                                        stop-color="rgba(242,242,242,1)" offset="0"></stop>
                                                    <stop id="SvgjsStop1433" stop-opacity="1"
                                                        stop-color="rgba(206,206,206,1)" offset="0.5"></stop>
                                                    <stop id="SvgjsStop1434" stop-opacity="1"
                                                        stop-color="rgba(206,206,206,1)" offset="0.65"></stop>
                                                    <stop id="SvgjsStop1435" stop-opacity="1"
                                                        stop-color="rgba(242,242,242,1)" offset="0.91"></stop>
                                                </linearGradient>
                                                <linearGradient id="SvgjsLinearGradient1443" x1="1" y1="0" x2="0"
                                                    y2="1">
                                                    <stop id="SvgjsStop1444" stop-opacity="1"
                                                        stop-color="rgba(85,110,230,1)" offset="0"></stop>
                                                    <stop id="SvgjsStop1445" stop-opacity="1"
                                                        stop-color="rgba(72,94,196,1)" offset="0.5"></stop>
                                                    <stop id="SvgjsStop1446" stop-opacity="1"
                                                        stop-color="rgba(72,94,196,1)" offset="0.65"></stop>
                                                    <stop id="SvgjsStop1447" stop-opacity="1"
                                                        stop-color="rgba(85,110,230,1)" offset="0.91"></stop>
                                                </linearGradient>
                                            </defs>
                                            <g id="SvgjsG1427" class="apexcharts-radialbar">
                                                <g id="SvgjsG1428">
                                                    <g id="SvgjsG1429" class="apexcharts-tracks">
                                                        <g id="SvgjsG1430"
                                                            class="apexcharts-radialbar-track apexcharts-track" rel="1">
                                                            <path id="apexcharts-radialbarTrack-0"
                                                                d="M 50.94156838842453 125.05843161157546 A 52.40853658536585 52.40853658536585 0 1 1 125.05843161157546 125.05843161157546"
                                                                fill="none" fill-opacity="1"
                                                                stroke="rgba(242,242,242,0.85)" stroke-opacity="1"
                                                                stroke-linecap="butt" stroke-width="14.514512195121952"
                                                                stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                                data:pathOrig="M 50.94156838842453 125.05843161157546 A 52.40853658536585 52.40853658536585 0 1 1 125.05843161157546 125.05843161157546">
                                                            </path>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG1437">
                                                        <g id="SvgjsG1442"
                                                            class="apexcharts-series apexcharts-radial-series"
                                                            seriesName="SeriesxA" rel="1" data:realIndex="0">
                                                            <path id="SvgjsPath1448"
                                                                d="M 50.94156838842453 125.05843161157546 A 52.40853658536585 52.40853658536585 0 1 1 125.69954624335796 51.59397137746902"
                                                                fill="none" fill-opacity="0.85"
                                                                stroke="url(#SvgjsLinearGradient1443)"
                                                                stroke-opacity="1" stroke-linecap="butt"
                                                                stroke-width="14.963414634146343" stroke-dasharray="4"
                                                                class="apexcharts-radialbar-area apexcharts-radialbar-slice-0"
                                                                data:angle="181" data:value="67" index="0" j="0"
                                                                data:pathOrig="M 50.94156838842453 125.05843161157546 A 52.40853658536585 52.40853658536585 0 1 1 125.69954624335796 51.59397137746902">
                                                            </path>
                                                        </g>
                                                        <circle id="SvgjsCircle1438" r="40.151280487804875" cx="88"
                                                            cy="88" class="apexcharts-radialbar-hollow"
                                                            fill="transparent"></circle>
                                                        <g id="SvgjsG1439" class="apexcharts-datalabels-group"
                                                            transform="translate(0, 0) scale(1)" style="opacity: 1;">
                                                            <text id="SvgjsText1440"
                                                                font-family="Helvetica, Arial, sans-serif" x="88"
                                                                y="148" text-anchor="middle" dominant-baseline="auto"
                                                                font-size="13px" font-weight="600" fill="#556ee6"
                                                                class="apexcharts-text apexcharts-datalabel-label"
                                                                style="font-family: Helvetica, Arial, sans-serif;">Series
                                                                A</text><text id="SvgjsText1441"
                                                                font-family="Helvetica, Arial, sans-serif" x="88"
                                                                y="126" text-anchor="middle" dominant-baseline="auto"
                                                                font-size="16px" font-weight="400" fill="#373d3f"
                                                                class="apexcharts-text apexcharts-datalabel-value"
                                                                style="font-family: Helvetica, Arial, sans-serif;">67%</text>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                            <line id="SvgjsLine1449" x1="0" y1="0" x2="176" y2="0" stroke="#b6b6b6"
                                                stroke-dasharray="0" stroke-width="1" stroke-linecap="butt"
                                                class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine1450" x1="0" y1="0" x2="176" y2="0" stroke-dasharray="0"
                                                stroke-width="0" stroke-linecap="butt"
                                                class="apexcharts-ycrosshairs-hidden"></line>
                                        </g>
                                    </svg></div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-muted mb-0">We craft digital, graphic and dimensional thinking.</p>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="row">
            <div class="col-md-4">
                <div class="mini-stats-wid card mb-4">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Orders</p>
                                <h4 class="mb-0">1,235</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon"><span
                                    class="avatar-title rounded-circle bg-primary"><i
                                        class="bx bx-copy-alt font-size-24"></i></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stats-wid card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Revenue</p>
                                <h4 class="mb-0">$35, 723</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon"><span
                                    class="avatar-title rounded-circle bg-primary"><i
                                        class="bx bx-archive-in font-size-24"></i></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stats-wid card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Average Price</p>
                                <h4 class="mb-0">$16.2</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon"><span
                                    class="avatar-title rounded-circle bg-primary"><i
                                        class="bx bx-purchase-tag-alt font-size-24"></i></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex flex-wrap">
                    <h4 class="card-title mb-4">Email Sent</h4>
                    <div class="ms-auto">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link" id="one_month" href="/dashboard">Week</a> </li>
                            <li class="nav-item"><a class="nav-link" id="one_month" href="/dashboard">Month</a></li>
                            <li class="nav-item"><a class="active nav-link" id="one_month" href="/dashboard">Year</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div options="[object Object]" series="[object Object],[object Object],[object Object]" type="bar"
                    height="359" class="apex-charts" width="100%" style="min-height: 374px;">
                    <div id="apexcharts73xi2lex" class="apexcharts-canvas apexcharts73xi2lex apexcharts-theme-light"
                        style="width: 713px; height: 359px;"><svg id="SvgjsSvg1451" width="713" height="359"
                            xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                            xmlns:svgjs="http://svgjs.dev" class="apexcharts-svg" xmlns:data="ApexChartsNS"
                            transform="translate(0, 0)" style="background: transparent;">
                            <foreignObject x="0" y="0" width="713" height="359">
                                <div class="apexcharts-legend apexcharts-align-center apx-legend-position-bottom"
                                    xmlns="http://www.w3.org/1999/xhtml"
                                    style="inset: auto 0px 1px; position: absolute; max-height: 179.5px;">
                                    <div class="apexcharts-legend-series" rel="1" seriesname="SeriesxA"
                                        data:collapsed="false" style="margin: 2px 5px;"><span
                                            class="apexcharts-legend-marker" rel="1" data:collapsed="false"
                                            style="background: rgb(85, 110, 230) !important; color: rgb(85, 110, 230); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 2px;"></span><span
                                            class="apexcharts-legend-text" rel="1" i="0" data:default-text="Series%20A"
                                            data:collapsed="false"
                                            style="color: rgb(55, 61, 63); font-size: 12px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                            A</span></div>
                                    <div class="apexcharts-legend-series" rel="2" seriesname="SeriesxB"
                                        data:collapsed="false" style="margin: 2px 5px;"><span
                                            class="apexcharts-legend-marker" rel="2" data:collapsed="false"
                                            style="background: rgb(241, 180, 76) !important; color: rgb(241, 180, 76); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 2px;"></span><span
                                            class="apexcharts-legend-text" rel="2" i="1" data:default-text="Series%20B"
                                            data:collapsed="false"
                                            style="color: rgb(55, 61, 63); font-size: 12px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                            B</span></div>
                                    <div class="apexcharts-legend-series" rel="3" seriesname="SeriesxC"
                                        data:collapsed="false" style="margin: 2px 5px;"><span
                                            class="apexcharts-legend-marker" rel="3" data:collapsed="false"
                                            style="background: rgb(52, 195, 143) !important; color: rgb(52, 195, 143); height: 12px; width: 12px; left: 0px; top: 0px; border-width: 0px; border-color: rgb(255, 255, 255); border-radius: 2px;"></span><span
                                            class="apexcharts-legend-text" rel="3" i="2" data:default-text="Series%20C"
                                            data:collapsed="false"
                                            style="color: rgb(55, 61, 63); font-size: 12px; font-weight: 400; font-family: Helvetica, Arial, sans-serif;">Series
                                            C</span></div>
                                </div>
                                <style type="text/css">
                                .apexcharts-legend {
                                    display: flex;
                                    overflow: auto;
                                    padding: 0 10px;
                                }

                                .apexcharts-legend.apx-legend-position-bottom,
                                .apexcharts-legend.apx-legend-position-top {
                                    flex-wrap: wrap
                                }

                                .apexcharts-legend.apx-legend-position-right,
                                .apexcharts-legend.apx-legend-position-left {
                                    flex-direction: column;
                                    bottom: 0;
                                }

                                .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left,
                                .apexcharts-legend.apx-legend-position-top.apexcharts-align-left,
                                .apexcharts-legend.apx-legend-position-right,
                                .apexcharts-legend.apx-legend-position-left {
                                    justify-content: flex-start;
                                }

                                .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center,
                                .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
                                    justify-content: center;
                                }

                                .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right,
                                .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
                                    justify-content: flex-end;
                                }

                                .apexcharts-legend-series {
                                    cursor: pointer;
                                    line-height: normal;
                                }

                                .apexcharts-legend.apx-legend-position-bottom .apexcharts-legend-series,
                                .apexcharts-legend.apx-legend-position-top .apexcharts-legend-series {
                                    display: flex;
                                    align-items: center;
                                }

                                .apexcharts-legend-text {
                                    position: relative;
                                    font-size: 14px;
                                }

                                .apexcharts-legend-text *,
                                .apexcharts-legend-marker * {
                                    pointer-events: none;
                                }

                                .apexcharts-legend-marker {
                                    position: relative;
                                    display: inline-block;
                                    cursor: pointer;
                                    margin-right: 3px;
                                    border-style: solid;
                                }

                                .apexcharts-legend.apexcharts-align-right .apexcharts-legend-series,
                                .apexcharts-legend.apexcharts-align-left .apexcharts-legend-series {
                                    display: inline-block;
                                }

                                .apexcharts-legend-series.apexcharts-no-click {
                                    cursor: auto;
                                }

                                .apexcharts-legend .apexcharts-hidden-zero-series,
                                .apexcharts-legend .apexcharts-hidden-null-series {
                                    display: none !important;
                                }

                                .apexcharts-inactive-legend {
                                    opacity: 0.45;
                                }
                                </style>
                            </foreignObject>
                            <g id="SvgjsG1611" class="apexcharts-yaxis" rel="0" transform="translate(14.34375, 0)">
                                <g id="SvgjsG1612" class="apexcharts-yaxis-texts-g"><text id="SvgjsText1614"
                                        font-family="Helvetica, Arial, sans-serif" x="20" y="31.5" text-anchor="end"
                                        dominant-baseline="auto" font-size="11px" font-weight="400" fill="#373d3f"
                                        class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1615">100</tspan>
                                        <title>100</title>
                                    </text><text id="SvgjsText1617" font-family="Helvetica, Arial, sans-serif" x="20"
                                        y="84.1606" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                        font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1618">80</tspan>
                                        <title>80</title>
                                    </text><text id="SvgjsText1620" font-family="Helvetica, Arial, sans-serif" x="20"
                                        y="136.8212" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                        font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1621">60</tspan>
                                        <title>60</title>
                                    </text><text id="SvgjsText1623" font-family="Helvetica, Arial, sans-serif" x="20"
                                        y="189.48180000000002" text-anchor="end" dominant-baseline="auto"
                                        font-size="11px" font-weight="400" fill="#373d3f"
                                        class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1624">40</tspan>
                                        <title>40</title>
                                    </text><text id="SvgjsText1626" font-family="Helvetica, Arial, sans-serif" x="20"
                                        y="242.1424" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                        font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1627">20</tspan>
                                        <title>20</title>
                                    </text><text id="SvgjsText1629" font-family="Helvetica, Arial, sans-serif" x="20"
                                        y="294.803" text-anchor="end" dominant-baseline="auto" font-size="11px"
                                        font-weight="400" fill="#373d3f" class="apexcharts-text apexcharts-yaxis-label "
                                        style="font-family: Helvetica, Arial, sans-serif;">
                                        <tspan id="SvgjsTspan1630">0</tspan>
                                        <title>0</title>
                                    </text></g>
                            </g>
                            <g id="SvgjsG1453" class="apexcharts-inner apexcharts-graphical"
                                transform="translate(44.34375, 30)">
                                <defs id="SvgjsDefs1452">
                                    <linearGradient id="SvgjsLinearGradient1456" x1="0" y1="0" x2="0" y2="1">
                                        <stop id="SvgjsStop1457" stop-opacity="0.4" stop-color="rgba(216,227,240,0.4)"
                                            offset="0"></stop>
                                        <stop id="SvgjsStop1458" stop-opacity="0.5" stop-color="rgba(190,209,230,0.5)"
                                            offset="1"></stop>
                                        <stop id="SvgjsStop1459" stop-opacity="0.5" stop-color="rgba(190,209,230,0.5)"
                                            offset="1"></stop>
                                    </linearGradient>
                                    <clipPath id="gridRectMask73xi2lex">
                                        <rect id="SvgjsRect1461" width="662.65625" height="263.303" x="-2" y="0" rx="0"
                                            ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0"
                                            fill="#fff"></rect>
                                    </clipPath>
                                    <clipPath id="forecastMask73xi2lex"></clipPath>
                                    <clipPath id="nonForecastMask73xi2lex"></clipPath>
                                    <clipPath id="gridRectMarkerMask73xi2lex">
                                        <rect id="SvgjsRect1462" width="662.65625" height="267.303" x="-2" y="-2" rx="0"
                                            ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0"
                                            fill="#fff"></rect>
                                    </clipPath>
                                </defs>
                                <rect id="SvgjsRect1460" width="8.233203125" height="263.303" x="0" y="0" rx="0" ry="0"
                                    opacity="1" stroke-width="0" stroke-dasharray="3"
                                    fill="url(#SvgjsLinearGradient1456)" class="apexcharts-xcrosshairs" y2="263.303"
                                    filter="none" fill-opacity="0.9"></rect>
                                <line id="SvgjsLine1549" x1="0" y1="264.303" x2="0" y2="270.303" stroke="#e0e0e0"
                                    stroke-dasharray="0" stroke-linecap="butt" class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1550" x1="54.888020833333336" y1="264.303" x2="54.888020833333336"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1551" x1="109.77604166666667" y1="264.303" x2="109.77604166666667"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1552" x1="164.6640625" y1="264.303" x2="164.6640625" y2="270.303"
                                    stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1553" x1="219.55208333333334" y1="264.303" x2="219.55208333333334"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1554" x1="274.4401041666667" y1="264.303" x2="274.4401041666667"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1555" x1="329.328125" y1="264.303" x2="329.328125" y2="270.303"
                                    stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1556" x1="384.2161458333333" y1="264.303" x2="384.2161458333333"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1557" x1="439.10416666666663" y1="264.303" x2="439.10416666666663"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1558" x1="493.99218749999994" y1="264.303" x2="493.99218749999994"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1559" x1="548.8802083333333" y1="264.303" x2="548.8802083333333"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1560" x1="603.7682291666666" y1="264.303" x2="603.7682291666666"
                                    y2="270.303" stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <line id="SvgjsLine1561" x1="658.65625" y1="264.303" x2="658.65625" y2="270.303"
                                    stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                    class="apexcharts-xaxis-tick"></line>
                                <g id="SvgjsG1545" class="apexcharts-grid">
                                    <g id="SvgjsG1546" class="apexcharts-gridlines-horizontal">
                                        <line id="SvgjsLine1563" x1="0" y1="52.6606" x2="658.65625" y2="52.6606"
                                            stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                            class="apexcharts-gridline"></line>
                                        <line id="SvgjsLine1564" x1="0" y1="105.3212" x2="658.65625" y2="105.3212"
                                            stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                            class="apexcharts-gridline"></line>
                                        <line id="SvgjsLine1565" x1="0" y1="157.98180000000002" x2="658.65625"
                                            y2="157.98180000000002" stroke="#e0e0e0" stroke-dasharray="0"
                                            stroke-linecap="butt" class="apexcharts-gridline"></line>
                                        <line id="SvgjsLine1566" x1="0" y1="210.6424" x2="658.65625" y2="210.6424"
                                            stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                            class="apexcharts-gridline"></line>
                                    </g>
                                    <g id="SvgjsG1547" class="apexcharts-gridlines-vertical"></g>
                                    <line id="SvgjsLine1569" x1="0" y1="263.303" x2="658.65625" y2="263.303"
                                        stroke="transparent" stroke-dasharray="0" stroke-linecap="butt"></line>
                                    <line id="SvgjsLine1568" x1="0" y1="1" x2="0" y2="263.303" stroke="transparent"
                                        stroke-dasharray="0" stroke-linecap="butt"></line>
                                </g>
                                <g id="SvgjsG1548" class="apexcharts-grid-borders">
                                    <line id="SvgjsLine1562" x1="0" y1="0" x2="658.65625" y2="0" stroke="#e0e0e0"
                                        stroke-dasharray="0" stroke-linecap="butt" class="apexcharts-gridline"></line>
                                    <line id="SvgjsLine1567" x1="0" y1="263.303" x2="658.65625" y2="263.303"
                                        stroke="#e0e0e0" stroke-dasharray="0" stroke-linecap="butt"
                                        class="apexcharts-gridline"></line>
                                    <line id="SvgjsLine1610" x1="0" y1="264.303" x2="658.65625" y2="264.303"
                                        stroke="#e0e0e0" stroke-dasharray="0" stroke-width="1" stroke-linecap="butt">
                                    </line>
                                </g>
                                <g id="SvgjsG1463" class="apexcharts-bar-series apexcharts-plot-series">
                                    <g id="SvgjsG1464" class="apexcharts-series" seriesName="SeriesxA" rel="1"
                                        data:realIndex="0">
                                        <path id="SvgjsPath1468"
                                            d="M 23.32740885416667 263.304 L 23.32740885416667 147.45068 L 31.56061197916667 147.45068 L 31.56061197916667 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 23.32740885416667 263.304 L 23.32740885416667 147.45068 L 31.56061197916667 147.45068 L 31.56061197916667 263.304 z"
                                            pathFrom="M 23.32740885416667 263.304 L 23.32740885416667 263.304 L 31.56061197916667 263.304 L 31.56061197916667 263.304 L 31.56061197916667 263.304 L 31.56061197916667 263.304 L 31.56061197916667 263.304 L 23.32740885416667 263.304 z"
                                            cy="147.44968" cx="78.2154296875" j="0" val="44" barHeight="115.85332"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1470"
                                            d="M 78.2154296875 263.304 L 78.2154296875 118.48735000000002 L 86.44863281250001 118.48735000000002 L 86.44863281250001 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 78.2154296875 263.304 L 78.2154296875 118.48735000000002 L 86.44863281250001 118.48735000000002 L 86.44863281250001 263.304 z"
                                            pathFrom="M 78.2154296875 263.304 L 78.2154296875 263.304 L 86.44863281250001 263.304 L 86.44863281250001 263.304 L 86.44863281250001 263.304 L 86.44863281250001 263.304 L 86.44863281250001 263.304 L 78.2154296875 263.304 z"
                                            cy="118.48635000000002" cx="133.10345052083335" j="1" val="55"
                                            barHeight="144.81664999999998" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1472"
                                            d="M 133.10345052083335 263.304 L 133.10345052083335 155.34977 L 141.33665364583334 155.34977 L 141.33665364583334 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 133.10345052083335 263.304 L 133.10345052083335 155.34977 L 141.33665364583334 155.34977 L 141.33665364583334 263.304 z"
                                            pathFrom="M 133.10345052083335 263.304 L 133.10345052083335 263.304 L 141.33665364583334 263.304 L 141.33665364583334 263.304 L 141.33665364583334 263.304 L 141.33665364583334 263.304 L 141.33665364583334 263.304 L 133.10345052083335 263.304 z"
                                            cy="155.34877" cx="187.9914713541667" j="2" val="41" barHeight="107.95423"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1474"
                                            d="M 187.9914713541667 263.304 L 187.9914713541667 86.89099000000002 L 196.22467447916668 86.89099000000002 L 196.22467447916668 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 187.9914713541667 263.304 L 187.9914713541667 86.89099000000002 L 196.22467447916668 86.89099000000002 L 196.22467447916668 263.304 z"
                                            pathFrom="M 187.9914713541667 263.304 L 187.9914713541667 263.304 L 196.22467447916668 263.304 L 196.22467447916668 263.304 L 196.22467447916668 263.304 L 196.22467447916668 263.304 L 196.22467447916668 263.304 L 187.9914713541667 263.304 z"
                                            cy="86.88999000000001" cx="242.87949218750003" j="3" val="67"
                                            barHeight="176.41300999999999" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1476"
                                            d="M 242.87949218750003 263.304 L 242.87949218750003 205.37734 L 251.11269531250002 205.37734 L 251.11269531250002 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 242.87949218750003 263.304 L 242.87949218750003 205.37734 L 251.11269531250002 205.37734 L 251.11269531250002 263.304 z"
                                            pathFrom="M 242.87949218750003 263.304 L 242.87949218750003 263.304 L 251.11269531250002 263.304 L 251.11269531250002 263.304 L 251.11269531250002 263.304 L 251.11269531250002 263.304 L 251.11269531250002 263.304 L 242.87949218750003 263.304 z"
                                            cy="205.37634" cx="297.76751302083335" j="4" val="22" barHeight="57.92666"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1478"
                                            d="M 297.76751302083335 263.304 L 297.76751302083335 150.08371000000002 L 306.00071614583334 150.08371000000002 L 306.00071614583334 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 297.76751302083335 263.304 L 297.76751302083335 150.08371000000002 L 306.00071614583334 150.08371000000002 L 306.00071614583334 263.304 z"
                                            pathFrom="M 297.76751302083335 263.304 L 297.76751302083335 263.304 L 306.00071614583334 263.304 L 306.00071614583334 263.304 L 306.00071614583334 263.304 L 306.00071614583334 263.304 L 306.00071614583334 263.304 L 297.76751302083335 263.304 z"
                                            cy="150.08271000000002" cx="352.65553385416666" j="5" val="43"
                                            barHeight="113.22028999999999" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1480"
                                            d="M 352.65553385416666 263.304 L 352.65553385416666 168.51492 L 360.88873697916665 168.51492 L 360.88873697916665 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 352.65553385416666 263.304 L 352.65553385416666 168.51492 L 360.88873697916665 168.51492 L 360.88873697916665 263.304 z"
                                            pathFrom="M 352.65553385416666 263.304 L 352.65553385416666 263.304 L 360.88873697916665 263.304 L 360.88873697916665 263.304 L 360.88873697916665 263.304 L 360.88873697916665 263.304 L 360.88873697916665 263.304 L 352.65553385416666 263.304 z"
                                            cy="168.51391999999998" cx="407.5435546875" j="6" val="36"
                                            barHeight="94.78908" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1482"
                                            d="M 407.5435546875 263.304 L 407.5435546875 126.38644 L 415.77675781249997 126.38644 L 415.77675781249997 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 407.5435546875 263.304 L 407.5435546875 126.38644 L 415.77675781249997 126.38644 L 415.77675781249997 263.304 z"
                                            pathFrom="M 407.5435546875 263.304 L 407.5435546875 263.304 L 415.77675781249997 263.304 L 415.77675781249997 263.304 L 415.77675781249997 263.304 L 415.77675781249997 263.304 L 415.77675781249997 263.304 L 407.5435546875 263.304 z"
                                            cy="126.38543999999999" cx="462.4315755208333" j="7" val="52"
                                            barHeight="136.91756" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1484"
                                            d="M 462.4315755208333 263.304 L 462.4315755208333 200.11128 L 470.6647786458333 200.11128 L 470.6647786458333 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 462.4315755208333 263.304 L 462.4315755208333 200.11128 L 470.6647786458333 200.11128 L 470.6647786458333 263.304 z"
                                            pathFrom="M 462.4315755208333 263.304 L 462.4315755208333 263.304 L 470.6647786458333 263.304 L 470.6647786458333 263.304 L 470.6647786458333 263.304 L 470.6647786458333 263.304 L 470.6647786458333 263.304 L 462.4315755208333 263.304 z"
                                            cy="200.11028" cx="517.3195963541666" j="8" val="24"
                                            barHeight="63.192719999999994" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1486"
                                            d="M 517.3195963541666 263.304 L 517.3195963541666 215.90946 L 525.5527994791667 215.90946 L 525.5527994791667 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 517.3195963541666 263.304 L 517.3195963541666 215.90946 L 525.5527994791667 215.90946 L 525.5527994791667 263.304 z"
                                            pathFrom="M 517.3195963541666 263.304 L 517.3195963541666 263.304 L 525.5527994791667 263.304 L 525.5527994791667 263.304 L 525.5527994791667 263.304 L 525.5527994791667 263.304 L 525.5527994791667 263.304 L 517.3195963541666 263.304 z"
                                            cy="215.90846" cx="572.2076171875" j="9" val="18" barHeight="47.39454"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1488"
                                            d="M 572.2076171875 263.304 L 572.2076171875 168.51492 L 580.4408203125 168.51492 L 580.4408203125 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 572.2076171875 263.304 L 572.2076171875 168.51492 L 580.4408203125 168.51492 L 580.4408203125 263.304 z"
                                            pathFrom="M 572.2076171875 263.304 L 572.2076171875 263.304 L 580.4408203125 263.304 L 580.4408203125 263.304 L 580.4408203125 263.304 L 580.4408203125 263.304 L 580.4408203125 263.304 L 572.2076171875 263.304 z"
                                            cy="168.51391999999998" cx="627.0956380208333" j="10" val="36"
                                            barHeight="94.78908" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1490"
                                            d="M 627.0956380208333 263.304 L 627.0956380208333 136.91856 L 635.3288411458334 136.91856 L 635.3288411458334 263.304 z"
                                            fill="rgba(85,110,230,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="0" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 627.0956380208333 263.304 L 627.0956380208333 136.91856 L 635.3288411458334 136.91856 L 635.3288411458334 263.304 z"
                                            pathFrom="M 627.0956380208333 263.304 L 627.0956380208333 263.304 L 635.3288411458334 263.304 L 635.3288411458334 263.304 L 635.3288411458334 263.304 L 635.3288411458334 263.304 L 635.3288411458334 263.304 L 627.0956380208333 263.304 z"
                                            cy="136.91756" cx="681.9836588541667" j="11" val="48"
                                            barHeight="126.38543999999999" barWidth="8.233203125"></path>
                                        <g id="SvgjsG1466" class="apexcharts-bar-goals-markers">
                                            <g id="SvgjsG1467" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1469" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1471" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1473" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1475" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1477" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1479" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1481" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1483" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1485" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1487" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1489" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                        </g>
                                    </g>
                                    <g id="SvgjsG1491" class="apexcharts-series" seriesName="SeriesxB" rel="2"
                                        data:realIndex="1">
                                        <path id="SvgjsPath1495"
                                            d="M 23.32740885416667 147.45168 L 23.32740885416667 113.22229000000002 L 31.56061197916667 113.22229000000002 L 31.56061197916667 147.45168 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 23.32740885416667 147.45168 L 23.32740885416667 113.22229000000002 L 31.56061197916667 113.22229000000002 L 31.56061197916667 147.45168 z"
                                            pathFrom="M 23.32740885416667 147.45168 L 23.32740885416667 147.45168 L 31.56061197916667 147.45168 L 31.56061197916667 147.45168 L 31.56061197916667 147.45168 L 31.56061197916667 147.45168 L 31.56061197916667 147.45168 L 23.32740885416667 147.45168 z"
                                            cy="113.22129000000001" cx="78.2154296875" j="0" val="13"
                                            barHeight="34.22939" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1497"
                                            d="M 78.2154296875 118.48835000000003 L 78.2154296875 57.92866000000002 L 86.44863281250001 57.92866000000002 L 86.44863281250001 118.48835000000003 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 78.2154296875 118.48835000000003 L 78.2154296875 57.92866000000002 L 86.44863281250001 57.92866000000002 L 86.44863281250001 118.48835000000003 z"
                                            pathFrom="M 78.2154296875 118.48835000000003 L 78.2154296875 118.48835000000003 L 86.44863281250001 118.48835000000003 L 86.44863281250001 118.48835000000003 L 86.44863281250001 118.48835000000003 L 86.44863281250001 118.48835000000003 L 86.44863281250001 118.48835000000003 L 78.2154296875 118.48835000000003 z"
                                            cy="57.927660000000024" cx="133.10345052083335" j="1" val="23"
                                            barHeight="60.559689999999996" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1499"
                                            d="M 133.10345052083335 155.35077 L 133.10345052083335 102.69017000000002 L 141.33665364583334 102.69017000000002 L 141.33665364583334 155.35077 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 133.10345052083335 155.35077 L 133.10345052083335 102.69017000000002 L 141.33665364583334 102.69017000000002 L 141.33665364583334 155.35077 z"
                                            pathFrom="M 133.10345052083335 155.35077 L 133.10345052083335 155.35077 L 141.33665364583334 155.35077 L 141.33665364583334 155.35077 L 141.33665364583334 155.35077 L 141.33665364583334 155.35077 L 141.33665364583334 155.35077 L 133.10345052083335 155.35077 z"
                                            cy="102.68917000000002" cx="187.9914713541667" j="2" val="20"
                                            barHeight="52.660599999999995" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1501"
                                            d="M 187.9914713541667 86.89199000000002 L 187.9914713541667 65.82775000000002 L 196.22467447916668 65.82775000000002 L 196.22467447916668 86.89199000000002 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 187.9914713541667 86.89199000000002 L 187.9914713541667 65.82775000000002 L 196.22467447916668 65.82775000000002 L 196.22467447916668 86.89199000000002 z"
                                            pathFrom="M 187.9914713541667 86.89199000000002 L 187.9914713541667 86.89199000000002 L 196.22467447916668 86.89199000000002 L 196.22467447916668 86.89199000000002 L 196.22467447916668 86.89199000000002 L 196.22467447916668 86.89199000000002 L 196.22467447916668 86.89199000000002 L 187.9914713541667 86.89199000000002 z"
                                            cy="65.82675000000002" cx="242.87949218750003" j="3" val="8"
                                            barHeight="21.064239999999998" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1503"
                                            d="M 242.87949218750003 205.37834 L 242.87949218750003 171.14895 L 251.11269531250002 171.14895 L 251.11269531250002 205.37834 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 242.87949218750003 205.37834 L 242.87949218750003 171.14895 L 251.11269531250002 171.14895 L 251.11269531250002 205.37834 z"
                                            pathFrom="M 242.87949218750003 205.37834 L 242.87949218750003 205.37834 L 251.11269531250002 205.37834 L 251.11269531250002 205.37834 L 251.11269531250002 205.37834 L 251.11269531250002 205.37834 L 251.11269531250002 205.37834 L 242.87949218750003 205.37834 z"
                                            cy="171.14795" cx="297.76751302083335" j="4" val="13" barHeight="34.22939"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1505"
                                            d="M 297.76751302083335 150.08471000000003 L 297.76751302083335 78.99290000000003 L 306.00071614583334 78.99290000000003 L 306.00071614583334 150.08471000000003 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 297.76751302083335 150.08471000000003 L 297.76751302083335 78.99290000000003 L 306.00071614583334 78.99290000000003 L 306.00071614583334 150.08471000000003 z"
                                            pathFrom="M 297.76751302083335 150.08471000000003 L 297.76751302083335 150.08471000000003 L 306.00071614583334 150.08471000000003 L 306.00071614583334 150.08471000000003 L 306.00071614583334 150.08471000000003 L 306.00071614583334 150.08471000000003 L 306.00071614583334 150.08471000000003 L 297.76751302083335 150.08471000000003 z"
                                            cy="78.99190000000003" cx="352.65553385416666" j="5" val="27"
                                            barHeight="71.09181" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1507"
                                            d="M 352.65553385416666 168.51592 L 352.65553385416666 121.12137999999999 L 360.88873697916665 121.12137999999999 L 360.88873697916665 168.51592 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 352.65553385416666 168.51592 L 352.65553385416666 121.12137999999999 L 360.88873697916665 121.12137999999999 L 360.88873697916665 168.51592 z"
                                            pathFrom="M 352.65553385416666 168.51592 L 352.65553385416666 168.51592 L 360.88873697916665 168.51592 L 360.88873697916665 168.51592 L 360.88873697916665 168.51592 L 360.88873697916665 168.51592 L 360.88873697916665 168.51592 L 352.65553385416666 168.51592 z"
                                            cy="121.12037999999998" cx="407.5435546875" j="6" val="18"
                                            barHeight="47.39454" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1509"
                                            d="M 407.5435546875 126.38744 L 407.5435546875 68.46078 L 415.77675781249997 68.46078 L 415.77675781249997 126.38744 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 407.5435546875 126.38744 L 407.5435546875 68.46078 L 415.77675781249997 68.46078 L 415.77675781249997 126.38744 z"
                                            pathFrom="M 407.5435546875 126.38744 L 407.5435546875 126.38744 L 415.77675781249997 126.38744 L 415.77675781249997 126.38744 L 415.77675781249997 126.38744 L 415.77675781249997 126.38744 L 415.77675781249997 126.38744 L 407.5435546875 126.38744 z"
                                            cy="68.45978" cx="462.4315755208333" j="7" val="22" barHeight="57.92666"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1511"
                                            d="M 462.4315755208333 200.11228 L 462.4315755208333 173.78198 L 470.6647786458333 173.78198 L 470.6647786458333 200.11228 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 462.4315755208333 200.11228 L 462.4315755208333 173.78198 L 470.6647786458333 173.78198 L 470.6647786458333 200.11228 z"
                                            pathFrom="M 462.4315755208333 200.11228 L 462.4315755208333 200.11228 L 470.6647786458333 200.11228 L 470.6647786458333 200.11228 L 470.6647786458333 200.11228 L 470.6647786458333 200.11228 L 470.6647786458333 200.11228 L 462.4315755208333 200.11228 z"
                                            cy="173.78098" cx="517.3195963541666" j="8" val="10"
                                            barHeight="26.330299999999998" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1513"
                                            d="M 517.3195963541666 215.91046 L 517.3195963541666 173.78198 L 525.5527994791667 173.78198 L 525.5527994791667 215.91046 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 517.3195963541666 215.91046 L 517.3195963541666 173.78198 L 525.5527994791667 173.78198 L 525.5527994791667 215.91046 z"
                                            pathFrom="M 517.3195963541666 215.91046 L 517.3195963541666 215.91046 L 525.5527994791667 215.91046 L 525.5527994791667 215.91046 L 525.5527994791667 215.91046 L 525.5527994791667 215.91046 L 525.5527994791667 215.91046 L 517.3195963541666 215.91046 z"
                                            cy="173.78098" cx="572.2076171875" j="9" val="16"
                                            barHeight="42.128479999999996" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1515"
                                            d="M 572.2076171875 168.51592 L 572.2076171875 105.3232 L 580.4408203125 105.3232 L 580.4408203125 168.51592 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 572.2076171875 168.51592 L 572.2076171875 105.3232 L 580.4408203125 105.3232 L 580.4408203125 168.51592 z"
                                            pathFrom="M 572.2076171875 168.51592 L 572.2076171875 168.51592 L 580.4408203125 168.51592 L 580.4408203125 168.51592 L 580.4408203125 168.51592 L 580.4408203125 168.51592 L 580.4408203125 168.51592 L 572.2076171875 168.51592 z"
                                            cy="105.3222" cx="627.0956380208333" j="10" val="24"
                                            barHeight="63.192719999999994" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1517"
                                            d="M 627.0956380208333 136.91956000000002 L 627.0956380208333 78.99290000000002 L 635.3288411458334 78.99290000000002 L 635.3288411458334 136.91956000000002 z"
                                            fill="rgba(241,180,76,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="1" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 627.0956380208333 136.91956000000002 L 627.0956380208333 78.99290000000002 L 635.3288411458334 78.99290000000002 L 635.3288411458334 136.91956000000002 z"
                                            pathFrom="M 627.0956380208333 136.91956000000002 L 627.0956380208333 136.91956000000002 L 635.3288411458334 136.91956000000002 L 635.3288411458334 136.91956000000002 L 635.3288411458334 136.91956000000002 L 635.3288411458334 136.91956000000002 L 635.3288411458334 136.91956000000002 L 627.0956380208333 136.91956000000002 z"
                                            cy="78.99190000000002" cx="681.9836588541667" j="11" val="22"
                                            barHeight="57.92666" barWidth="8.233203125"></path>
                                        <g id="SvgjsG1493" class="apexcharts-bar-goals-markers">
                                            <g id="SvgjsG1494" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1496" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1498" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1500" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1502" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1504" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1506" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1508" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1510" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1512" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1514" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1516" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                        </g>
                                    </g>
                                    <g id="SvgjsG1518" class="apexcharts-series" seriesName="SeriesxC" rel="3"
                                        data:realIndex="2">
                                        <path id="SvgjsPath1522"
                                            d="M 23.32740885416667 113.22329000000002 L 23.32740885416667 84.25996000000002 L 31.56061197916667 84.25996000000002 L 31.56061197916667 113.22329000000002 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 23.32740885416667 113.22329000000002 L 23.32740885416667 84.25996000000002 L 31.56061197916667 84.25996000000002 L 31.56061197916667 113.22329000000002 z"
                                            pathFrom="M 23.32740885416667 113.22329000000002 L 23.32740885416667 113.22329000000002 L 31.56061197916667 113.22329000000002 L 31.56061197916667 113.22329000000002 L 31.56061197916667 113.22329000000002 L 31.56061197916667 113.22329000000002 L 31.56061197916667 113.22329000000002 L 23.32740885416667 113.22329000000002 z"
                                            cy="84.25896000000002" cx="78.2154296875" j="0" val="11"
                                            barHeight="28.96333" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1524"
                                            d="M 78.2154296875 57.92966000000002 L 78.2154296875 13.16815000000002 L 86.44863281250001 13.16815000000002 L 86.44863281250001 57.92966000000002 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 78.2154296875 57.92966000000002 L 78.2154296875 13.16815000000002 L 86.44863281250001 13.16815000000002 L 86.44863281250001 57.92966000000002 z"
                                            pathFrom="M 78.2154296875 57.92966000000002 L 78.2154296875 57.92966000000002 L 86.44863281250001 57.92966000000002 L 86.44863281250001 57.92966000000002 L 86.44863281250001 57.92966000000002 L 86.44863281250001 57.92966000000002 L 86.44863281250001 57.92966000000002 L 78.2154296875 57.92966000000002 z"
                                            cy="13.16715000000002" cx="133.10345052083335" j="1" val="17"
                                            barHeight="44.76151" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1526"
                                            d="M 133.10345052083335 102.69117000000003 L 133.10345052083335 63.19572000000002 L 141.33665364583334 63.19572000000002 L 141.33665364583334 102.69117000000003 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 133.10345052083335 102.69117000000003 L 133.10345052083335 63.19572000000002 L 141.33665364583334 63.19572000000002 L 141.33665364583334 102.69117000000003 z"
                                            pathFrom="M 133.10345052083335 102.69117000000003 L 133.10345052083335 102.69117000000003 L 141.33665364583334 102.69117000000003 L 141.33665364583334 102.69117000000003 L 141.33665364583334 102.69117000000003 L 141.33665364583334 102.69117000000003 L 141.33665364583334 102.69117000000003 L 133.10345052083335 102.69117000000003 z"
                                            cy="63.194720000000025" cx="187.9914713541667" j="2" val="15"
                                            barHeight="39.49545" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1528"
                                            d="M 187.9914713541667 65.82875000000003 L 187.9914713541667 26.333300000000026 L 196.22467447916668 26.333300000000026 L 196.22467447916668 65.82875000000003 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 187.9914713541667 65.82875000000003 L 187.9914713541667 26.333300000000026 L 196.22467447916668 26.333300000000026 L 196.22467447916668 65.82875000000003 z"
                                            pathFrom="M 187.9914713541667 65.82875000000003 L 187.9914713541667 65.82875000000003 L 196.22467447916668 65.82875000000003 L 196.22467447916668 65.82875000000003 L 196.22467447916668 65.82875000000003 L 196.22467447916668 65.82875000000003 L 196.22467447916668 65.82875000000003 L 187.9914713541667 65.82875000000003 z"
                                            cy="26.332300000000025" cx="242.87949218750003" j="3" val="15"
                                            barHeight="39.49545" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1530"
                                            d="M 242.87949218750003 171.14995000000002 L 242.87949218750003 115.85632000000001 L 251.11269531250002 115.85632000000001 L 251.11269531250002 171.14995000000002 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 242.87949218750003 171.14995000000002 L 242.87949218750003 115.85632000000001 L 251.11269531250002 115.85632000000001 L 251.11269531250002 171.14995000000002 z"
                                            pathFrom="M 242.87949218750003 171.14995000000002 L 242.87949218750003 171.14995000000002 L 251.11269531250002 171.14995000000002 L 251.11269531250002 171.14995000000002 L 251.11269531250002 171.14995000000002 L 251.11269531250002 171.14995000000002 L 251.11269531250002 171.14995000000002 L 242.87949218750003 171.14995000000002 z"
                                            cy="115.85532" cx="297.76751302083335" j="4" val="21" barHeight="55.29363"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1532"
                                            d="M 297.76751302083335 78.99390000000004 L 297.76751302083335 42.13148000000003 L 306.00071614583334 42.13148000000003 L 306.00071614583334 78.99390000000004 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 297.76751302083335 78.99390000000004 L 297.76751302083335 42.13148000000003 L 306.00071614583334 42.13148000000003 L 306.00071614583334 78.99390000000004 z"
                                            pathFrom="M 297.76751302083335 78.99390000000004 L 297.76751302083335 78.99390000000004 L 306.00071614583334 78.99390000000004 L 306.00071614583334 78.99390000000004 L 306.00071614583334 78.99390000000004 L 306.00071614583334 78.99390000000004 L 306.00071614583334 78.99390000000004 L 297.76751302083335 78.99390000000004 z"
                                            cy="42.130480000000034" cx="352.65553385416666" j="5" val="14"
                                            barHeight="36.86242" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1534"
                                            d="M 352.65553385416666 121.12237999999999 L 352.65553385416666 92.15905 L 360.88873697916665 92.15905 L 360.88873697916665 121.12237999999999 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 352.65553385416666 121.12237999999999 L 352.65553385416666 92.15905 L 360.88873697916665 92.15905 L 360.88873697916665 121.12237999999999 z"
                                            pathFrom="M 352.65553385416666 121.12237999999999 L 352.65553385416666 121.12237999999999 L 360.88873697916665 121.12237999999999 L 360.88873697916665 121.12237999999999 L 360.88873697916665 121.12237999999999 L 360.88873697916665 121.12237999999999 L 360.88873697916665 121.12237999999999 L 352.65553385416666 121.12237999999999 z"
                                            cy="92.15804999999999" cx="407.5435546875" j="6" val="11"
                                            barHeight="28.96333" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1536"
                                            d="M 407.5435546875 68.46178 L 407.5435546875 21.06724 L 415.77675781249997 21.06724 L 415.77675781249997 68.46178 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 407.5435546875 68.46178 L 407.5435546875 21.06724 L 415.77675781249997 21.06724 L 415.77675781249997 68.46178 z"
                                            pathFrom="M 407.5435546875 68.46178 L 407.5435546875 68.46178 L 415.77675781249997 68.46178 L 415.77675781249997 68.46178 L 415.77675781249997 68.46178 L 415.77675781249997 68.46178 L 415.77675781249997 68.46178 L 407.5435546875 68.46178 z"
                                            cy="21.06624" cx="462.4315755208333" j="7" val="18" barHeight="47.39454"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1538"
                                            d="M 462.4315755208333 173.78298 L 462.4315755208333 129.02147 L 470.6647786458333 129.02147 L 470.6647786458333 173.78298 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 462.4315755208333 173.78298 L 462.4315755208333 129.02147 L 470.6647786458333 129.02147 L 470.6647786458333 173.78298 z"
                                            pathFrom="M 462.4315755208333 173.78298 L 462.4315755208333 173.78298 L 470.6647786458333 173.78298 L 470.6647786458333 173.78298 L 470.6647786458333 173.78298 L 470.6647786458333 173.78298 L 470.6647786458333 173.78298 L 462.4315755208333 173.78298 z"
                                            cy="129.02047" cx="517.3195963541666" j="8" val="17" barHeight="44.76151"
                                            barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1540"
                                            d="M 517.3195963541666 173.78298 L 517.3195963541666 142.18662 L 525.5527994791667 142.18662 L 525.5527994791667 173.78298 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 517.3195963541666 173.78298 L 517.3195963541666 142.18662 L 525.5527994791667 142.18662 L 525.5527994791667 173.78298 z"
                                            pathFrom="M 517.3195963541666 173.78298 L 517.3195963541666 173.78298 L 525.5527994791667 173.78298 L 525.5527994791667 173.78298 L 525.5527994791667 173.78298 L 525.5527994791667 173.78298 L 525.5527994791667 173.78298 L 517.3195963541666 173.78298 z"
                                            cy="142.18562" cx="572.2076171875" j="9" val="12"
                                            barHeight="31.596359999999997" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1542"
                                            d="M 572.2076171875 105.3242 L 572.2076171875 52.6636 L 580.4408203125 52.6636 L 580.4408203125 105.3242 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 572.2076171875 105.3242 L 572.2076171875 52.6636 L 580.4408203125 52.6636 L 580.4408203125 105.3242 z"
                                            pathFrom="M 572.2076171875 105.3242 L 572.2076171875 105.3242 L 580.4408203125 105.3242 L 580.4408203125 105.3242 L 580.4408203125 105.3242 L 580.4408203125 105.3242 L 580.4408203125 105.3242 L 572.2076171875 105.3242 z"
                                            cy="52.662600000000005" cx="627.0956380208333" j="10" val="20"
                                            barHeight="52.660599999999995" barWidth="8.233203125"></path>
                                        <path id="SvgjsPath1544"
                                            d="M 627.0956380208333 78.99390000000002 L 627.0956380208333 31.599360000000022 L 635.3288411458334 31.599360000000022 L 635.3288411458334 78.99390000000002 z"
                                            fill="rgba(52,195,143,1)" fill-opacity="1" stroke-opacity="1"
                                            stroke-linecap="round" stroke-width="0" stroke-dasharray="0"
                                            class="apexcharts-bar-area" index="2" clip-path="url(#gridRectMask73xi2lex)"
                                            pathTo="M 627.0956380208333 78.99390000000002 L 627.0956380208333 31.599360000000022 L 635.3288411458334 31.599360000000022 L 635.3288411458334 78.99390000000002 z"
                                            pathFrom="M 627.0956380208333 78.99390000000002 L 627.0956380208333 78.99390000000002 L 635.3288411458334 78.99390000000002 L 635.3288411458334 78.99390000000002 L 635.3288411458334 78.99390000000002 L 635.3288411458334 78.99390000000002 L 635.3288411458334 78.99390000000002 L 627.0956380208333 78.99390000000002 z"
                                            cy="31.59836000000002" cx="681.9836588541667" j="11" val="18"
                                            barHeight="47.39454" barWidth="8.233203125"></path>
                                        <g id="SvgjsG1520" class="apexcharts-bar-goals-markers">
                                            <g id="SvgjsG1521" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1523" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1525" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1527" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1529" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1531" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1533" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1535" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1537" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1539" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1541" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                            <g id="SvgjsG1543" className="apexcharts-bar-goals-groups"
                                                class="apexcharts-hidden-element-shown"
                                                clip-path="url(#gridRectMarkerMask73xi2lex)"></g>
                                        </g>
                                    </g>
                                    <g id="SvgjsG1465" class="apexcharts-datalabels" data:realIndex="0"></g>
                                    <g id="SvgjsG1492" class="apexcharts-datalabels" data:realIndex="1"></g>
                                    <g id="SvgjsG1519" class="apexcharts-datalabels" data:realIndex="2"></g>
                                </g>
                                <line id="SvgjsLine1570" x1="0" y1="0" x2="658.65625" y2="0" stroke="#b6b6b6"
                                    stroke-dasharray="0" stroke-width="1" stroke-linecap="butt"
                                    class="apexcharts-ycrosshairs"></line>
                                <line id="SvgjsLine1571" x1="0" y1="0" x2="658.65625" y2="0" stroke-dasharray="0"
                                    stroke-width="0" stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line>
                                <g id="SvgjsG1572" class="apexcharts-xaxis" transform="translate(0, 0)">
                                    <g id="SvgjsG1573" class="apexcharts-xaxis-texts-g" transform="translate(0, -4)">
                                        <text id="SvgjsText1575" font-family="Helvetica, Arial, sans-serif"
                                            x="27.444010416666668" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1576">Jan</tspan>
                                            <title>Jan</title>
                                        </text><text id="SvgjsText1578" font-family="Helvetica, Arial, sans-serif"
                                            x="82.33203125" y="292.303" text-anchor="middle" dominant-baseline="auto"
                                            font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1579">Feb</tspan>
                                            <title>Feb</title>
                                        </text><text id="SvgjsText1581" font-family="Helvetica, Arial, sans-serif"
                                            x="137.22005208333334" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1582">Mar</tspan>
                                            <title>Mar</title>
                                        </text><text id="SvgjsText1584" font-family="Helvetica, Arial, sans-serif"
                                            x="192.10807291666669" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1585">Apr</tspan>
                                            <title>Apr</title>
                                        </text><text id="SvgjsText1587" font-family="Helvetica, Arial, sans-serif"
                                            x="246.99609375000003" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1588">May</tspan>
                                            <title>May</title>
                                        </text><text id="SvgjsText1590" font-family="Helvetica, Arial, sans-serif"
                                            x="301.8841145833333" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1591">Jun</tspan>
                                            <title>Jun</title>
                                        </text><text id="SvgjsText1593" font-family="Helvetica, Arial, sans-serif"
                                            x="356.77213541666663" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1594">Jul</tspan>
                                            <title>Jul</title>
                                        </text><text id="SvgjsText1596" font-family="Helvetica, Arial, sans-serif"
                                            x="411.66015624999994" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1597">Aug</tspan>
                                            <title>Aug</title>
                                        </text><text id="SvgjsText1599" font-family="Helvetica, Arial, sans-serif"
                                            x="466.54817708333326" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1600">Sep</tspan>
                                            <title>Sep</title>
                                        </text><text id="SvgjsText1602" font-family="Helvetica, Arial, sans-serif"
                                            x="521.4361979166666" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1603">Oct</tspan>
                                            <title>Oct</title>
                                        </text><text id="SvgjsText1605" font-family="Helvetica, Arial, sans-serif"
                                            x="576.32421875" y="292.303" text-anchor="middle" dominant-baseline="auto"
                                            font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1606">Nov</tspan>
                                            <title>Nov</title>
                                        </text><text id="SvgjsText1608" font-family="Helvetica, Arial, sans-serif"
                                            x="631.2122395833334" y="292.303" text-anchor="middle"
                                            dominant-baseline="auto" font-size="12px" font-weight="400" fill="#373d3f"
                                            class="apexcharts-text apexcharts-xaxis-label "
                                            style="font-family: Helvetica, Arial, sans-serif;">
                                            <tspan id="SvgjsTspan1609">Dec</tspan>
                                            <title>Dec</title>
                                        </text>
                                    </g>
                                </g>
                                <g id="SvgjsG1631" class="apexcharts-yaxis-annotations"></g>
                                <g id="SvgjsG1632" class="apexcharts-xaxis-annotations"></g>
                                <g id="SvgjsG1633" class="apexcharts-point-annotations"></g>
                            </g>
                        </svg>
                        <div class="apexcharts-tooltip apexcharts-theme-light">
                            <div class="apexcharts-tooltip-title"
                                style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"></div>
                            <div class="apexcharts-tooltip-series-group" style="order: 1;"><span
                                    class="apexcharts-tooltip-marker"
                                    style="background-color: rgb(85, 110, 230);"></span>
                                <div class="apexcharts-tooltip-text"
                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                    <div class="apexcharts-tooltip-y-group"><span
                                            class="apexcharts-tooltip-text-y-label"></span><span
                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                    <div class="apexcharts-tooltip-goals-group"><span
                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                    <div class="apexcharts-tooltip-z-group"><span
                                            class="apexcharts-tooltip-text-z-label"></span><span
                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                </div>
                            </div>
                            <div class="apexcharts-tooltip-series-group" style="order: 2;"><span
                                    class="apexcharts-tooltip-marker"
                                    style="background-color: rgb(241, 180, 76);"></span>
                                <div class="apexcharts-tooltip-text"
                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                    <div class="apexcharts-tooltip-y-group"><span
                                            class="apexcharts-tooltip-text-y-label"></span><span
                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                    <div class="apexcharts-tooltip-goals-group"><span
                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                    <div class="apexcharts-tooltip-z-group"><span
                                            class="apexcharts-tooltip-text-z-label"></span><span
                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                </div>
                            </div>
                            <div class="apexcharts-tooltip-series-group" style="order: 3;"><span
                                    class="apexcharts-tooltip-marker"
                                    style="background-color: rgb(52, 195, 143);"></span>
                                <div class="apexcharts-tooltip-text"
                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                    <div class="apexcharts-tooltip-y-group"><span
                                            class="apexcharts-tooltip-text-y-label"></span><span
                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                    <div class="apexcharts-tooltip-goals-group"><span
                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                    <div class="apexcharts-tooltip-z-group"><span
                                            class="apexcharts-tooltip-text-z-label"></span><span
                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="apexcharts-yaxistooltip apexcharts-yaxistooltip-0 apexcharts-yaxistooltip-left apexcharts-theme-light">
                            <div class="apexcharts-yaxistooltip-text"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 card-title">Social Source</div>
                <div class="text-center">
                    <div class="avatar-sm mx-auto mb-4"><span
                            class="avatar-title rounded-circle bg-primary-subtle font-size-24"><i
                                class="mdi mdi-facebook text-primary"></i></span></div>
                    <p class="font-16 text-muted mb-2"></p>
                    <h5><a class="text-dark" href="/dashboard">Facebook - <span class="text-muted font-16">125
                                sales</span> </a></h5>
                    <p class="text-muted">Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero
                        venenatis faucibus tincidunt.</p><a class="text-primary font-16" href="/dashboard">Learn more <i
                            class="mdi mdi-chevron-right"></i></a>
                </div>
                <div class="mt-4 row">
                    <div class="col-4 col">
                        <div class="social-source text-center mt-3">
                            <div class="avatar-xs mx-auto mb-3"><span
                                    class="avatar-title rounded-circle bg-primary font-size-16"><i
                                        class="mdi mdi-facebook text-white"></i></span></div>
                            <h5 class="font-size-15">Facebook</h5>
                            <p class="text-muted mb-0">125 sales</p>
                        </div>
                    </div>
                    <div class="col-4 col">
                        <div class="social-source text-center mt-3">
                            <div class="avatar-xs mx-auto mb-3"><span
                                    class="avatar-title rounded-circle bg-info font-size-16"><i
                                        class="mdi mdi-twitter text-white"></i></span></div>
                            <h5 class="font-size-15">Twitter</h5>
                            <p class="text-muted mb-0">112 sales</p>
                        </div>
                    </div>
                    <div class="col-4 col">
                        <div class="social-source text-center mt-3">
                            <div class="avatar-xs mx-auto mb-3"><span
                                    class="avatar-title rounded-circle bg-pink font-size-16"><i
                                        class="mdi mdi-instagram text-white"></i></span></div>
                            <h5 class="font-size-15">Instagram</h5>
                            <p class="text-muted mb-0">104 sales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-5 card-title">Activity</div>
                <ul class="verti-timeline list-unstyled">
                    <li class="event-list false">
                        <div class="event-timeline-dot"><i class="bx bx-right-arrow-circle font-size-18 false"></i>
                        </div>
                        <div class="flex-shrink-0 d-flex">
                            <div class="me-3">
                                <h5 class="font-size-14">22 Nov<i
                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                </h5>
                            </div>
                            <div class="flex-grow-1">
                                <div>Responded to need “Volunteer Activities”</div>
                            </div>
                        </div>
                    </li>
                    <li class="event-list false">
                        <div class="event-timeline-dot"><i class="bx bx-right-arrow-circle font-size-18 false"></i>
                        </div>
                        <div class="flex-shrink-0 d-flex">
                            <div class="me-3">
                                <h5 class="font-size-14">17 Nov<i
                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                </h5>
                            </div>
                            <div class="flex-grow-1">
                                <div>Everyone realizes why a new common language would be desirable... Read More</div>
                            </div>
                        </div>
                    </li>
                    <li class="event-list active">
                        <div class="event-timeline-dot"><i
                                class="bx bx-right-arrow-circle font-size-18 bx-fade-right"></i></div>
                        <div class="flex-shrink-0 d-flex">
                            <div class="me-3">
                                <h5 class="font-size-14">15 Nov<i
                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                </h5>
                            </div>
                            <div class="flex-grow-1">
                                <div>Joined the group “Boardsmanship Forum”</div>
                            </div>
                        </div>
                    </li>
                    <li class="event-list false">
                        <div class="event-timeline-dot"><i class="bx bx-right-arrow-circle font-size-18 false"></i>
                        </div>
                        <div class="flex-shrink-0 d-flex">
                            <div class="me-3">
                                <h5 class="font-size-14">22 Nov<i
                                        class="bx bx-right-arrow-alt font-size-16 text-primary align-middle ms-2"></i>
                                </h5>
                            </div>
                            <div class="flex-grow-1">
                                <div>Responded to need “In-Kind Opportunity”</div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="text-center mt-4"><a class="btn btn-primary waves-effect waves-light btn-sm"
                        href="/dashboard">View More <i class="mdi mdi-arrow-right ms-1"></i></a></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 card-title">Top Cities Selling Product</div>
                <div class="text-center">
                    <div class="mb-4"><i class="bx bx-map-pin text-primary display-4"></i></div>
                    <h3>1,456</h3>
                    <p>San Francisco</p>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table align-middle table-nowrap">
                        <tbody>
                            <tr>
                                <td style="width: 30%;">
                                    <p class="mb-0">San Francisco</p>
                                </td>
                                <td style="width: 25%;">
                                    <h5 class="mb-0">1,456</h5>
                                </td>
                                <td>
                                    <div class="bg-transparent progress-sm progress">
                                        <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="94"
                                            aria-valuemin="0" aria-valuemax="100" style="width: 94%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%;">
                                    <p class="mb-0">Los Angeles</p>
                                </td>
                                <td style="width: 25%;">
                                    <h5 class="mb-0">1,123</h5>
                                </td>
                                <td>
                                    <div class="bg-transparent progress-sm progress">
                                        <div class="progress-bar bg-success" role="progressbar" aria-valuenow="82"
                                            aria-valuemin="0" aria-valuemax="100" style="width: 82%;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 30%;">
                                    <p class="mb-0">San Diego</p>
                                </td>
                                <td style="width: 25%;">
                                    <h5 class="mb-0">1,026</h5>
                                </td>
                                <td>
                                    <div class="bg-transparent progress-sm progress">
                                        <div class="progress-bar bg-warning" role="progressbar" aria-valuenow="70"
                                            aria-valuemin="0" aria-valuemax="100" style="width: 70%;"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>