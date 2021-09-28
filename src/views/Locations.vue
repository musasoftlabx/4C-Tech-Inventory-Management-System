<template>
  <v-main>
    <NoContent v-if="NoContent.visible" :NoContent="NoContent" />
    <v-container
      v-else
      fluid
      :class="$vuetify.breakpoint.smAndUp && 'px-12 pt-6'"
    >
      <v-row justify="center">
        <v-col cols="12" sm="12">
          <v-data-iterator
            :items="dataset"
            :items-per-page.sync="itemsPerPage"
            :page="page"
            :search="search"
            :custom-filter="CustomFilter"
            hide-default-footer
            no-data-text=""
          >
            <template v-slot:header>
              <div>
                <div v-if="dataset.length >= 6">
                  <v-row>
                    <v-col cols="12" sm="4">
                      <v-text-field
                        v-model="search"
                        clearable
                        rounded
                        flat
                        outlined
                        hide-details
                        dense
                        prepend-inner-icon="mdi-table-search"
                        label="Search"
                        class="my-3"
                      >
                        <template v-slot:append>
                          <v-btn
                            color="primary"
                            small
                            rounded
                            class="mr-n4"
                            style="margin-top: -2px"
                            @click="$refs.CreateLocation.visible = true"
                          >
                            <v-icon left>mdi-shape-polygon-plus</v-icon>ADD
                            LOCATION
                          </v-btn>
                        </template>
                      </v-text-field>
                    </v-col>
                    <v-spacer />
                    <v-col align="end" cols="12" sm="4">
                      <span class="mr-4 grey--text"
                        >Page {{ page }} of {{ numberOfPages }}</span
                      >
                      <v-btn
                        fab
                        dark
                        small
                        color="primary"
                        class="mr-1"
                        @click="formerPage"
                      >
                        <v-icon>mdi-chevron-left</v-icon>
                      </v-btn>
                      <v-btn
                        fab
                        dark
                        small
                        color="primary"
                        class="ml-1"
                        @click="nextPage"
                      >
                        <v-icon>mdi-chevron-right</v-icon>
                      </v-btn>
                    </v-col>
                  </v-row>
                  <v-row no-gutters align="center">
                    <span class="grey--text">Locations per page</span>
                    <v-menu offset-y>
                      <template v-slot:activator="{ on }">
                        <v-btn dark text color="primary" class="ml-2" v-on="on">
                          {{ itemsPerPage }}
                          <v-icon>mdi-chevron-down</v-icon>
                        </v-btn>
                      </template>
                      <v-list>
                        <v-list-item
                          v-for="(number, index) in itemsPerPageArray"
                          :key="index"
                          @click="updateItemsPerPage(number)"
                        >
                          <v-list-item-title>{{ number }}</v-list-item-title>
                        </v-list-item>
                      </v-list>
                    </v-menu>
                  </v-row>
                </div>
                <h3 class="mt-2">
                  All Locations<v-chip
                    small
                    text-color="white"
                    color="#bcbf02"
                    class="ml-2 subtitle-1"
                    ><span>{{ dataset.length }}</span></v-chip
                  >
                </h3>

                <div class="body-2 mt-2">All locations appear here.</div>
                <v-btn
                  v-if="dataset.length < 6"
                  outlined
                  color="primary"
                  class="mt-3 GradientButton"
                  @click="$refs.CreateLocation.visible = true"
                >
                  <v-icon left>mdi-shape-polygon-plus</v-icon>ADD LOCATION
                </v-btn>
                <v-divider class="mt-5 mb-8" />
              </div>
            </template>

            <template v-slot:no-data
              ><v-row no-gutters justify="center" class="mt-10">
                <v-alert
                  text
                  outlined
                  color="deep-orange"
                  icon="mdi-progress-alert"
                >
                  Mmmmh...looks like you don't have any locations currently.
                  Click
                  <v-btn
                    color="primary"
                    small
                    outlined
                    class="mx-2 GradientButton"
                    style="margin-top: -2px"
                    @click="$refs.CreateLocation.visible = true"
                    >ADD LOCATION
                  </v-btn>
                  to add a new location.
                </v-alert>
              </v-row>
            </template>

            <template v-slot:default="props">
              <section v-if="skeleton">
                <v-row>
                  <v-col
                    v-for="i in 12"
                    :key="i"
                    cols="12"
                    sm="6"
                    md="4"
                    lg="3"
                    xl="2"
                  >
                    <v-skeleton-loader type="image, article" />
                  </v-col>
                </v-row>
              </section>
              <section>
                <v-row>
                  <v-col
                    v-for="(item, i) in props.items"
                    :key="i"
                    cols="12"
                    sm="6"
                    md="4"
                    lg="3"
                    xl="2"
                  >
                    <v-card
                      max-width="400"
                      class="rounded-b-xl elevation-3 px-5 pb-5 pt-8"
                      style="border-top: 3px solid #7b68ee"
                    >
                      <img
                        style="
                          position: absolute;
                          left: -12px;
                          top: -12px;
                          z-index: 1;
                        "
                        :src="$store.getters.server + 'img/CornerRibbon.png'"
                        width="100px"
                        height="100px"
                      />
                      <!-- <div class="text-h4 font-weight-bold text-center">
                          {{ item.name }}
                        </div> -->

                      <v-row
                        :id="`loc-${item.barcode}`"
                        no-gutters
                        justify="center"
                      >
                        <svg ref="Barcode" :id="item.barcode" />
                      </v-row>

                      <div class="text-caption font-italic text-center">
                        Located in Kenya at {{ item.store }} under the
                        {{ item.section }} section
                      </div>

                      <v-text-field
                        v-model="item.PhysicalCount"
                        v-mask="'####'"
                        :rules="[validators.NumericsRegex]"
                        label="Physical Count *"
                        filled
                        rounded
                        clearable
                        hide-details
                        prepend-inner-icon="mdi-tag-multiple"
                        :disabled="item.edited > 0"
                        :readonly="item.edited > 0"
                        class="my-3"
                        @change="
                          EditPhysicalCount(i, item.PK, item.PhysicalCount)
                        "
                      />

                      <v-row no-gutters>
                        <!-- <v-col cols="6">
                            <v-card class="PhysicalCount" dark>
                              <div class="text-caption">Physical count</div>
                              <div class="text-h5 font-weight-thin">
                                {{ item.PhysicalCount }}
                              </div>
                            </v-card>
                          </v-col> -->
                        <v-col cols="12" align="center">
                          <v-card
                            width="100"
                            class="PhysicalCount rounded-lg elevation-1"
                            dark
                          >
                            <div class="text-caption">System Count</div>
                            <div class="text-h4 font-weight-thin yellow--text">
                              <number
                                ref="number1"
                                :from="0"
                                :to="item.SystemCount"
                                :duration="1"
                                :format="FormatWithCommas"
                                :delay="0"
                                easing="Power1.easeOut"
                              />
                            </div>
                          </v-card>
                        </v-col>
                      </v-row>

                      <div class="text-center caption my-2">
                        <span class="font-weight-bold">Modified On:&nbsp;</span>
                        <span>{{ item.ModifiedOn }}</span>
                      </div>
                      <v-row>
                        <v-col cols="4" align="center">
                          <v-btn
                            color="pink"
                            fab
                            small
                            text
                            @click="Print(item.barcode)"
                            ><v-icon>mdi-printer</v-icon></v-btn
                          >
                        </v-col>
                        <v-col cols="4" align="center">
                          <v-btn
                            color="primary"
                            fab
                            small
                            text
                            @click="ShowScanner(item.barcode, item.PK)"
                            ><v-icon>mdi-barcode-scan</v-icon></v-btn
                          >
                        </v-col>
                        <v-col cols="4" align="center">
                          <v-btn
                            color="brown"
                            fab
                            small
                            text
                            @click="View(item.barcode)"
                            ><v-icon>mdi-eye</v-icon></v-btn
                          >
                        </v-col>
                      </v-row>
                    </v-card>
                  </v-col>
                </v-row>
              </section>
            </template>
          </v-data-iterator>
        </v-col>
      </v-row>
    </v-container>
    <Drawer />
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
    <CreateLocation ref="CreateLocation" @NewLocation="NewLocation" />
    <Scanner ref="Scanner" @Scanned="Scanned" />
  </v-main>
</template>

<script>
import JsBarcode from "jsbarcode";
//import PrintJS from "print-js";

import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import NoContent from "@/components/NoContent";
import CreateLocation from "@/components/Dialogs/CreateLocation";
import Scanner from "@/components/Dialogs/Scanner";
export default {
  beforeRouteEnter(to, from, next) {
    !localStorage._4c_token_ ? next(from.fullPath) : next();
  },
  components: {
    Drawer,
    Snackbar,
    NoContent,
    CreateLocation,
    Scanner,
  },
  data() {
    return {
      NoContent: {
        visible: true,
        loading: true,
      },
      apps: true,
      skeleton: false,
      headers: [],
      dataset: [],
      companies: [],
      company: null,
      itemsPerPageArray: [12, 24, 36],
      search: "",
      filter: {},
      sortDesc: false,
      page: 1,
      itemsPerPage: 12,
      sortBy: "no",
    };
  },
  watch: {
    name(str) {
      if (str) {
        this.name = str.replace(/\w\S*/g, function (txt) {
          return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
        this.alias = this.name.replace(/[\W_]/g, "");
      }
    },
  },
  mounted() {
    this.$Progress.start();
    this.skeleton = true;
    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(`${this.$store.getters.endpoint}Locations/Get/`, {
        method: "post",
        body: JSON.stringify({
          token: this.$store.getters.token,
        }),
      })
    )
      .then((response) => {
        response.text().then((res) => {
          this.dataset = JSON.parse(res);

          setTimeout(() => {
            this.skeleton = false;
          }, 1000);
          this.NoContent.visible = false;
        });
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
        this.$Progress.finish();
      });
  },
  updated() {
    //let x = this.dataset.length % this.itemsPerPage;
    //for (let i = 0; i < 5; i++) {
    //if (i < this.itemsPerPage * this.page) {

    //}
    //}
    this.dataset.forEach((item, i) => {
      JsBarcode(`#${this.dataset[i].barcode}`, this.dataset[i].barcode, {
        width: 1,
        height: 50,
        textPosition: "top",
        textMargin: 10,
        //lineColor: "#990000",
      });
    });
  },
  methods: {
    ShowScanner(LocationBarcode, LocationPK) {
      this.$refs.Scanner.LocationPK = LocationPK;
      this.$refs.Scanner.LocationBarcode = LocationBarcode;
      this.$refs.Scanner.visible = true;
      setTimeout(() => {
        this.$refs.Scanner.$refs.ProductBarcode.focus();
      }, 100);
    },
    NewLocation(dataset) {
      this.dataset.unshift(dataset);
    },
    Scanned() {},
    EditPhysicalCount(index, key, value) {
      if (value && value > 0) {
        if (this.dataset[index].edited === 0) {
          this.Confirm({
            task: "show",
            operation: "edit",
            color: "info",
            metadata: {
              index,
              key,
              value,
            },
            icon: "mdi-playlist-edit",
            content: `Are you sure you want to set <b class="info--text">${value}</b> as the physical count? Kindly note that once edited, you won't able to edit again.`,
            title: "Edit Physical Count",
          });
        } else {
          this.$refs.Alert.Alertify({
            visible: true,
            content:
              "This location has already been counted. Changes cannot be made.",
            title: "Error",
            icon: "mdi-progress-alert",
            color: "error",
          });
        }
      }
    },
    CustomFilter(value, search) {
      return (
        value != null &&
        typeof value !== "boolean" &&
        JSON.stringify(value)
          .toLowerCase()
          .indexOf(search.toString().toLowerCase()) !== -1
      );
    },
    nextPage() {
      if (this.page + 1 <= this.numberOfPages) this.page += 1;
    },
    formerPage() {
      if (this.page - 1 >= 1) this.page -= 1;
    },
    updateItemsPerPage(number) {
      this.itemsPerPage = number;
    },
    Confirm(props) {
      if (props.task === "show") {
        this.$refs.Confirm.Confirmify({
          visible: true,
          task: props.task,
          operation: props.operation,
          metadata: props.metadata,
          content: props.content,
          title: props.title,
          icon: props.icon || "mdi-help-circle",
          color: props.color || "info",
          truth: props.truth || "YES",
          falsy: props.falsy || "NO",
        });
      } else if (props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
        if (this.$refs.Confirm.confirm.operation === "edit") {
          let metadata = this.$refs.Confirm.confirm.metadata;
          let index = metadata.index;
          let key = metadata.key;
          let value = metadata.value;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Locations/Edit/`, {
              method: "post",
              body: JSON.stringify({
                token: this.$store.getters.token,
                key,
                value,
              }),
            })
          )
            .then((response) => {
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error while editing count. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.dataset[index].edited = 1;

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Edited physical count!",
                    color: "success",
                    timeout: 2000,
                  });
                }
              });
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
              this.loading = false;
            });
        }
      } else if (!props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
      }
    },
    /* Print(barcode) {
      PrintJS(barcode, "html");
    }, */
    async Print(barcode) {
      await this.$htmlToPaper(`loc-${barcode}`);
    },
  },
  computed: {
    numberOfPages() {
      return Math.ceil(this.dataset.length / this.itemsPerPage);
    },
    filteredKeys() {
      return this.headers.filter((key) => key !== `Name`);
    },
  },
};
</script>

<style>
.CustomFormModifier .v-btn.v-btn--has-bg {
  background-image: linear-gradient(
    to right,
    #84fab0 0%,
    #8fd3f4 51%,
    #84fab0 100%
  );
}
.custom-shape-divider-bottom-1632171631 {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  overflow: hidden;
  line-height: 0;
}

.custom-shape-divider-bottom-1632171631 svg {
  position: relative;
  display: block;
  width: calc(100% + 1.3px);
  height: 120px;
  transform: rotateY(180deg);
}

.custom-shape-divider-bottom-1632171631 .shape-fill {
  fill: #fff;
}

.divider-color {
  background-image: linear-gradient(to right, #74ebd5 0%, #9face6 100%);
}
.PhysicalCount {
  background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
}
.SystemCount {
  background-image: linear-gradient(120deg, #fccb90 0%, #d57eeb 100%);
}
</style>
