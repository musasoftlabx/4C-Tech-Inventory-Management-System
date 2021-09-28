<template>
  <v-card dark height="250" class="elevation-5 RAMcard">
    <div class="px-5 py-3">
      <v-row no-gutters>
        <v-col cols="6" class="text-caption font-weight-bold pt-1">RAM</v-col>
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
        <span class="text-caption">used</span>
      </div>
      <v-simple-table
        dense
        style="background: transparent"
        class="CustomVerticalTable mt-3"
      >
        <template v-slot:default>
          <thead>
            <tr>
              <th class="text-left">Used Mem.</th>
              <th class="text-left">Free Mem.</th>
              <th class="text-left">Cache Mem.</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ BytesToSize(used) }}</td>
              <td>{{ BytesToSize(free) }}</td>
              <td>{{ BytesToSize(cached) }}</td>
            </tr>
          </tbody>
        </template>
      </v-simple-table>
      <div class="mt-3">
        <v-icon class="mr-2 mt-n1" color="#b0ffb3">mdi-memory</v-icon
        ><span class="text-caption font-weight-bold"
          >Buffers: {{ BytesToSize(buffers) }}</span
        >
      </div>
    </div>
    <div
      ref="RAMchart"
      style="height: 250px; position: absolute; margin-top: -204px; width: 100%"
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
      usage: 0,
      free: 0,
      used: 0,
      cached: 0,
      buffers: 0,
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
    this.chart = am4core.create(this.$refs.RAMchart, am4charts.XYChart);
    this.chart.logo.disabled = true;
    this.chart.zoomOutButton.disabled = true;
    this.chart.padding(200, -3, 0, -3);
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
    series.tooltipText = "Perc: [bold]{valueY}[/]";
    series.strokeFill = "orange";
    series.strokeWidth = 0;
    series.showOnInit = false;
    series.fillOpacity = 1;
    var gradient = new am4core.LinearGradient();
    gradient.addColor(am4core.color("yellow"), 0.5);
    gradient.addColor(am4core.color("#C6FF00"), 0.9);
    series.fill = gradient;

    let axisTooltip = categoryAxis.tooltip;
    axisTooltip.label.fontSize = 13;
    axisTooltip.background.fill = am4core.color("#07BEB8");
    axisTooltip.background.strokeWidth = 0;
    axisTooltip.background.cornerRadius = 3;
    axisTooltip.background.pointerLength = 0;
    axisTooltip.dy = -12;

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
  },
  methods: {
    SetData(RAM) {
      let data = [];
      RAM.map((obj) => {
        data.push({
          time: obj.timestamp.time,
          load: obj.memory["memused-percent"],
        });
      });
      this.usage = RAM[RAM.length - 1].memory["memused-percent"];
      this.free = RAM[RAM.length - 1].memory.memfree;
      this.used = RAM[RAM.length - 1].memory.memused;
      this.cached = RAM[RAM.length - 1].memory.cached;
      this.buffers = RAM[RAM.length - 1].memory.buffers;

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
.RAMcard {
  background: linear-gradient(45deg, rgb(3 76 9), rgb(188 118 241));
}
</style>
