<template>
  <div style="z-index: 0">
    <v-card class="elevation-3 LogsCard">
      <v-progress-linear v-if="progress" indeterminate />
      <div class="pt-6 px-5 pb-3">
        <v-row no-gutters>
          <v-col cols="8">
            <div class="ml-5 text-caption font-weight-bold">LOGS</div>
            <div class="ml-5 mt-1 caption black--text">
              Values are indicative of:
              <v-menu
                ref="menu"
                :close-on-content-click="false"
                :return-value.sync="datepicker.date"
                transition="scale-transition"
                min-width="auto"
              >
                <template v-slot:activator="{ on, attrs }">
                  <v-btn
                    outlined
                    x-small
                    color="black"
                    style="margin-top: -1px"
                    dark
                    v-bind="attrs"
                    v-on="on"
                  >
                    <span class="font-weight-bold">{{ datepicker.date }}</span>
                  </v-btn>
                </template>
                <v-date-picker
                  v-model="datepicker.date"
                  no-title
                  scrollable
                  @input="
                    $refs.menu.save(datepicker.date);
                    datepicker.visible = false;
                  "
                />
              </v-menu>
            </div>
          </v-col>
          <v-col align="end">
            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn x-small fab text class="mr-5" v-bind="attrs" v-on="on"
                  ><v-icon style="z-index: 1" size="20"
                    >mdi-open-in-app</v-icon
                  ></v-btn
                >
              </template>
              <span>Open logs table</span>
            </v-tooltip>

            <v-tooltip bottom>
              <template v-slot:activator="{ on, attrs }">
                <v-btn
                  x-small
                  fab
                  text
                  v-bind="attrs"
                  v-on="on"
                  @click="Logs(datepicker.date)"
                  ><v-icon style="z-index: 1" size="20"
                    >mdi-refresh</v-icon
                  ></v-btn
                >
              </template>
              <span>Refresh</span>
            </v-tooltip>
          </v-col>
        </v-row>

        <v-row class="ml-2">
          <v-col cols="4">
            <v-row>
              <v-icon color="orange" size="40" class="mx-2"
                >mdi-car-brake-alert</v-icon
              >
              <div>
                <div class="text-h6 mb-n2">{{ DailyWarnings }} Warnings</div>
                <small>Registered today</small>
              </div>
            </v-row>
            <div
              ref="LogWarningsChart"
              class="chart"
              style="height: 70px; width: 100%"
            />
          </v-col>
          <v-col cols="4">
            <v-row>
              <v-icon color="red" size="40" class="mx-2"
                >mdi-close-circle-outline</v-icon
              >
              <div>
                <div class="text-h6 mb-n2">{{ DailyErrors }} Errors</div>
                <small>Registered today</small>
              </div>
            </v-row>
            <div
              ref="LogErrorsChart"
              class="chart"
              style="height: 70px; width: 100%"
            />
          </v-col>
          <v-col cols="4">
            <v-row>
              <v-icon color="brown" size="40" class="mx-2">mdi-skull</v-icon>
              <div>
                <div class="text-h6 mb-n2">{{ DailyNotices }} Notices</div>
                <small>Registered today</small>
              </div>
            </v-row>
            <div
              ref="LogNoticesChart"
              class="chart"
              style="height: 70px; width: 100%"
            />
          </v-col>
        </v-row>
        <div ref="LogsChart" class="chart" style="height: 310px" />
      </div>
    </v-card>
  </div>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";
am4core.useTheme(am4themes_animated);

export default {
  data() {
    return {
      progress: false,
      datepicker: {
        visible: false,
        date: new Date().toISOString().substr(0, 10),
      },
      DailyWarnings: 0,
      DailyErrors: 0,
      DailyNotices: 0,
    };
  },
  created() {
    this.Logs(this.datepicker.date);
  },
  watch: {
    "datepicker.date"(v) {
      this.Logs(v);
    },
  },
  methods: {
    Logs(date) {
      this.progress = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Dashboard/Logs/", {
          method: "post",
          body: JSON.stringify({
            token: "",
            date,
          }),
        })
      )
        .then((response) => {
          if (response.status > 200) {
            this.$refs.Snackbar.Snackify({
              visible: true,
              content:
                "We were unable to retrieve some information. Kindly refresh to retry.",
              color: "error",
              timeout: 10000,
            });
          } else {
            response.text().then((res) => {
              let logs = JSON.parse(res);
              this.DailyWarnings = logs.DailyLogs.warnings;
              this.DailyErrors = logs.DailyLogs.errors;
              this.DailyNotices = logs.DailyLogs.notices;
              this.LogsChart(logs.HourlyLogs);
              this.MiniLogsChart(
                "LogWarningsChart",
                "Monthly warnings",
                "orange",
                "warnings",
                logs.MonthlyLogs
              );
              this.MiniLogsChart(
                "LogErrorsChart",
                "Monthly errors",
                "red",
                "errors",
                logs.MonthlyLogs
              );
              this.MiniLogsChart(
                "LogNoticesChart",
                "Monthly notices",
                "brown",
                "notices",
                logs.MonthlyLogs
              );
            });
          }
        })
        .catch(() => {
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          this.progress = false;
        });
    },
    LogsChart(data) {
      let chart = am4core.create(this.$refs.LogsChart, am4charts.XYChart);
      chart.logo.disabled = true;
      chart.fontSize = 13;
      //chart.colors.step = 4;
      chart.data = data;

      chart.exporting.menu = new am4core.ExportMenu();
      chart.exporting.menu.align = "left";
      chart.exporting.menu.verticalAlign = "bottom";
      chart.exporting.menu.items = [
        {
          label: "Export Chart",
          menu: [
            {
              label: "Image",
              menu: [
                { type: "png", label: "PNG" },
                { type: "jpg", label: "JPG" },
                { type: "svg", label: "SVG" },
                { type: "pdf", label: "PDF" },
              ],
            },
            {
              label: "Data",
              menu: [
                { type: "json", label: "JSON" },
                { type: "csv", label: "CSV" },
                { type: "xlsx", label: "XLSX" },
                { type: "html", label: "HTML" },
                { type: "pdfdata", label: "PDF" },
              ],
            },
            {
              label: "Print",
              type: "print",
            },
          ],
        },
      ];

      let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "time";

      function createAxisAndSeries(field, name, opposite, bullet, color) {
        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        if (chart.yAxes.indexOf(valueAxis) != 0) {
          valueAxis.syncWithAxis = chart.yAxes.getIndex(0);
        }

        var series = chart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.categoryX = "time";
        series.strokeWidth = 2;
        series.yAxis = valueAxis;
        series.name = name;
        series.stroke = color;
        series.tooltipText = "{name}: [bold]{valueY}[/]";
        series.tensionX = 0.8;
        series.showOnInit = true;

        switch (bullet) {
          case "triangle":
            var bullet1 = series.bullets.push(new am4charts.Bullet());
            bullet1.width = 6;
            bullet1.height = 6;
            bullet1.horizontalCenter = "middle";
            bullet1.verticalCenter = "middle";

            var triangle = bullet1.createChild(am4core.Triangle);
            triangle.fill = color;
            triangle.direction = "top";
            triangle.width = 6;
            triangle.height = 6;
            break;
          case "rectangle":
            var bullet2 = series.bullets.push(new am4charts.Bullet());
            bullet2.width = 6;
            bullet2.height = 6;
            bullet2.horizontalCenter = "middle";
            bullet2.verticalCenter = "middle";

            var rectangle = bullet2.createChild(am4core.Rectangle);
            rectangle.stroke = color;
            rectangle.fill = color;
            rectangle.width = 6;
            rectangle.height = 6;
            break;
          default:
            var bullet3 = series.bullets.push(new am4charts.CircleBullet());
            bullet3.circle.stroke = color;
            bullet3.circle.fill = color;
            bullet3.width = 6;
            bullet3.height = 6;
            break;
        }

        valueAxis.renderer.line.strokeOpacity = 1;
        valueAxis.renderer.line.strokeWidth = 2;
        valueAxis.renderer.line.stroke = series.stroke;
        valueAxis.renderer.labels.template.fill = series.stroke;
        valueAxis.renderer.opposite = opposite;
      }

      createAxisAndSeries("warnings", "Warnings", false, "circle", "orange");
      createAxisAndSeries("errors", "Errors", true, "triangle", "red");
      createAxisAndSeries("notices", "Notices", true, "rectangle", "brown");

      chart.legend = new am4charts.Legend();
      chart.cursor = new am4charts.XYCursor();
    },
    MiniLogsChart(reference, title, color, category, data) {
      var container = am4core.create(this.$refs[reference], am4core.Container);
      container.logo.disabled = true;
      container.layout = "grid";
      container.fixedWidthGrid = false;
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);

      var chart = container.createChild(am4charts.XYChart);
      chart.width = am4core.percent(75);
      chart.height = 70;

      chart.data = data;

      chart.titles.template.fontSize = 10;
      chart.titles.template.textAlign = "left";
      chart.titles.template.isMeasured = true;
      chart.titles.create().text = title;

      chart.padding(20, 0, 0, 0);

      var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.grid.template.disabled = true;
      dateAxis.renderer.labels.template.disabled = true;
      dateAxis.startLocation = 0.5;
      dateAxis.endLocation = 0.7;
      dateAxis.cursorTooltipEnabled = false;

      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.renderer.grid.template.disabled = true;
      valueAxis.renderer.baseGrid.disabled = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.cursorTooltipEnabled = false;

      chart.cursor = new am4charts.XYCursor();
      chart.cursor.lineY.disabled = true;
      chart.cursor.behavior = "none";

      var series = chart.series.push(new am4charts.LineSeries());
      series.tooltipText = "{date}: [bold]{" + category + "}";
      series.dataFields.dateX = "date";
      series.dataFields.valueY = category;
      series.tensionX = 0.8;
      series.strokeWidth = 2;
      series.stroke = color;

      // render data points as bullets
      var bullet = series.bullets.push(new am4charts.CircleBullet());
      bullet.circle.opacity = 0;
      bullet.circle.fill = color;
      bullet.circle.propertyFields.opacity = "opacity";
      bullet.circle.radius = 3;
    },
  },
};
</script>

<style>
.LogsCard {
  background: linear-gradient(45deg, rgb(253 253 253), rgb(217 255 250));
}
/* .amcharts-amexport-menu {
  margin: 5px 5px 0 0 !important;
} */
/* .amcharts-amexport-item {
  border: 2px solid #777;
  text-align: center;
  width: 100% !important;
  padding: 3px 5px 0 5px !important;
  background: green !important;
}
.amcharts-amexport-label {
  font-size: 13px;
  padding: 5px;
  color: white !important;
} */
.amcharts-amexport-top .amcharts-amexport-item > .amcharts-amexport-menu {
  top: -30px !important;
  left: -30px !important;
}
</style>
