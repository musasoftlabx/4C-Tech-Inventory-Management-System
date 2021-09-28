<template>
  <div style="z-index: 0">
    <v-card outline class="elevation-5 MetricsCard">
      <v-progress-linear v-if="progress" indeterminate />
      <v-row no-gutters class="pt-6 mx-4">
        <v-col cols="8">
          <div class="ml-5 text-caption font-weight-bold">METRICS</div>
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
                @click="Metrics(datepicker.date)"
                ><v-icon style="z-index: 1" size="20"
                  >mdi-refresh</v-icon
                ></v-btn
              >
            </template>
            <span>Refresh</span>
          </v-tooltip>
        </v-col>
      </v-row>
      <v-row class="mx-8">
        <v-col cols="4">
          <v-row>
            <div ref="DeviceChart" style="height: 50px; width: 30%" />
            <div>
              <div class="text-h6 mb-n2">Device</div>
              <small
                ><span class="primary--text font-weight-bold">80%</span> Using
                Desktop</small
              >
            </div>
          </v-row>
        </v-col>
        <v-col cols="4">
          <v-row>
            <div ref="OSchart" style="height: 50px; width: 30%" />
            <div>
              <div class="text-h6 mb-n2">OS</div>
              <small
                ><span class="primary--text font-weight-bold">80%</span> Using
                Win 10</small
              >
            </div>
          </v-row>
        </v-col>
        <v-col cols="4">
          <v-row>
            <div ref="BrowserChart" style="height: 50px; width: 30%" />
            <div>
              <div class="text-h6 mb-n2">Browser</div>
              <small
                ><span class="primary--text font-weight-bold">75%</span> Using
                Chrome</small
              >
            </div>
          </v-row>
        </v-col>
      </v-row>
      <div class="mx-9 mb-4 caption black--text">
        Values are indicative of the past hour. However, the chart below shows
        realtime data trickling in per second.
      </div>

      <div ref="MetricsChart" class="chart" style="height: 350px" />
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
    this.Metrics(this.datepicker.date);
  },
  watch: {
    "datepicker.date"(v) {
      this.Metrics(v);
    },
  },
  methods: {
    Metrics(date) {
      this.progress = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Dashboard/Metrics/", {
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
              let metrics = JSON.parse(res);
              this.MetricsChart(metrics.activity);
              this.MiniCharts(metrics.device, "DeviceChart");
              this.MiniCharts(metrics.OS, "OSchart");
              this.MiniCharts(metrics.browser, "BrowserChart");
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
    MetricsChart(data) {
      let chart = am4core.create(this.$refs.MetricsChart, am4charts.XYChart);
      chart.logo.disabled = true;
      chart.fontSize = 13;
      //chart.colors.step = 4;
      //chart.data = data;

      chart.exporting.menu = new am4core.ExportMenu();
      chart.exporting.menu.align = "left";
      chart.exporting.menu.verticalAlign = "top";
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

      chart.data = data;

      // Create axes
      var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.renderer.inside = true;
      categoryAxis.dataFields.category = "time";
      categoryAxis.renderer.grid.template.disabled = true;
      //categoryAxis.renderer.minGridDistance = 100; // Will separate labels on axis
      /* categoryAxis.startLocation = 0.5;
      categoryAxis.endLocation = 0.5;
      categoryAxis.renderer.minLabelPosition = 0.05;
      categoryAxis.renderer.maxLabelPosition = 0.95; */

      var categoryAxisTooltip = categoryAxis.tooltip.background;
      categoryAxisTooltip.pointerLength = 0;
      categoryAxisTooltip.fillOpacity = 0.3;
      categoryAxisTooltip.filters.push(new am4core.BlurFilter()).blur = 5;
      categoryAxis.tooltip.dy = 5;

      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.inside = true;
      valueAxis.renderer.grid.template.disabled = true;
      valueAxis.renderer.minLabelPosition = 0.05;
      valueAxis.renderer.maxLabelPosition = 0.95;

      var valueAxisTooltip = valueAxis.tooltip.background;
      valueAxisTooltip.pointerLength = 0;
      valueAxisTooltip.fillOpacity = 0.3;
      valueAxisTooltip.filters.push(new am4core.BlurFilter()).blur = 5;

      // Create series
      var series1 = chart.series.push(new am4charts.LineSeries());
      series1.dataFields.categoryX = "time";
      series1.dataFields.valueY = "Home";
      series1.fillOpacity = 0.5;
      series1.fill = "red";
      series1.stacked = true;
      series1.tooltipText = "[font-size: 13]Home page: {valueY}[/]";
      series1.tooltip.pointerOrientation = "vertical";
      series1.tooltip.label.textAlign = "middle";

      var blur1 = new am4core.BlurFilter();
      blur1.blur = 20;
      series1.filters.push(blur1);

      var series2 = chart.series.push(new am4charts.LineSeries());
      series2.dataFields.categoryX = "time";
      series2.dataFields.valueY = "APIs";
      series2.fillOpacity = 1;
      series2.stacked = true;

      var blur2 = new am4core.BlurFilter();
      blur2.blur = 20;
      series2.filters.push(blur2);

      var series3 = chart.series.push(new am4charts.LineSeries());
      series3.dataFields.categoryX = "time";
      series3.dataFields.valueY = "count";
      series3.stroke = am4core.color("#fff");
      series3.strokeWidth = 2;
      //series3.strokeDasharray = "3,3";
      series3.tooltipText = "[font-size: 13]Cumulative: {valueY}[/]";
      series3.tooltip.pointerOrientation = "vertical";
      series3.tooltip.label.textAlign = "middle";
      series3.tensionX = 0.7;

      var bullet3 = series3.bullets.push(new am4charts.CircleBullet());
      bullet3.circle.radius = 3;
      bullet3.fill = chart.colors.getIndex(3);
      bullet3.stroke = am4core.color("#fff");
      bullet3.strokeWidth = 1;

      var bullet3hover = bullet3.states.create("hover");
      bullet3hover.properties.scale = 1.2;

      var shadow3 = new am4core.DropShadowFilter();
      series3.filters.push(shadow3);

      chart.cursor = new am4charts.XYCursor();
      chart.cursor.lineX.disabled = true;
      chart.cursor.lineY.disabled = true;
    },
    MiniCharts(data, ref) {
      var chart = am4core.create(this.$refs[ref], am4charts.PieChart);
      chart.logo.disabled = true;
      // Add and configure Series
      var pieSeries = chart.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "entity";
      // Let's cut a hole in our Pie chart the size of 30% the radius
      chart.innerRadius = am4core.percent(50);
      // Put a thick white border around each Slice
      pieSeries.slices.template.stroke = am4core.color("#fff");
      pieSeries.slices.template.strokeWidth = 2;
      pieSeries.slices.template.strokeOpacity = 1;
      // change the cursor on hover to make it apparent the object can be interacted with
      pieSeries.slices.template.cursorOverStyle = [
        {
          property: "cursor",
          value: "pointer",
        },
      ];
      pieSeries.labels.template.disabled = true;
      pieSeries.ticks.template.disabled = true;
      // Create a base filter effect (as if it's not there) for the hover to return to
      var shadow = pieSeries.slices.template.filters.push(
        new am4core.DropShadowFilter()
      );
      shadow.opacity = 0;
      // Create hover state
      var hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists
      // Slightly shift the shadow and make it more prominent on hover
      var hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter());
      hoverShadow.opacity = 0.3;
      hoverShadow.blur = 5;
      // Assign data
      chart.data = data;
    },
  },
};
</script>

<style>
.MetricsCard {
  background: linear-gradient(45deg, rgb(255 149 98), rgb(235 255 174));
}
/* .amcharts-amexport-menu {
  margin: 5px 5px 0 0 !important;
} */
.amcharts-amexport-item {
  border: 2px solid #777;
  text-align: center;
  width: 100% !important;
  padding: 3px 5px 0 5px !important;
  _background: green !important;
}
.amcharts-amexport-label {
  font-size: 13px;
  padding: 5px;
  color: black !important;
}
.amcharts-amexport-top .amcharts-amexport-item > .amcharts-amexport-menu {
  top: -30px !important;
  left: -30px !important;
}
</style>
