<template>
  <v-card dark height="180" class="elevation-10 DevelopersCard">
    <div class="px-5 py-3">
      <v-row no-gutters>
        <v-col cols="6" class="text-caption font-weight-bold pt-1"
          >DEVELOPERS</v-col
        >
        <v-col cols="6" align="end">
          <v-btn x-small fab text
            ><v-icon style="z-index: 1" size="20">mdi-refresh</v-icon></v-btn
          ></v-col
        >
      </v-row>
      <div class="text-h4 font-weight-thin mt-n1 mb-2">
        <number
          ref="number1"
          :from="0"
          :to="developers.count"
          :duration="2"
          :delay="2"
          easing="Power1.easeOut"
        /><span class="caption ml-1">
          12%<v-icon class="mx-1 mt-n1" color="#ffaca6"
            >mdi-progress-download</v-icon
          >from last month</span
        >
      </div>
      <div>
        <v-avatar v-for="i in 3" :key="i" size="40" class="GroupedAvatars">
          <img :src="'https://picsum.photos/7' + i" />
        </v-avatar>
        <span class="text-body-2 yellow--text ml-3">+3 new this month.</span>
      </div>
    </div>
    <div
      ref="DevelopersChart"
      style="height: 183px; position: absolute; margin-top: -143px; width: 100%"
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
      developers: {
        count: 2340,
      },
    };
  },
  created() {
    this.GetDevelopers();
  },
  mounted() {
    this.DevelopersChart();
  },
  methods: {
    DevelopersChart() {
      let chart = am4core.create(this.$refs.DevelopersChart, am4charts.XYChart);
      chart.logo.disabled = true;
      chart.padding(125, -5, 0, -5);
      chart.opacity = 1;

      chart.data = [
        {
          month: "Jan",
          developers: 23,
        },
        {
          month: "Feb",
          developers: 26,
        },
        {
          month: "Mar",
          developers: 30,
        },
        {
          month: "Apr",
          developers: 20,
          lineColor: chart.colors.next(),
        },
        {
          month: "May",
          developers: 60,
        },
        {
          month: "Jun",
          developers: 53,
        },
        {
          month: "Jul",
          developers: 75,
        },
        {
          month: "Aug",
          developers: 89,
          lineColor: chart.colors.next(),
        },
        {
          month: "Sep",
          developers: 24,
        },
        {
          month: "Oct",
          developers: 14,
        },
        {
          month: "Nov",
          developers: 37,
        },
        {
          month: "Dec",
          developers: 75,
        },
      ];

      let categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.renderer.grid.template.location = 1;
      categoryAxis.renderer.ticks.template.disabled = true;
      categoryAxis.renderer.line.opacity = 0;
      categoryAxis.renderer.grid.template.disabled = true;
      categoryAxis.renderer.minGridDistance = 40;
      categoryAxis.dataFields.category = "month";
      categoryAxis.startLocation = 0.4;
      categoryAxis.endLocation = 0.6;

      let valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.tooltip.disabled = true;
      valueAxis.renderer.line.opacity = 0;
      valueAxis.renderer.ticks.template.disabled = true;
      valueAxis.min = 0;

      let lineSeries = chart.series.push(new am4charts.LineSeries());
      lineSeries.dataFields.categoryX = "month";
      lineSeries.dataFields.valueY = "developers";
      lineSeries.tooltipText = "Developers: {valueY.value}";
      lineSeries.fillOpacity = 0.5;
      lineSeries.strokeOpacity = 1;
      lineSeries.strokeWidth = 1;
      lineSeries.propertyFields.stroke = "lineColor";
      lineSeries.propertyFields.fill = "lineColor";
      lineSeries.tensionX = 0.8;

      let bullet = lineSeries.bullets.push(new am4charts.CircleBullet());
      bullet.circle.radius = 2;
      bullet.circle.fill = am4core.color("#fff");
      bullet.circle.strokeWidth = 1;

      chart.cursor = new am4charts.XYCursor();
      chart.cursor.behavior = "none";

      categoryAxis.renderer.grid.template.disabled = true;
      categoryAxis.renderer.labels.template.disabled = true;
      valueAxis.renderer.grid.template.disabled = true;
      valueAxis.renderer.labels.template.disabled = true;
    },
    GetDevelopers() {
      //this.$Progress.start();
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "Dashboard/GetDevelopers/", {
          method: "post",
          body: JSON.stringify({
            operation: "datarize",
          }),
        })
      )
        .then((response) => {
          if (response.status > 200) {
            //this.$Progress.fail();
            this.$refs.Snackbar.Snackify({
              visible: true,
              content:
                "We were unable to retrieve some information. This might affect your application. Kindly refresh to retry.",
              color: "error",
              timeout: 10000,
            });
          } else {
            response.text().then((res) => {
              console.log(res);
              //this.developers.count = JSON.parse(res).count;
            });
          }
        })
        .catch(() => {
          //this.$Progress.fail();
          this.$refs.Alert.Alertify({
            visible: true,
            content: this.$store.getters.fetchTimeoutError,
            title: "Connection Timeout",
            icon: "mdi-wifi-strength-1-alert",
            color: "error",
          });
        })
        .finally(() => {
          //this.$Progress.finish();
        });
    },
  },
};
</script>

<style>
.DevelopersCard {
  background: linear-gradient(45deg, rgb(29 95 107), rgb(72, 198, 67));
}
</style>
