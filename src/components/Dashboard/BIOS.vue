<template>
  <v-card class="elevation-1">
    <v-progress-linear v-if="progress" indeterminate />
    <v-card flat height="170" tile class="BIOScard elevation-5">
      <v-row>
        <v-col cols="12" align="center" class="mb-3">
          <v-img
            :lazy-src="$store.getters.server + 'img/redhat.png/'"
            max-height="60"
            max-width="60"
            :src="$store.getters.server + 'img/redhat.png'"
          />
          <div class="text-h6">{{ BIOS["distro"] }}</div>
          <div class="text-body-2">{{ BIOS["version"] }}</div>
          <div
            ref="NETchart"
            style="
              height: 50px;
              position: absolute;
              margin-top: -4px;
              width: 100%;
            "
          />
        </v-col>
      </v-row>
    </v-card>
    <v-card tile color="#f1f1f1">
      <v-row>
        <v-col align="center">
          <div class="text-caption font-weight-bold">PACKETS IN</div>
          <div class="text-body-2 font-weight-black green--text">
            <number
              :from="0"
              :to="PacketsIn"
              :duration="1"
              :format="FormatToDecimal"
              :delay="0"
              class="ml-1"
            />
            Pkts/s
          </div>
        </v-col>
        <v-col align="center">
          <div class="text-caption font-weight-bold">PACKETS OUT</div>
          <div class="text-body-2 font-weight-black red--text">
            <number
              :from="0"
              :to="PacketsOut"
              :duration="1"
              :format="FormatToDecimal"
              :delay="0"
              class="ml-1"
            />
            Pkts/s
          </div>
        </v-col>
        <v-col align="center">
          <div class="text-caption font-weight-bold">BYTES IN</div>
          <div class="text-body-2 font-weight-black green--text">
            <number
              :from="0"
              :to="BytesIn"
              :duration="1"
              :format="FormatToDecimal"
              :delay="0"
              class="ml-1"
            />
            KB/s
          </div>
        </v-col>
        <v-col align="center">
          <div class="text-caption font-weight-bold">BYTES OUT</div>
          <div class="text-body-2 font-weight-black red--text">
            <number
              :from="0"
              :to="BytesOut"
              :duration="1"
              :format="FormatToDecimal"
              :delay="0"
              class="ml-1"
            />
            KB/s
          </div>
        </v-col>
      </v-row>
    </v-card>
    <v-data-table
      :headers="headers"
      :items="dataset"
      dense
      hide-default-header
      hide-default-footer
    >
      <template v-slot:item.key="{ item }"
        ><span class="font-weight-bold text-caption">{{ item.key }}</span>
      </template>
      <template v-slot:item.value="{ item }"
        ><span class="text-caption">{{ item.value }}</span>
      </template>
    </v-data-table>
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
      progress: false,
      BIOS: [],
      headers: [
        { text: "Key", value: "key" },
        { text: "Value", value: "value" },
      ],
      dataset: [],
      PacketsIn: 0,
      PacketsOut: 0,
      BytesIn: 0,
      BytesOut: 0,
      chart: null,
    };
  },
  created() {
    this.GetBIOS();
    setInterval(() => {
      this.GetBIOS();
    }, 60000);
  },
  mounted() {
    this.chart = am4core.create(this.$refs.NETchart, am4charts.XYChart);
    this.chart.logo.disabled = true;
    this.chart.zoomOutButton.disabled = true;
    this.chart.padding(0, -5, 0, -5);
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
    series.dataFields.valueY = "received";
    series.tooltipText = "Rec. pkts/s: [bold]{valueY}[/]";
    series.fillOpacity = 1;
    series.fill = "green";
    series.strokeWidth = 0;
    series.showOnInit = false;

    let series1 = this.chart.series.push(new am4charts.LineSeries());
    series1.dataFields.categoryX = "time";
    series1.dataFields.valueY = "transmitted";
    series1.tooltipText = "Trans. pkts/s: [bold]{valueY}[/]";
    series1.fillOpacity = 1;
    series1.fill = "red";
    series1.strokeWidth = 0;
    series1.showOnInit = false;

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

    //categoryAxis.renderer.grid.template.disabled = true;
    categoryAxis.renderer.labels.template.disabled = true;
    //valueAxis.renderer.grid.template.disabled = true;
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
    GetBIOS() {
      this.progress = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Dashboard/BIOS/", {
          method: "post",
          body: JSON.stringify({
            token: "",
          }),
        })
      )
        .then((response) => {
          if (response.status > 200) {
            this.$refs.Snackbar.Snackify({
              visible: true,
              content:
                "We were unable to retrieve some information. This might affect your application. Kindly refresh to retry.",
              color: "error",
              timeout: 10000,
            });
          } else {
            response.text().then((res) => {
              this.BIOS = JSON.parse(res);
              this.$emit(
                "EmmittedByBIOS",
                this.$options._componentTag,
                this.BIOS
              );

              let hidden = [
                "distro",
                "version",
                "Operating System",
                "Kernel version",
                "CPU model",
                "CPU cores",
                "CPU frequency",
              ];
              this.dataset = [];
              Object.entries(JSON.parse(res)).map((el) => {
                if (!hidden.includes(el[0])) {
                  this.dataset.push({
                    key: el[0],
                    value: el[1],
                  });
                }
              });
            });
          }
        })
        .catch(() => {})
        .finally(() => {
          this.progress = false;
        });
    },
    SetData(NET) {
      let data = [];
      NET.map((obj) => {
        data.push({
          time: obj.timestamp.time,
          received: obj.network["net-dev"][0].rxpck,
          transmitted: obj.network["net-dev"][0].txpck,
        });
      });

      (this.PacketsIn = NET[NET.length - 1].network["net-dev"][0].rxpck),
        (this.PacketsOut = NET[NET.length - 1].network["net-dev"][0].txpck),
        (this.BytesIn = NET[NET.length - 1].network["net-dev"][0].rxkB),
        (this.BytesOut = NET[NET.length - 1].network["net-dev"][0].txkB),
        this.chart.addData(data, 1);
    },
  },
  beforeUnmount() {
    this.chart.dispose();
  },
};
</script>

<style>
.BIOScard {
  background: linear-gradient(45deg, rgb(234 226 226), rgb(253 152 142));
}
</style>
