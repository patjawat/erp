"use strict";

!(function () {
  let e, t, o, r;
  o = isDarkStyle
    ? ((e = config.colors_dark.textMuted),
      (t = config.colors_dark.headingColor),
      (r = config.colors_dark.bodyColor),
      "dark")
    : ((e = config.colors.textMuted),
      (t = config.colors.headingColor),
      (r = config.colors.bodyColor),
      "light");
  var s = {
      donut: {
        series1: config.colors.success,
        series2: "#43ff64e6",
        series3: "#43ff6473",
        series4: "#43ff6433",
      },
      line: {
        series1: config.colors.warning,
        series2: config.colors.primary,
        series3: "#7367f029",
      },
    },
    a = document.querySelector("#shipmentStatisticsChart"),
    i = {
      series: [
        {
          name: "Shipment",
          type: "column",
          data: [38, 45, 33, 38, 32, 50, 48, 40, 42, 37],
        },
        {
          name: "Delivery",
          type: "line",
          data: [23, 28, 23, 32, 28, 44, 32, 38, 26, 34],
        },
      ],
      chart: {
        height: 270,
        type: "line",
        stacked: !1,
        parentHeightOffset: 0,
        toolbar: { show: !1 },
        zoom: { enabled: !1 },
      },
      markers: {
        size: 4,
        colors: [config.colors.white],
        strokeColors: s.line.series2,
        hover: { size: 6 },
        borderRadius: 4,
      },
      stroke: { curve: "smooth", width: [0, 3], lineCap: "round" },
      legend: {
        show: !0,
        position: "bottom",
        markers: { width: 8, height: 8, offsetX: -3 },
        height: 40,
        offsetY: 10,
        itemMargin: { horizontal: 10, vertical: 0 },
        fontSize: "15px",
        fontFamily: "Inter",
        fontWeight: 400,
        labels: { colors: t, useSeriesColors: !1 },
        offsetY: 10,
      },
      grid: { strokeDashArray: 8 },
      colors: [s.line.series1, s.line.series2],
      fill: { opacity: [1, 1] },
      plotOptions: {
        bar: {
          columnWidth: "30%",
          startingShape: "rounded",
          endingShape: "rounded",
          borderRadius: 4,
        },
      },
      dataLabels: { enabled: !1 },
      xaxis: {
        tickAmount: 10,
        categories: [
          "1 Jan",
          "2 Jan",
          "3 Jan",
          "4 Jan",
          "5 Jan",
          "6 Jan",
          "7 Jan",
          "8 Jan",
          "9 Jan",
          "10 Jan",
        ],
        labels: {
          style: {
            colors: e,
            fontSize: "13px",
            fontFamily: "Inter",
            fontWeight: 400,
          },
        },
        axisBorder: { show: !1 },
        axisTicks: { show: !1 },
      },
      yaxis: {
        tickAmount: 4,
        min: 10,
        max: 50,
        labels: {
          style: {
            colors: e,
            fontSize: "13px",
            fontFamily: "Inter",
            fontWeight: 400,
          },
          formatter: function (e) {
            return e + "%";
          },
        },
      },
      responsive: [
        {
          breakpoint: 1400,
          options: {
            chart: { height: 270 },
            xaxis: { labels: { style: { fontSize: "10px" } } },
            legend: {
              itemMargin: { vertical: 0, horizontal: 10 },
              fontSize: "13px",
              offsetY: 12,
            },
          },
        },
        {
          breakpoint: 1399,
          options: {
            chart: { height: 415 },
            plotOptions: { bar: { columnWidth: "50%" } },
          },
        },
        {
          breakpoint: 982,
          options: { plotOptions: { bar: { columnWidth: "30%" } } },
        },
        {
          breakpoint: 480,
          options: { chart: { height: 250 }, legend: { offsetY: 7 } },
        },
      ],
    },
    a =
      (null !== a && new ApexCharts(a, i).render(),
      document.querySelector("#deliveryExceptionsChart")),
    i = {
      chart: { height: 420, parentHeightOffset: 0, type: "donut" },
      labels: [
        "Incorrect address",
        "Weather conditions",
        "Federal Holidays",
        "Damage during transit",
      ],
      series: [13, 25, 22, 40],
      colors: [
        s.donut.series1,
        s.donut.series2,
        s.donut.series3,
        s.donut.series4,
      ],
      stroke: { width: 0 },
      dataLabels: {
        enabled: !1,
        formatter: function (e, t) {
          return parseInt(e) + "%";
        },
      },
      legend: {
        show: !0,
        position: "bottom",
        offsetY: 10,
        markers: { width: 8, height: 8, offsetX: -3 },
        itemMargin: { horizontal: 15, vertical: 5 },
        fontSize: "13px",
        fontFamily: "Inter",
        fontWeight: 400,
        labels: { colors: t, useSeriesColors: !1 },
      },
      tooltip: { theme: o },
      grid: { padding: { top: 15 } },
      plotOptions: {
        pie: {
          donut: {
            size: "75%",
            labels: {
              show: !0,
              value: {
                fontSize: "26px",
                fontFamily: "Inter",
                color: t,
                fontWeight: 500,
                offsetY: -30,
                formatter: function (e) {
                  return parseInt(e) + "%";
                },
              },
              name: { offsetY: 20, fontFamily: "Inter" },
              total: {
                show: !0,
                fontSize: "0.9rem",
                label: "AVG. Exceptions",
                color: r,
                formatter: function (e) {
                  return "30%";
                },
              },
            },
          },
        },
      },
      responsive: [{ breakpoint: 420, options: { chart: { height: 360 } } }],
    };
  null !== a && new ApexCharts(a, i).render();
})(),
  $(function () {
    var e = $(".dt-route-vehicles");
    e.length &&
      (e.DataTable({
        ajax: assetsPath + "json/logistics-dashboard.json",
        columns: [
          { data: "id" },
          { data: "id" },
          { data: "location" },
          { data: "start_city" },
          { data: "end_city" },
          { data: "warnings" },
          { data: "progress" },
        ],
        columnDefs: [
          {
            className: "control",
            orderable: !1,
            searchable: !1,
            responsivePriority: 2,
            targets: 0,
            render: function (e, t, o, r) {
              return "";
            },
          },
          {
            targets: 1,
            orderable: !1,
            searchable: !1,
            checkboxes: !0,
            checkboxes: {
              selectAllRender:
                '<input type="checkbox" class="form-check-input">',
            },
            responsivePriority: 3,
            render: function () {
              return '<input type="checkbox" class="dt-checkboxes form-check-input">';
            },
          },
          {
            targets: 2,
            responsivePriority: 1,
            render: function (e, t, o, r) {
              return (
                '<div class="d-flex justify-content-start align-items-center user-name"><div class="avatar-wrapper"><div class="avatar me-2"><span class="avatar-initial rounded-circle bg-label-secondary"><i class="mdi mdi-bus"></i></span></div></div><div class="d-flex flex-column"><a class="text-heading fw-medium" href="app-logistics-fleet.html">VOL-' +
                o.location +
                "</a></div></div>"
              );
            },
          },
          {
            targets: 3,
            render: function (e, t, o, r) {
              return (
                '<div class="text-body">' +
                o.start_city +
                ", " +
                o.start_country +
                "</div >"
              );
            },
          },
          {
            targets: 4,
            render: function (e, t, o, r) {
              return (
                '<div class="text-body">' +
                o.end_city +
                ", " +
                o.end_country +
                "</div >"
              );
            },
          },
          {
            targets: -2,
            render: function (e, t, o, r) {
              var o = o.warnings,
                s = {
                  1: { title: "No Warnings", class: "bg-label-success" },
                  2: {
                    title: "Temperature Not Optimal",
                    class: "bg-label-warning",
                  },
                  3: { title: "Ecu Not Responding", class: "bg-label-danger" },
                  4: { title: "Oil Leakage", class: "bg-label-info" },
                  5: { title: "fuel problems", class: "bg-label-primary" },
                };
              return void 0 === s[o]
                ? e
                : '<span class="badge rounded-pill ' +
                    s[o].class +
                    '">' +
                    s[o].title +
                    "</span>";
            },
          },
          {
            targets: -1,
            render: function (e, t, o, r) {
              o = o.progress;
              return (
                '<div class="d-flex align-items-center"><div div class="progress w-100 rounded" style="height: 8px;"><div class="progress-bar" role="progressbar" style="width:' +
                o +
                '%;" aria-valuenow="' +
                o +
                '" aria-valuemin="0" aria-valuemax="100"></div></div><div class="text-body ms-3">' +
                o +
                "%</div></div>"
              );
            },
          },
        ],
        order: [2, "asc"],
        dom: '<"table-responsive"t><"row d-flex align-items-center"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        displayLength: 5,
        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (e) {
                return "Details of " + e.data().location;
              },
            }),
            type: "column",
            renderer: function (e, t, o) {
              o = $.map(o, function (e, t) {
                return "" !== e.title
                  ? '<tr data-dt-row="' +
                      e.rowIndex +
                      '" data-dt-column="' +
                      e.columnIndex +
                      '"><td>' +
                      e.title +
                      ":</td> <td>" +
                      e.data +
                      "</td></tr>"
                  : "";
              }).join("");
              return !!o && $('<table class="table"/><tbody />').append(o);
            },
          },
        },
      }),
      $(".dataTables_info").addClass("pt-0"));
  });
