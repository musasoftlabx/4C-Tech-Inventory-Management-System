<template>
  <v-card dark height="250" class="elevation-5 HDDcard">
    <div class="px-5 py-3">
      <v-row no-gutters>
        <v-col cols="6" class="text-caption font-weight-bold pt-1">HDD</v-col>
        <v-col cols="6" align="end">
          <span class="text-caption font-weight-bold yellow--text"
            ><number
              :from="0"
              :to="TPS"
              :duration="1"
              :format="FormatToDecimal"
              :delay="0"
              class="ml-1"
            />
            Trfs/s</span
          >
          <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
              <v-btn @click="Action" x-small fab text v-bind="attrs" v-on="on"
                ><v-icon style="z-index: 1" size="20">{{
                  action.icon
                }}</v-icon></v-btn
              >
            </template>
            <span>{{ action.name }}</span>
          </v-tooltip></v-col
        >
      </v-row>
      <v-row no-gutters>
        <v-col>
          <div class="text-h4 font-weight-bold mt-n1 mb-2">
            {{ BytesToSize(used) }}
            <span class="text-caption"></span>
          </div>
          <div class="mt-3">
            <div class="text-caption font-italic mt-3">Total Disk Space</div>
            <div class="text-subtitle-2">{{ BytesToSize(total) }}</div>
            <div class="text-caption font-italic">Free Disk Space</div>
            <span class="text-subtitle-2">{{ BytesToSize(free) }}</span>
          </div></v-col
        >
        <v-col align="end" style="z-index: 1">
          <div ref="HDDchart" style="height: 140px" />
        </v-col>
      </v-row>
    </div>
    <div
      ref="HDDRWchart"
      style="height: 250px; position: absolute; margin-top: -196px; width: 100%"
    ></div>
  </v-card>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";
am4core.useTheme(am4themes_animated);

export default {
  data() {
    return {
      TPS: 0,
      used: 0,
      total: 0,
      free: 0,
      data: [],
      action: {
        interval: 0,
        icon: "mdi-pause",
        name: "Pause",
      },
      chart: null,
      cylinder: null,
    };
  },
  mounted() {
    this.chart = am4core.create(this.$refs.HDDRWchart, am4charts.XYChart);
    this.chart.logo.disabled = true;
    this.chart.zoomOutButton.disabled = true;
    this.chart.padding(190, -5, 0, -5);
    this.chart.hiddenState.properties.opacity = 0;

    function AddZero(i) {
      if (i < 10) {
        i = "0" + i;
      }
      return i;
    }
    var d = new Date();
    var h = AddZero(d.getHours() - 1);
    var m = AddZero(d.getMinutes());

    let load = 0;
    for (let i = 0; i <= 50; i++) {
      load = Math.floor(Math.random() * 51);
      this.chart.data.push({ time: h + ":" + m + ":" + i, load });
    }

    let categoryAxis = this.chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "time";

    let valueAxis = this.chart.yAxes.push(new am4charts.ValueAxis());

    let series = this.chart.series.push(new am4charts.LineSeries());
    series.dataFields.categoryX = "time";
    series.dataFields.valueY = "reads";
    series.yAxis = valueAxis;
    series.tooltipText = "Reads: [bold]{valueY}[/]";
    series.stroke = "yellow";
    series.strokeWidth = 1;
    series.showOnInit = false;

    let series1 = this.chart.series.push(new am4charts.LineSeries());
    series1.dataFields.categoryX = "time";
    series1.dataFields.valueY = "writes";
    series1.tooltipText = "Writes: [bold]{valueY}[/]";
    series1.stroke = "pink";
    series1.strokeWidth = 1;
    series1.showOnInit = false;

    let bullet1 = series1.bullets.push(new am4charts.CircleBullet());
    bullet1.circle.radius = 2;
    bullet1.circle.fill = am4core.color("#fff");
    bullet1.circle.strokeWidth = 1;

    let axisTooltip = categoryAxis.tooltip;
    axisTooltip.label.fontSize = 13;
    axisTooltip.background.fill = am4core.color("#07BEB8");
    axisTooltip.background.strokeWidth = 0;
    axisTooltip.background.cornerRadius = 3;
    axisTooltip.background.pointerLength = 0;
    axisTooltip.dy = -20;

    let axisTooltip2 = valueAxis.tooltip;
    axisTooltip2.label.fontSize = 13;
    axisTooltip2.zIndex = 20000000;
    axisTooltip2.background.fill = am4core.color("#07BEB8");
    axisTooltip2.background.strokeWidth = 0;
    axisTooltip2.background.cornerRadius = 3;
    axisTooltip2.background.pointerLength = 0;
    //axisTooltip2.dx = -20;

    this.chart.cursor = new am4charts.XYCursor();

    categoryAxis.renderer.labels.template.disabled = true;
    valueAxis.renderer.labels.template.disabled = true;
    categoryAxis.renderer.grid.template.stroke = "#fff";
    categoryAxis.renderer.grid.template.opacity = 1;
    valueAxis.renderer.grid.template.opacity = 1;
    valueAxis.renderer.grid.template.stroke = "#fff";

    /**** HDD **** */
    this.cylinder = am4core.create(this.$refs.HDDchart, am4charts.XYChart3D);
    this.cylinder.logo.disabled = true;
    this.cylinder.padding(0, -30, 0, 0);
    this.cylinder.fontSize = 13;
    this.cylinder.hiddenState.properties.opacity = 1;

    this.cylinder.cursor = new am4charts.XYCursor();
    this.cylinder.cursor.xAxis = categoryAxis;

    // Create axes
    let categoryAxisHDD = this.cylinder.xAxes.push(
      new am4charts.CategoryAxis()
    );
    categoryAxisHDD.dataFields.category = "category";
    categoryAxisHDD.renderer.grid.template.location = 0;
    categoryAxisHDD.renderer.grid.template.strokeOpacity = 0;

    let valueAxisHDD = this.cylinder.yAxes.push(new am4charts.ValueAxis());
    valueAxisHDD.renderer.grid.template.strokeOpacity = 0;
    valueAxisHDD.min = -40;
    valueAxisHDD.max = 140;
    valueAxisHDD.strictMinMax = true;
    valueAxisHDD.renderer.baseGrid.disabled = true;
    valueAxisHDD.renderer.labels.template.adapter.add("text", (text) => {
      if (text > 100 || text < 0) {
        return "";
      } else {
        return text + "%";
      }
    });

    // Create series
    var series1HDD = this.cylinder.series.push(new am4charts.ConeSeries());
    series1HDD.dataFields.valueY = "value1";
    series1HDD.dataFields.categoryX = "category";
    series1HDD.columns.template.width = am4core.percent(80);
    series1HDD.columns.template.fillOpacity = 0.9;
    series1HDD.columns.template.strokeOpacity = 1;
    series1HDD.columns.template.strokeWidth = 2;
    series1HDD.showOnInit = false;

    var series2HDD = this.cylinder.series.push(new am4charts.ConeSeries());
    series2HDD.dataFields.valueY = "value2";
    series2HDD.dataFields.categoryX = "category";
    series2HDD.stacked = true;
    series2HDD.columns.template.width = am4core.percent(80);
    series2HDD.columns.template.fill = am4core.color("#000");
    series2HDD.columns.template.fillOpacity = 0.1;
    series2HDD.columns.template.stroke = am4core.color("#000");
    series2HDD.columns.template.strokeOpacity = 0.2;
    series2HDD.columns.template.strokeWidth = 2;
    series2HDD.showOnInit = false;

    let axisTooltipHDD = categoryAxisHDD.tooltip;
    axisTooltipHDD.label.fontSize = 13;
    axisTooltipHDD.label.fill = am4core.color("#000");
    axisTooltipHDD.background.fill = am4core.color("#C6FF00");
    axisTooltipHDD.background.strokeWidth = 0;
    axisTooltipHDD.background.cornerRadius = 3;
    axisTooltipHDD.background.pointerLength = 0;

    let axisTooltip2HDD = valueAxisHDD.tooltip;
    axisTooltip2HDD.label.fontSize = 13;
    axisTooltip2HDD.label.fill = am4core.color("#000");
    axisTooltip2HDD.zIndex = 20000000;
    axisTooltip2HDD.background.fill = am4core.color("#C6FF00");
    axisTooltip2HDD.background.strokeWidth = 0;
    axisTooltip2HDD.background.cornerRadius = 3;
    axisTooltip2HDD.background.pointerLength = 0;

    categoryAxisHDD.renderer.grid.template.disabled = true;
    categoryAxisHDD.renderer.labels.template.disabled = true;
    valueAxisHDD.renderer.grid.template.disabled = true;
    valueAxisHDD.renderer.labels.template.disabled = true;
  },
  methods: {
    SetData(HDD) {
      let data = [];
      HDD.map((obj) => {
        data.push({
          time: obj.timestamp.time,
          transfers: obj.disk[1]["tps"],
          reads: obj.disk[1]["rd_sec"],
          writes: obj.disk[1]["wr_sec"],
        });
      });
      this.TPS = HDD[HDD.length - 1].disk[1]["tps"];

      // Filter to get only from /var filesystem
      let FS = HDD[HDD.length - 1].filesystems.filter(
        (obj) => obj.filesystem === "/dev/mapper/rhel-var"
      );

      this.used = FS[0].MBfsused * 1000;
      this.free = FS[0].MBfsfree * 1000;
      this.total = this.used + this.free;

      let PercentageFree = FS[0]["%fsused"];
      let PercentageUsed = 100 - PercentageFree;

      this.chart.addData(data, 1);
      this.cylinder.data = [
        {
          category: "Disk usage",
          value1: PercentageFree,
          value2: PercentageUsed,
        },
      ];
    },
    GetStats() {
      let that = this;
      function UpdateChart() {
        that
          .promiseFetch(that.$store.getters.fetchTimeout)(
            fetch(that.$store.getters.endpoint + "Dashboard/SystemStats/", {
              method: "post",
              body: JSON.stringify({
                token: "",
                hardware: "HDD",
              }),
            })
          )
          .then((response) => {
            if (response.status > 200) {
              that.$refs.Snackbar.Snackify({
                visible: true,
                content:
                  "We were unable to retrieve some information. Kindly refresh to retry.",
                color: "error",
                timeout: 10000,
              });
            } else {
              response.text().then((res) => {
                let HDD = JSON.parse(res).sysstat.hosts[0].statistics;
                let data = [];
                HDD.map((obj) => {
                  data.push({
                    time: obj.timestamp.time,
                    transfers: obj.disk[1]["tps"],
                    reads: obj.disk[1]["rd_sec"],
                    writes: obj.disk[1]["wr_sec"],
                  });
                });
                that.TPS = HDD[HDD.length - 1].disk[1]["tps"];
                that.used = HDD[HDD.length - 1].filesystems[4].MBfsused * 1000;
                that.free = HDD[HDD.length - 1].filesystems[4].MBfsfree * 1000;
                that.total = that.used + that.free;

                let PercentageFree =
                  HDD[HDD.length - 1].filesystems[4]["%fsused"];
                let PercentageUsed = 100 - PercentageFree;
                that.GenerateChart(data);
                that.GenerateCylinder(PercentageFree, PercentageUsed);
              });
            }
          })
          .catch(() => {})
          .finally(() => {});
      }

      UpdateChart();
      /*  this.action.interval = setInterval(() => {
        UpdateChart();
      }, 30000); */
    },
    Action() {
      if (this.action.name === "Pause") {
        this.action.name = "Play";
        this.action.icon = "mdi-play";
        clearInterval(this.action.interval);
      } else {
        this.action.name = "Pause";
        this.action.icon = "mdi-pause";
        this.GetStats();
      }
    },
  },
};
</script>

<style>
.HDDcard {
  background: linear-gradient(45deg, rgb(125 128 114), rgb(4 90 7));
}
</style>
