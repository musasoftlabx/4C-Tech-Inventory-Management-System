<template>
  <v-main>
    <v-card tile flat>
      <!-- <Appbar /> -->
      <Drawer />

      <v-container fluid>
        <v-row>
          <v-col cols="12" md="8">
            <v-card flat color="transparent" class="py-8 rounded-xl">
              <v-row no-gutters>
                <v-col cols="12" md="6">
                  <div class="text-h4">
                    Welcome back <span class="teal--text">Musa</span>,
                  </div>
                  <div class="text-subtitle-2">
                    Hi User, here are some quick statistics for you.
                  </div>
                </v-col>
                <v-col
                  cols="12"
                  md="6"
                  align="end"
                  class="pr-10 hidden-md-and-down"
                >
                  <div class="text-body-1">Total Products,</div>
                  <div class="text-h4 orange--text text-undeline">
                    <number
                      ref="number1"
                      :from="0"
                      :to="products"
                      :duration="3"
                      :format="FormatWithCommas"
                      :delay="0"
                      easing="Power1.easeOut"
                    />
                  </div>
                </v-col>
              </v-row>
            </v-card>
            <v-row no-gutters>
              <v-col
                v-for="(entity, i) in entities"
                :key="i"
                cols="12"
                md="4"
                align="center"
              >
                <v-card
                  dark
                  flat
                  :width="$vuetify.breakpoint.mdAndDown ? '80%' : 250"
                  class="mb-6 pa-6 rounded-xl elevation-20 text-left"
                  :class="entity.class"
                >
                  <v-row no-gutters>
                    <v-col cols="6">
                      <v-img
                        height="50"
                        width="50"
                        :src="`${$store.getters.server}img/${entity.image}`"
                      />
                      <!-- <v-icon size="50">mdi-family-tree</v-icon> -->
                    </v-col>
                    <v-col cols="6" align="end">
                      <v-icon size="20">mdi-refresh</v-icon>
                    </v-col>
                  </v-row>

                  <div
                    style="
                      margin: 10px;
                      position: absolute;
                      background-color: #fff;
                      border-radius: 30px;
                      height: 100px;
                      top: 0;
                      left: 0;
                      width: 160px;
                      opacity: 0;
                    "
                  />
                  <div class="text-subtitle-2 mt-2">
                    {{ entity.name }}
                  </div>
                  <div class="text-h6 font-weight-thin mb-2">
                    <v-row no-gutters>
                      <v-col cols="7">
                        <number
                          ref="number1"
                          :from="0"
                          :to="entity.count"
                          :duration="3"
                          :format="FormatWithCommas"
                          :delay="0"
                          easing="Power1.easeOut"
                        />
                      </v-col>
                      <v-col cols="5" align="end">
                        <span class="text-caption yellow--text font-italic"
                          >as of now</span
                        >
                      </v-col>
                    </v-row>
                  </div>
                  <v-progress-linear
                    stream
                    buffer-value="50"
                    rounded
                    color="white"
                    value="15"
                  />
                </v-card>
              </v-col>
            </v-row>
            <v-row v-if="$vuetify.breakpoint.smAndUp">
              <v-col cols="12">
                <div class="text-h6 text-center font-weight-thin">
                  Stock control
                </div>
                <div class="text-subtitle-2 text-center">
                  We are currently keeping stock in the following branches
                </div>
                <div
                  id="chartdiv"
                  ref="WorldMap"
                  class="chart"
                  style="width: 100%"
                  :style="
                    $vuetify.breakpoint.mdAndDown
                      ? 'height: 300px'
                      : 'height: 500px'
                  "
                />
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="12" md="4" class="px-8">
            <v-row class="py-3">
              <v-col cols="10" align="end">
                <v-avatar color="primary" size="50">
                  <span class="headline white--text">M</span>
                </v-avatar>
              </v-col>
            </v-row>
            <v-card flat class="rounded-xl">
              <div class="text-h6">Statistics</div>
              <div class="text-subtitle-2 mb-5">Verification rate</div>
              <div id="VerificationRate" style="width: 100%" />
            </v-card>
            <div class="text-h6 my-1">Top verifiers</div>
            <div class="text-subtitle-2 mb-3">Leading in number of scans</div>
            <v-row :justify="$vuetify.breakpoint.mdAndDown ? 'center' : 'left'">
              <v-col v-for="i in 3" :key="i" cols="3" align="center">
                <img
                  width="60"
                  src="https://avataaars.io/?avatarStyle=Circle&topType=ShortHairTheCaesarSidePart&accessoriesType=Blank&hairColor=Platinum&facialHairType=BeardLight&facialHairColor=Black&clotheType=BlazerSweater&eyeType=WinkWacky&eyebrowType=Angry&mouthType=Tongue&skinColor=Yellow"
                />
                <div class="text-subtitle-2 text-center font-weight-thin">
                  Name
                </div>
                <div class="text-caption text-center">300 scans</div>
              </v-col>
            </v-row>

            <v-card flat max-width="500" class="pa-8 rounded-xl text-center">
              <div class="text-h6 font-weight-light">Total Discrepancies</div>
              <div class="text-subtitle-2">
                Something that needs your attention
              </div>
              <div id="DiscrepanciesChart" style="height: 300px" />
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Snackbar ref="Snackbar" />
    <Alert ref="Alert" />
  </v-main>
</template>

<script>
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";
//import am4themes_dark from "@amcharts/amcharts4/themes/dark";
import am4themes_animated from "@amcharts/amcharts4/themes/animated";
import * as am4maps from "@amcharts/amcharts4/maps";
import worldLow from "@amcharts/amcharts4-geodata/worldLow";
//import usaLow from "@amcharts/amcharts4-geodata/usaLow";
import kenyaLow from "@amcharts/amcharts4-geodata/kenyaLow";
//am4core.useTheme(am4themes_dark);
am4core.useTheme(am4themes_animated);

//import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";

export default {
  components: {
    //Appbar,
    Drawer,
    Snackbar,
  },

  data() {
    return {
      entities: [
        {
          name: "Stores",
          image: "store.png",
          count: 8880,
          class: "StoresCard",
        },
        {
          name: "Sections",
          image: "section.png",
          count: 8880,
          class: "SectionsCard",
        },
        {
          name: "Locations",
          image: "location.png",
          count: 8880,
          class: "LocationsCard",
        },
      ],
      products: 9369936,

      socket: null,
      developers: {
        total: 10000,
      },
      darkUI: false,
      loading: false,
      statistics: {
        subAgents: null,
        assistantSubAgents: null,
      },
      headers: [],
      dataset: [],
      itemsPerPage: 20,
      options: {},
      page: 1,
      pageCount: 0,
      offset: 0,
      selected: [],
      search: null,
      searchResults: null,

      profile: {
        messages: [
          {
            from: "You",
            message: `Sure, I'll see you later.`,
            time: "10:42am",
            color: "deep-purple lighten-1",
          },
          {
            from: "John Doe",
            message: "Yeah, sure. Does 1:00pm work?",
            time: "10:37am",
            color: "green",
          },
          {
            from: "You",
            message: "Did you still want to grab lunch today?",
            time: "9:47am",
            color: "deep-purple lighten-1",
          },
        ],
      },

      MessagesLoaded: false,
      LoadingRooms: true,
      start: null,
      end: null,
      currentUserId: 0,
      EmmitedByChildren: {
        BIOS: null,
      },

      map: {},
      pie: {},
      line: {},
    };
  },
  mounted() {
    this.$vuetify.breakpoint.smAndUp && this.WorldMap();
    this.DiscrepanciesChart();
    this.MiniLogsChart(
      "VerificationRate",
      "Monthly warnings",
      "orange",
      "value",
      [
        { date: new Date(2021, 0, 1, 8, 0, 0), value: 57 },
        { date: new Date(2021, 0, 1, 9, 0, 0), value: 27 },
        { date: new Date(2021, 0, 1, 10, 0, 0), value: 24 },
        { date: new Date(2021, 0, 1, 11, 0, 0), value: 59 },
        { date: new Date(2021, 0, 1, 12, 0, 0), value: 33 },
        { date: new Date(2021, 0, 1, 13, 0, 0), value: 46 },
        { date: new Date(2021, 0, 1, 14, 0, 0), value: 20 },
        { date: new Date(2021, 0, 1, 15, 0, 0), value: 42 },
        { date: new Date(2021, 0, 1, 16, 0, 0), value: 59, opacity: 1 },
      ]
    );

    /* this.AppsChart();
    this.APIhitsChart();
    this.ForumChart(); */
    //this.GetRooms();
  },
  methods: {
    WorldMap() {
      this.map = am4core.create(this.$refs.WorldMap, am4maps.MapChart);
      this.map.logo.disabled = true;
      this.map.padding(20, 0, 0, 0);
      this.map.fontSize = 10;

      /* this.map.titles.template.fontSize = 10;
      this.map.titles.template.textAlign = "left";
      this.map.titles.template.isMeasured = true;
      this.map.titles.create().text = "map"; */

      // Set map definition
      this.map.geodata = worldLow;

      // Set projection
      this.map.projection = new am4maps.projections.Miller();
      //chart.projection = new am4maps.projections.AlbersUsa();

      // Series for World map
      var worldSeries = this.map.series.push(new am4maps.MapPolygonSeries());
      worldSeries.exclude = ["AQ"];
      worldSeries.useGeodata = true;

      var polygonTemplate = worldSeries.mapPolygons.template;
      polygonTemplate.tooltipText = "{name}";
      polygonTemplate.fill = this.map.colors.getIndex(1);
      polygonTemplate.nonScalingStroke = true;

      // Hover state
      var hs = polygonTemplate.states.create("hover");
      hs.properties.fill = am4core.color("#367B25");

      // Series for United States map
      var usaSeries = this.map.series.push(new am4maps.MapPolygonSeries());
      usaSeries.geodata = kenyaLow;

      var usPolygonTemplate = usaSeries.mapPolygons.template;
      usPolygonTemplate.tooltipText = "{name}";
      usPolygonTemplate.fill = this.map.colors.getIndex(1);
      usPolygonTemplate.nonScalingStroke = true;

      // Hover state
      var hss = usPolygonTemplate.states.create("hover");
      hss.properties.fill = am4core.color("#367B25");

      // Create active state
      var activeState = polygonTemplate.states.create("active");
      activeState.properties.fill = this.map.colors.getIndex(4);

      // Create an event to toggle "active" state
      //var that = this;
      polygonTemplate.events.on("hit", function (ev) {
        //console.log(ev.target.tooltipText);
        /* that.$refs.Alert.Alertify({
          title: "Update Error!",
          content: "The last change you made could not be saved. Kindly retry.",
          visible: true,
          icon: "mdi-wifi-strength-1-alert",
          color: "error",
        }); */
        ev.target.isActive = !ev.target.isActive;
      });

      this.map.series.push(new am4maps.GraticuleSeries());

      // Small map
      this.map.smallMap = new am4maps.SmallMap();
      // Re-position to top right (it defaults to bottom left)
      this.map.smallMap.align = "right";
      this.map.smallMap.valign = "middle";
      this.map.smallMap.series.push(worldSeries);

      // Export
      this.map.exporting.menu = new am4core.ExportMenu();

      // Zoom control
      this.map.zoomControl = new am4maps.ZoomControl();

      var homeButton = new am4core.Button();
      homeButton.events.on("hit", function () {
        this.map.goHome();
      });

      homeButton.icon = new am4core.Sprite();
      homeButton.padding(7, 5, 7, 5);
      homeButton.width = 30;
      homeButton.icon.path =
        "M16,8 L14,8 L14,16 L10,16 L10,10 L6,10 L6,16 L2,16 L2,8 L0,8 L8,0 L16,8 Z M16,8";
      homeButton.marginBottom = 10;
      homeButton.parent = this.map.zoomControl;
      homeButton.insertBefore(this.map.zoomControl.plusButton);

      // Add image series
      var imageSeries = this.map.series.push(new am4maps.MapImageSeries());
      imageSeries.mapImages.template.propertyFields.longitude = "longitude";
      imageSeries.mapImages.template.propertyFields.latitude = "latitude";
      imageSeries.mapImages.template.tooltipText = "{title}";
      imageSeries.mapImages.template.propertyFields.url = "url";

      var circle = imageSeries.mapImages.template.createChild(am4core.Circle);
      circle.radius = 7;
      circle.propertyFields.fill = "color";
      circle.nonScaling = true;

      var circle2 = imageSeries.mapImages.template.createChild(am4core.Circle);
      circle2.radius = 7;
      circle2.propertyFields.fill = "color";

      circle2.events.on("inited", function (event) {
        animateBullet(event.target);
      });
      let that = this;
      function animateBullet(circle) {
        var animation = circle.animate(
          [
            {
              property: "scale",
              from: 1 / that.map.zoomLevel,
              to: 5 / that.map.zoomLevel,
            },
            { property: "opacity", from: 1, to: 0 },
          ],
          1000,
          am4core.ease.circleOut
        );
        animation.events.on("animationended", function (event) {
          animateBullet(event.target.object);
        });
      }

      var colorSet = new am4core.ColorSet();

      imageSeries.data = [
        {
          title: "London",
          latitude: 51.5002,
          longitude: -0.1262,
          color: colorSet.next(),
        },
        {
          title: "Peking",
          latitude: 39.9056,
          longitude: 116.3958,
          color: colorSet.next(),
        },
        {
          title: "New Delhi",
          latitude: 28.6353,
          longitude: 77.225,
          color: colorSet.next(),
        },
        {
          title: "Nairobi",
          latitude: 1.2921,
          longitude: 36.8219,
          url: "http://www.google.co.jp",
          color: colorSet.next(),
        },
        {
          title: "Brasilia",
          latitude: -15.7801,
          longitude: -47.9292,
          color: colorSet.next(),
        },
        {
          title: "Washington",
          latitude: 38.8921,
          longitude: -77.0241,
          color: colorSet.next(),
        },
        {
          title: "Kinshasa",
          latitude: -4.3369,
          longitude: 15.3271,
          color: colorSet.next(),
        },
        {
          title: "Cairo",
          latitude: 30.0571,
          longitude: 31.2272,
          color: colorSet.next(),
        },
        {
          title: "Pretoria",
          latitude: -25.7463,
          longitude: 28.1876,
          color: colorSet.next(),
        },
      ];

      /*  // Create map polygon series
      var polygonSeries = chart.series.push(new am4maps.MapPolygonSeries());

      //Set min/max fill color for each area
      polygonSeries.heatRules.push({
        property: "fill",
        target: polygonSeries.mapPolygons.template,
        min: chart.colors.getIndex(1).brighten(1),
        max: chart.colors.getIndex(1).brighten(-0.3),
      });

      // Make map load polygon data (state shapes and names) from GeoJSON
      polygonSeries.useGeodata = true;

      // Set heatmap values for each state
      polygonSeries.data = [
        {
          id: "KE-28",
          value: 28,
          title: "Nairobi",
          latitude: 1.2921,
          longitude: 36.8219,
        },
        { id: "KE-19", value: 19 },
        { id: "KE-39", value: 39 },
        { id: "KE-14", value: 14 },
        { id: "KE-21", value: 21 },
        { id: "KE-23", value: 23 },
        { id: "KE-30", value: 30 },
        { id: "KE-10", value: 10 },
        { id: "KE-22", value: 22 },
        { id: "KE-13", value: 13 },
        { id: "KE-27", value: 27 },
        { id: "KE-29", value: 29 },
        { id: "KE-16", value: 16 },
        { id: "KE-33", value: 33 },
        { id: "KE-34", value: 34 },
        { id: "KE-02", value: 2 },
        { id: "KE-08", value: 8 },
        { id: "KE-06", value: 6 },
        { id: "KE-15", value: 15 },
        { id: "KE-18", value: 18 },
        { id: "KE-40", value: 40 },
        { id: "KE-36", value: 36 },
        { id: "KE-17", value: 17 },
        { id: "KE-12", value: 12 },
        { id: "KE-41", value: 41 },
        { id: "KE-35", value: 35 },
        { id: "KE-45", value: 45 },
        { id: "KE-31", value: 31 },
        { id: "KE-38", value: 38 },
        { id: "KE-32", value: 32 },
        { id: "KE-26", value: 26 },
        { id: "KE-04", value: 4 },
        { id: "KE-20", value: 20 },
        { id: "KE-11", value: 11 },
        { id: "KE-44", value: 44 },
        { id: "KE-07", value: 7 },
        { id: "KE-03", value: 3 },
        { id: "KE-42", value: 42 },
        { id: "KE-05", value: 5 },
        { id: "KE-01", value: 1 },
        { id: "KE-09", value: 9 },
        { id: "KE-37", value: 37 },
        { id: "KE-47", value: 47 },
        { id: "KE-46", value: 46 },
        { id: "KE-24", value: 24 },
        { id: "KE-25", value: 25 },
        { id: "KE-43", value: 43 },
      ];

      // Set up heat legend
      let heatLegend = chart.createChild(am4maps.HeatLegend);
      heatLegend.series = polygonSeries;
      heatLegend.align = "left";
      heatLegend.valign = "bottom";
      heatLegend.width = am4core.percent(20);
      heatLegend.marginRight = am4core.percent(4);
      heatLegend.minValue = 0;
      heatLegend.maxValue = 40000000;

      // Set up custom heat map legend labels using axis ranges
      var minRange = heatLegend.valueAxis.axisRanges.create();
      minRange.value = heatLegend.minValue;
      minRange.label.text = minRange.value;
      var maxRange = heatLegend.valueAxis.axisRanges.create();
      maxRange.value = heatLegend.maxValue;
      maxRange.label.text = maxRange.value;

      // Blank out internal heat legend value axis labels
      heatLegend.valueAxis.renderer.labels.template.adapter.add(
        "text",
        function () {
          return "";
        }
      );

      // Configure series tooltip
      var polygonTemplate = polygonSeries.mapPolygons.template;
      polygonTemplate.tooltipText = "{name}: {value}";
      polygonTemplate.nonScalingStroke = true;
      polygonTemplate.strokeWidth = 0.5;

      // Create hover state and set alternative fill color
      var hs = polygonTemplate.states.create("hover");
      hs.properties.fill = am4core.color("#3c5bdc");

       */
    },
    MiniLogsChart(reference, title, color, category, data) {
      var container = am4core.create(reference, am4core.Container);
      container.logo.disabled = true;
      container.layout = "grid";
      container.fixedWidthGrid = false;
      container.width = am4core.percent(100);
      container.height = am4core.percent(100);

      this.line = container.createChild(am4charts.XYChart);
      this.line.width = am4core.percent(75);
      this.line.height = 120;
      this.line.fontSize = 12;

      this.line.data = data;

      /* chart.titles.template.fontSize = 10;
      chart.titles.template.textAlign = "left";
      chart.titles.template.isMeasured = true;
      chart.titles.create().text = title; */

      this.line.padding(0, 0, 0, 0);

      var dateAxis = this.line.xAxes.push(new am4charts.DateAxis());
      dateAxis.renderer.grid.template.disabled = false;
      dateAxis.renderer.labels.template.disabled = false;
      dateAxis.startLocation = 0.5;
      dateAxis.endLocation = 0.7;
      dateAxis.cursorTooltipEnabled = false;

      var valueAxis = this.line.yAxes.push(new am4charts.ValueAxis());
      valueAxis.min = 0;
      valueAxis.renderer.grid.template.disabled = true;
      valueAxis.renderer.baseGrid.disabled = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.cursorTooltipEnabled = false;

      this.line.cursor = new am4charts.XYCursor();
      this.line.cursor.lineY.disabled = true;
      this.line.cursor.behavior = "none";

      var series = this.line.series.push(new am4charts.LineSeries());
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
      bullet.circle.radius = 7;
    },
    DiscrepanciesChart() {
      this.pie = am4core.create("DiscrepanciesChart", am4charts.PieChart);
      this.pie.hiddenState.properties.opacity = 0; // this creates initial fade-in
      this.pie.logo.disabled = true;
      this.pie.padding(10, 0, 0, 0);
      this.pie.fontSize = 11;

      this.pie.data = [
        {
          type: "Physical Count",
          count: 501,
        },
        {
          type: "System Count",
          count: 301,
        },
      ];

      this.pie.innerRadius = 50;
      this.pie.legend = new am4charts.Legend();

      // Add and configure Series
      var pieSeries = this.pie.series.push(new am4charts.PieSeries());
      pieSeries.dataFields.value = "count";
      pieSeries.dataFields.category = "type";
      pieSeries.slices.template.stroke = am4core.color("#fff");
      pieSeries.slices.template.strokeWidth = 2;
      pieSeries.slices.template.strokeOpacity = 1;

      // This creates initial animation
      pieSeries.hiddenState.properties.opacity = 1;
      pieSeries.hiddenState.properties.endAngle = -45;
      pieSeries.hiddenState.properties.startAngle = -45;
      //pieSeries.labels.template.fill = am4core.color("#fff"); - change label colors
    },
  },
  beforeUnmount() {
    this.map.dispose();
    this.pie.dispose();
    this.line.dispose();
  },
};
</script>

<style>
.v-stepper,
.v-stepper__header {
  box-shadow: unset;
}
.StatCard1 {
  background: linear-gradient(45deg, rgb(29 95 107), rgb(72, 198, 67));
  _background: darkslategray !important;
}
.StatCard2 {
  background: linear-gradient(45deg, rgb(191 31 142), rgb(56 132 177));
}
.StatCard3 {
  background: linear-gradient(45deg, rgb(195 184 44), rgb(41 93 81));
}
.StatCard4 {
  background: linear-gradient(45deg, rgb(44 195 167), rgb(220 68 68));
}
.KenyanMap {
  background: linear-gradient(45deg, rgb(216 187 122), rgb(175 255 255));
}
.Card1 {
  _background: linear-gradient(135deg, rgb(101, 155, 103), rgb(209, 213, 81));
  background: darkslateblue !important;
}
.GroupedAvatars {
  _display: inline-block;
}

.GroupedAvatars:not(:first-child) {
  margin-left: -20px;
  border: 2px solid #c79f56;
  /*   -webkit-mask: radial-gradient(
    circle 15px at 5px 50%,
    transparent 99%,
    #fff 100%
  ); */
  mask: radial-gradient(circle 25px at -5px 50%, transparent 100%, #fff 100%);
}

.map-marker {
  /* adjusting for the marker dimensions
    so that it is centered on coordinates */
  margin-left: -8px;
  margin-top: -8px;
  box-sizing: border-box;
}
.map-marker.map-clickable {
  cursor: pointer;
}

.StoresCard {
  _background-image: linear-gradient(to top, #e8198b 0%, #c7eafd 100%);
  background-image: linear-gradient(to right, #ed6ea0 0%, #ec8c69 100%);
}
.SectionsCard {
  background-image: linear-gradient(-20deg, #b721ff 0%, #21d4fd 100%);
}
.LocationsCard {
  background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
}
</style>
