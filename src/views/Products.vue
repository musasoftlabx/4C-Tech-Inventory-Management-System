<template>
  <v-main>
    <v-card tile flat>
      <!-- <Appbar /> -->
      <Drawer />
      <v-container fluid>
        <v-row no-gutters align="center" class="py-3">
          <v-col>
            <div class="ml-4">
              <div class="text-h6">
                <number
                  :from="0"
                  :to="count"
                  :duration="1"
                  :format="FormatWithCommas"
                  :delay="0"
                  easing="Power1.easeIn"
                  class="mr-1"
                />{{ $route.name }}
              </div>
              <div class="text-caption">
                This table is editable. However, only Name and Country are.
                Leaving the data item will automatically save.
              </div>
              <v-btn
                small
                color="primary"
                rounded
                @click="ShowAddStore"
                class="GradientButton mt-3"
              >
                <v-icon left>mdi-plus</v-icon>ADD PRODUCT</v-btn
              >
            </div>
          </v-col>
        </v-row>
        <v-divider />
        <v-row no-gutters class="mt-6 px-4">
          <v-col cols="12">
            <v-card id="FancyTable" outlined />
          </v-col>
        </v-row>
      </v-container>
    </v-card>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <AddStore ref="AddStore" @NewStore="NewStore" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
  </v-main>
</template>

<script>
//import Appbar from "@/components/Appbar";
import Fancy from "fancygrid";
import "fancygrid/client/fancy.min.css";
Fancy.stylesLoaded = true;
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
import AddStore from "@/components/Dialogs/AddStore";
export default {
  components: {
    //Appbar,
    Drawer,
    Snackbar,
    AddStore,
  },
  data() {
    return {
      loading: false,
      FancyGrid: {},
      count: 0,

      FancyGridDefaults: {
        sortable: true,
        editable: false,
        resizable: true,
        ellipsis: false,
        filter: {
          header: true,
          emptyText: "Search",
        },
      },
    };
  },
  mounted() {
    if (this.$vuetify.breakpoint.mdAndUp) this.FancyGridDefaults["flex"] = 1;

    new Promise((resolve) => {
      resolve(this.FancyGridDefaults["flex"] === 1);
    }).then(() => {
      this.FancyGrid = new Fancy.Grid({
        renderTo: "FancyTable",
        theme: {
          name: "blue",
          /* config: {
          cellHeight: 92,
          titleHeight: 65,
        }, */
        },
        tbar: [
          {
            text: "Export to CSV",
            handler: () => {
              this.exportToCSV({
                fileName: "Products",
                header: true,
              });
            },
          },
        ],
        height: "fit",
        selModel: "rows",
        stripped: true,
        exporter: true,
        defaults: this.FancyGridDefaults,
        trackOver: true,
        clicksToEdit: 1,
        columns: [
          {
            type: "order",
            locked: true,
          },
          {
            index: "id",
            title: "PK",
            hidden: true,
          },
          {
            index: "barcode",
            locked: true,
            title: "Barcode",
          },
          {
            index: "name",
            title: "Name",
            editable: true,
          },
          {
            index: "size",
            title: "Size",
            editable: true,
          },
          {
            index: "length",
            title: "Length",
            editable: true,
          },
          {
            index: "ColorCode",
            title: "Color Code",
            editable: true,
          },
          {
            type: "combo",
            index: "color",
            title: "Color",
            editable: true,
            data: ["Brown", "Khaki", "Caramel", "Black"],
          },
          {
            index: "SpecialCode",
            title: "Special Code",
          },
          {
            index: "ModifiedBy",
            title: "Modified By",
          },
          {
            index: "ModifiedOn",
            title: "Modified On",
          },
        ],
        data: {
          remotePage: true,
          remoteFilter: true,
          proxy: {
            autoLoad: true,
            autoSave: false,
            url: `${this.$store.getters.endpoint}Products/Get/`,
            params: {
              token: this.$store.getters.token,
            },
            writer: {
              type: "json",
            },
            beforeRequest: (o) => {
              this.$Progress.start();
              return o;
            },
            afterRequest: (o) => {
              this.$Progress.finish();
              this.count = o.response.totalCount;
              return o;
            },
          },
        },
        paging: {
          pageSize: 15,
          pageSizeData: [5, 10, 15, 20, 50],
          inputWidth: 100,
          refreshButton: true,
        },
        events: [
          {
            change: (grid, o) => {
              this.$Progress.start();
              this.promiseFetch(this.$store.getters.fetchTimeout)(
                fetch(`${this.$store.getters.endpoint}Products/Edit/`, {
                  method: "post",
                  body: JSON.stringify({
                    token: this.$store.getters.token,
                    key: o.id,
                    entity: o.key,
                    value: o.value,
                  }),
                })
              )
                .then((response) => {
                  response.text().then(() => {
                    if (response.status > 200) {
                      this.$Progress.fail();
                      this.$refs.Alert.Alertify({
                        title: "Update Error!",
                        content:
                          "The last change you made could not be saved. Kindly retry.",
                        visible: true,
                        icon: "mdi-wifi-strength-1-alert",
                        color: "error",
                      });
                    } else {
                      this.$refs.Snackbar.Snackify({
                        visible: true,
                        content: "Changes saved!",
                        color: "success",
                        timeout: 2000,
                      });
                      this.FancyGrid.save(o);
                    }
                  });
                })
                .catch(() => {
                  this.$Progress.fail();
                  this.$refs.Alert.Alertify({
                    title: "Connection Timeout",
                    content: this.$store.getters.fetchTimeoutError,
                    visible: true,
                    icon: "mdi-wifi-strength-1-alert",
                    color: "error",
                  });
                })
                .finally(() => {
                  this.$Progress.finish();
                });
            },
          },
        ],
      });
    });
  },
  methods: {
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
          color: props.color || "orange",
          truth: props.truth || "YES",
          falsy: props.falsy || "NO",
        });
      } else if (props.task) {
        this.$refs.Confirm.Confirmify({
          visible: false,
        });
        if (this.$refs.Confirm.confirm.operation === "delete") {
          let metadata = this.$refs.Confirm.confirm.metadata;
          let object = metadata.object;
          let key = metadata.key;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(`${this.$store.getters.endpoint}Products/Delete/`, {
              method: "post",
              body: JSON.stringify({
                key,
              }),
            })
          )
            .then((response) => {
              response.text().then(() => {
                if (response.status > 200) {
                  this.$refs.Alert.Alertify({
                    visible: true,
                    content: "Error while deleting store. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  this.FancyGrid.remove(object);

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "Store removed!",
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
    ShowAddStore() {
      this.$refs.AddStore.visible = true;
      setTimeout(() => {
        this.$refs.AddStore.$refs.StoreName.focus();
      }, 100);
    },
    GetCountry(country) {
      this.country = country.name;
      this.CountryCode = country.iso2;
    },
    NewStore(data) {
      this.FancyGrid.insert(data);

      this.$refs.Snackbar.Snackify({
        visible: true,
        content: `Added ${data.name} to list of products.`,
        color: "success",
        timeout: 3000,
      });
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
  },
  computed: {
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

.action-column-remove {
  cursor: pointer;
  padding: 5px !important;
  position: relative;
  top: -5px;
  border-radius: 2px;
  background-color: #e4827d;
  color: #fff;
}
.id-column-cls .fancy-grid-cell {
  background: #ffb798 !important;
  border-color: #ffb798;
}
</style>
