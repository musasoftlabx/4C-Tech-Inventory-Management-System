<template>
  <v-main>
    <v-card tile flat>
      <!-- <Appbar /> -->
      <v-container fluid>
        <v-row no-gutters align="center" class="py-3">
          <v-col>
            <div class="ml-4">
              <div class="text-h6">{{ $route.name }}</div>
              <v-btn
                small
                color="primary"
                rounded
                @click="ShowAddStore"
                class="GradientButton mt-3"
              >
                <v-icon left>mdi-plus</v-icon>ADD SECTION</v-btn
              >
            </div>
          </v-col>
        </v-row>
        <v-divider />
        <v-row no-gutters class="mt-6 px-4">
          <v-col cols="12">
            <v-card id="FancyTable" flat />
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
import Snackbar from "@/components/Snackbar";
import AddStore from "@/components/Dialogs/AddStore";
export default {
  components: {
    //Appbar,
    Snackbar,
    AddStore,
  },
  data() {
    return {
      loading: false,
      FancyGrid: {},

      FancyGridDefaults: {
        sortable: true,
        editable: false,
        resizable: true,
        ellipsis: false,
      },
    };
  },
  mounted() {
    this.FancyGrid = new Fancy.Grid({
      renderTo: "FancyTable",
      theme: {
        name: "material",
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
              fileName: "Stores",
              header: true,
            });
          },
        },
      ],
      width: "fit",
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
          index: "code",
          title: "Code",
        },
        {
          index: "name",
          title: "Name",
          editable: true,
        },
      ],
      data: {
        proxy: {
          autoLoad: true,
          autoSave: false,
          remoteSort: true,
          //type: "rest",
          url: `${this.$store.getters.endpoint}Sections/Get/`,
          params: {
            token: this.$store.getters.token,
          },
          writer: {
            type: "json",
          },
          /* beforeRequest: (o) => {
              this.$Progress.start();
              //o.params["token"] = this.$store.getters.token;
              return o;
            },
            afterRequest: (o) => {
              this.$Progress.finish();
              return o;
            }, */
        },
      },
      events: [
        {
          change: (grid, o) => {
            this.$Progress.start();
            this.promiseFetch(this.$store.getters.fetchTimeout)(
              fetch(`${this.$store.getters.endpoint}Sections/Edit/`, {
                method: "post",
                body: JSON.stringify({
                  token: this.$store.getters.token,
                  resource: "sections",
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
            fetch(`${this.$store.getters.endpoint}Sections/Delete/`, {
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
        content: `Added ${data.name} to list of stores.`,
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
