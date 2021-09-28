<template>
  <v-card dark height="250" class="elevation-5 CPUcard">
    <div class="px-5 py-3">
      <v-row no-gutters>
        <v-col cols="6" class="text-caption font-weight-bold pt-1">CPU</v-col>
        <v-col cols="6" align="end">
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
      <div class="text-h4 font-weight-bold mt-n1 mb-2">
        <number
          ref="number1"
          :from="0"
          :to="usage"
          :duration="1"
          :format="FormatToDecimal"
          :delay="0"
          easing="Power1.easeOut"
        />%
        <span class="text-caption">usage from {{ CPU.cores }}</span>
        <div class="text-caption ml-1 mt-1 yellow--text">
          {{ CPU.model }}
        </div>
        <v-chip label small color="lime darken-3" class="ml-1">{{
          OneMinuteLoad
        }}</v-chip
        ><v-chip label small color="cyan" class="ml-3">{{
          FiveMinutesLoad
        }}</v-chip
        ><v-chip label small color="green" class="ml-3">{{
          FifteenMinutesLoad
        }}</v-chip>
        <div class="text-caption mt-2">
          <span class="ml-2 font-weight-bold">User:</span
          ><number
            :from="0"
            :to="usr"
            :duration="1"
            :format="FormatToDecimal"
            :delay="0"
            easing="Power1.easeIn"
            class="ml-1 lime--text text--accent-1"
          />% ,<span class="ml-2 font-weight-bold">System:</span>
          <number
            :from="0"
            :to="sys"
            :duration="1"
            :format="FormatToDecimal"
            :delay="0"
            easing="Power1.easeIn"
            class="ml-1 lime--text text--accent-1"
          />% ,<span class="ml-2 font-weight-bold">Idle:</span>
          <number
            :from="0"
            :to="idle"
            :duration="1"
            :format="FormatToDecimal"
            :delay="0"
            easing="Power1.easeIn"
            class="ml-1 lime--text text--accent-1"
          />%
        </div>
      </div>
    </div>
    <div
      ref="CPUchart"
      style="height: 250px; position: absolute; margin-top: -192px; width: 100%"
    />
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
      model: null,
      usage: 0,
      idle: 0,
      usr: 0,
      sys: 0,
      CPU: {
        model: null,
        cores: null,
      },
      OneMinuteLoad: null,
      FiveMinutesLoad: null,
      FifteenMinutesLoad: null,
      data: [],
      action: {
        interval: 0,
        icon: "mdi-pause",
        name: "Pause",
      },
      chart: null,
    };
  },
  mounted() {
    this.chart = am4core.create(this.$refs.CPUchart, am4charts.XYChart);
    this.chart.logo.disabled = true;
    this.chart.zoomOutButton.disabled = true;
    this.chart.padding(180, -3, 0, -3);
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
    series.dataFields.valueY = "load";
    series.tooltipText = "Load: [bold]{valueY}[/]";
    /* series.fillOpacity = 0.6;
    series.fill = "orange"; */
    series.strokeWidth = 0;
    series.showOnInit = false;

    series.fillOpacity = 1;
    var gradient = new am4core.LinearGradient();
    gradient.addColor(am4core.color("yellow"), 0.5);
    gradient.addColor(am4core.color("orange"), 0.5);
    series.fill = gradient;

    let axisTooltip = categoryAxis.tooltip;
    axisTooltip.label.fontSize = 13;
    axisTooltip.background.fill = am4core.color("#07BEB8");
    axisTooltip.background.strokeWidth = 0;
    axisTooltip.background.cornerRadius = 3;
    axisTooltip.background.pointerLength = 0;
    axisTooltip.dy = -13;

    let axisTooltip2 = valueAxis.tooltip;
    axisTooltip2.label.fontSize = 13;
    axisTooltip2.zIndex = 20000000;
    axisTooltip2.background.fill = am4core.color("#07BEB8");
    axisTooltip2.background.strokeWidth = 0;
    axisTooltip2.background.cornerRadius = 3;
    axisTooltip2.background.pointerLength = 0;
    //axisTooltip2.dx = -20;

    this.chart.cursor = new am4charts.XYCursor();

    categoryAxis.renderer.grid.template.disabled = true;
    categoryAxis.renderer.labels.template.disabled = true;
    valueAxis.renderer.grid.template.disabled = true;
    valueAxis.renderer.labels.template.disabled = true;

    var bullet = series.createChild(am4charts.CircleBullet);
    bullet.circle.radius = 5;
    bullet.fillOpacity = 1;
    bullet.fill = this.chart.colors.getIndex(1);
    bullet.isMeasured = false;

    series.events.on("validated", function () {
      bullet.moveTo(series.dataItems.last.point);
      bullet.validatePosition();
    });
  },
  methods: {
    SetData(CPU) {
      let data = [];
      CPU.map((obj) => {
        data.push({
          time: obj.timestamp.time,
          load: obj["cpu-load-all"][0].sys + obj["cpu-load-all"][0].usr,
        });
      });
      this.usage =
        CPU[CPU.length - 1]["cpu-load-all"][0].sys +
        CPU[CPU.length - 1]["cpu-load-all"][0].usr;
      this.idle = CPU[CPU.length - 1]["cpu-load-all"][0].idle;
      this.usr = CPU[CPU.length - 1]["cpu-load-all"][0].usr;
      this.sys = CPU[CPU.length - 1]["cpu-load-all"][0].sys;
      this.OneMinuteLoad = CPU[CPU.length - 1].queue["ldavg-1"];
      this.FiveMinutesLoad = CPU[CPU.length - 1].queue["ldavg-5"];
      this.FifteenMinutesLoad = CPU[CPU.length - 1].queue["ldavg-15"];

      this.chart.addData(data, 1);
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
  beforeUnmount() {
    this.chart.dispose();
  },
};
</script>

<style>
.CPUcard {
  background: linear-gradient(45deg, rgb(87 15 152), rgb(148 130 39));
}
</style>
