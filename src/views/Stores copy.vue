<template>
  <v-main>
    <v-card tile flat>
      <Appbar />
      <Drawer />
      <v-container fluid>
        <v-row no-gutters align="center" class="py-3">
          <v-col>
            <div class="ml-4">
              <div class="text-h6">API Mappings</div>
              <div class="text-caption">
                This table is editable. Click on a data item to edit. Leaving
                the data item will automatically save.
              </div>
              <v-text-field
                v-model="search"
                clearable
                flat
                outlined
                hide-details
                dense
                prepend-inner-icon="mdi-table-search"
                label="Search"
                class="my-3"
                style="width: 300px"
              />
            </div>
          </v-col>
          <v-col align="end" class="pr-8"
            ><v-btn
              color="primary"
              :loading="loading"
              :disabled="loading"
              @click="$refs.CreateDocumentationItem.visible = true"
            >
              <v-icon left>mdi-plus</v-icon>CREATE NEW MAPPING</v-btn
            ></v-col
          >
        </v-row>

        <!-- <v-row align="center">
        <v-col>
          <div class="ml-4">
            <div class="text-h6 mb-n1">{{ $route.name }}</div>
            <small class="ml-1">Feel free to use CTRL + S to quick save</small>
          </div>
        </v-col>
        <v-col align="end">
          <v-btn
            color="primary"
            outlined
            large
            :loading="loading"
            :disabled="loading"
            class="pr-5"
            >SAVE CHANGES</v-btn
          >
        </v-col>
      </v-row> -->
        <v-divider />

        <v-row justify="center" class="px-4">
          <v-col cols="12">
            <v-data-table
              :headers="headers"
              :items="dataset"
              :loading="loading"
              :items-per-page="25"
              :search="search"
              :custom-filter="CustomFilter"
              @input="selected = $event"
              dense
              class="mx-5 CustomVerticalTable mt-3"
              style="background: transparent"
            >
              <template v-slot:item.OperationFK="{ header, item }">
                <div>Operation</div>
                <v-select
                  v-model="item.OperationFK.value"
                  :items="operations"
                  hide-details
                  solo
                  flat
                  dense
                  @focus="
                    item.OperationFK.focused = true;
                    item.OperationFK.memory = item.OperationFK.value;
                  "
                  @blur="item.OperationFK.focused = false"
                  @change="
                    item.OperationFK.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.OperationFK.value,
                      item.OperationFK.memory
                    );
                  "
                  :loading="item.OperationFK.loading"
                  :outlined="item.OperationFK.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-subtitle-2 font-weight-bold"
                />

                <div>API</div>
                <v-select
                  v-model="item.API.value"
                  :items="APIs"
                  hide-details
                  solo
                  flat
                  dense
                  @focus="
                    item.API.focused = true;
                    item.API.memory = item.API.value;
                  "
                  @blur="item.API.focused = false"
                  @change="
                    item.API.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.API.value,
                      item.API.memory
                    );
                  "
                  :loading="item.API.loading"
                  :outlined="item.API.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-subtitle-2 font-weight-bold"
                />

                <div>Type</div>
                <v-select
                  v-model="item.type.value"
                  :items="datatypes"
                  hide-details
                  solo
                  flat
                  dense
                  @focus="
                    item.type.focused = true;
                    item.type.memory = item.type.value;
                  "
                  @blur="item.type.focused = false"
                  @change="
                    item.type.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.type.value,
                      item.type.memory
                    );
                  "
                  :loading="item.type.loading"
                  :outlined="item.type.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-subtitle-2 font-weight-bold"
                />

                <div>Name</div>
                <v-text-field
                  v-model.trim="item.name.value"
                  solo
                  dense
                  flat
                  hide-details
                  @focus="
                    item.name.focused = true;
                    item.name.memory = item.name.value;
                  "
                  @keyup.enter="
                    item.name.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.name.value,
                      item.name.memory
                    );
                  "
                  @blur="
                    item.name.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.name.value,
                      item.name.memory
                    );
                  "
                  :loading="item.name.loading"
                  :outlined="item.name.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-body-2 font-weight-bold"
                />
              </template>

              <template v-slot:item.description="{ header, item }">
                <v-textarea
                  v-model.trim="item.description.value"
                  solo
                  dense
                  flat
                  hide-details
                  @focus="
                    item.description.focused = true;
                    item.description.memory = item.description.value;
                  "
                  @keyup.enter="
                    item.name.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.description.value,
                      item.description.memory
                    );
                  "
                  @blur="
                    item.description.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.description.value,
                      item.description.memory
                    );
                  "
                  :loading="item.description.loading"
                  :outlined="item.description.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-body-2 font-weight-bold"
                />
              </template>

              <template v-slot:item.sample="{ header, item }">
                <v-textarea
                  v-model.trim="item.sample.value"
                  solo
                  dense
                  flat
                  hide-details
                  @focus="
                    item.sample.focused = true;
                    item.sample.memory = item.sample.value;
                  "
                  @keyup.enter="
                    item.name.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.sample.value,
                      item.sample.memory
                    );
                  "
                  @blur="
                    item.sample.focused = false;
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.sample.value,
                      item.sample.memory
                    );
                  "
                  :loading="item.sample.loading"
                  :outlined="item.sample.focused"
                  prepend-inner-icon="mdi-playlist-edit"
                  class="text-body-2 font-weight-bold"
                />
              </template>

              <template v-slot:item.visible="{ header, item }">
                <v-switch
                  v-model="item.visible.value"
                  dense
                  @focus="item.visible.memory = item.visible.value"
                  @change="
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.visible.value,
                      item.visible.memory
                    )
                  "
                />
              </template>

              <template v-slot:item.delete="{ header, item }">
                <v-btn
                  fab
                  text
                  small
                  @click="Delete(dataset.indexOf(item), item.PK)"
                >
                  <v-icon color="error">mdi-delete</v-icon>
                </v-btn>
              </template>
              <!-- <template v-slot:item.ParentTitle="{ header, item }">
              <v-text-field
                v-model="item.ParentTitle.value"
                solo
                dense
                flat
                hide-details
                @focus="
                  item.ParentTitle.focused = true;
                  item.ParentTitle.memory = item.ParentTitle.value;
                "
                @keyup.enter="
                  item.ParentTitle.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.ParentTitle.value,
                    item.ParentTitle.memory
                  );
                "
                @blur="
                  item.ParentTitle.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.ParentTitle.value,
                    item.ParentTitle.memory
                  );
                "
                :loading="item.ParentTitle.loading"
                :outlined="item.ParentTitle.focused"
                prepend-inner-icon="mdi-playlist-edit"
                class="text-body-2 font-weight-bold"
              />

              <v-text-field
                v-model="item.PostmanCollectionLink.value"
                solo
                dense
                flat
                hide-details
                @focus="
                  item.PostmanCollectionLink.focused = true;
                  item.PostmanCollectionLink.memory =
                    item.PostmanCollectionLink.value;
                "
                @keyup.enter="
                  item.ParentTitle.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.PostmanCollectionLink.value,
                    item.PostmanCollectionLink.memory
                  );
                "
                @blur="
                  item.PostmanCollectionLink.focused = false;
                  Edit(
                    dataset.indexOf(item),
                    item.PK,
                    header.value,
                    item.PostmanCollectionLink.value,
                    item.PostmanCollectionLink.memory
                  );
                "
                :loading="item.PostmanCollectionLink.loading"
                :outlined="item.PostmanCollectionLink.focused"
                label="Postman Collection Link (Optional)"
                prepend-inner-icon="mdi-playlist-edit"
                class="text-body-2"
              />

              <v-btn
                small
                outlined
                color="error"
                class="mt-2 ml-3"
                :loading="item.ParentTitle.loading"
                @click="
                  DeleteItem(item.ParentTitle.value, dataset.indexOf(item))
                "
                >DELETE ITEM</v-btn
              >
            </template>

            <template v-slot:item.ParentContent="{ header, item }">
              <div class="my-4">
                <jodit-editor
                  v-model="item.ParentContent.value"
                  :config="editor"
                  @input="
                    Edit(
                      dataset.indexOf(item),
                      item.PK,
                      header.value,
                      item.ParentContent.value,
                      item.ParentContent.memory
                    )
                  "
                />
                <v-btn
                  tile
                  small
                  color="primary"
                  class="mt-4"
                  :loading="item.ParentContent.loading"
                  @click="
                    GetDocumentationChildren(
                      item.ParentTitle.value,
                      dataset.indexOf(item)
                    )
                  "
                  >EDIT CHILD ITEMS</v-btn
                >
              </div>
            </template> -->
            </v-data-table>
          </v-col>
        </v-row>
      </v-container>
    </v-card>

    <v-dialog
      v-model="visible"
      max-width="400"
      transition="dialog-top-transition"
    >
      <v-card flat rounded="lg">
        <div class="custom-shape-divider-bottom-1632171631 divider-color">
          <svg
            data-name="Layer 1"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1200 120"
            preserveAspectRatio="none"
          >
            <path
              d="M741,116.23C291,117.43,0,27.57,0,6V120H1200V6C1200,27.93,1386.4,129.83,741,116.23Z"
              class="shape-fill"
            ></path>
          </svg>
        </div>
        <v-card
          dark
          flat
          color="transparent"
          class="pl-8 pt-2"
          style="position: absolute"
        >
          <v-row no-gutters>
            <v-col cols="10" class="mt-3">
              <h2 style="color: #757b2a" class="font-weight-bold">
                &#11208; Create Location
              </h2>
              <div
                style="color: #1d6954"
                class="font-weight-bold text-caption mt-0 ml-1"
              >
                Create a new location by entering the details below
              </div>
            </v-col>
            <v-col cols="2" align="end"
              ><v-icon size="24" class="ml-5" @click="visible = false"
                >mdi-close</v-icon
              ></v-col
            >
          </v-row>
        </v-card>
        <div
          class="pt-2 px-5 pb-0 CustomFormModifier"
          style="
            max-height: 75vh;
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 150px !important;
          "
        >
          <v-form
            ref="CreateLocation"
            v-model="valid"
            class="CustomFormModifier"
          >
            <v-row no-gutters>
              <v-col cols="4" class="px-2">
                <vue-tel-input-vuetify
                  v-model="country"
                  :rules="[validators.required]"
                  :defaultCountry="'KE'"
                  :preferredCountries="['KE', 'UG', 'TZ']"
                  :mode="'international'"
                  :inputOptions="{ showDialCode: true }"
                  selectLabel="Country *"
                  label="Country *"
                  outlined
                  rounded
                  dense
                  @country-changed="GetCountry"
                />
              </v-col>
              <v-col class="px-2"
                ><div class="text-caption">Selected country:</div>
                <div class="font-weight-bold" style="color: #76c7b1">
                  {{ country }}
                </div></v-col
              >
              <v-col cols="12" class="px-2">
                <v-select
                  v-model="store"
                  :rules="[validators.required]"
                  :items="datastore.stores"
                  label="Store *"
                  item-text="store"
                  item-value="code"
                  filled
                  rounded
                  dense
                  prepend-inner-icon="mdi-account-box"
                />
              </v-col>
              <v-col cols="12" class="px-2">
                <v-select
                  v-model="section"
                  :rules="[validators.required]"
                  :items="datastore.sections"
                  label="Section *"
                  item-text="section"
                  item-value="code"
                  filled
                  rounded
                  dense
                  prepend-inner-icon="mdi-account-box"
                />
              </v-col>
              <v-col cols="12" align="end" class="px-2 pt-3 pb-6">
                <v-btn
                  color="primary"
                  height="35"
                  rounded
                  :loading="loading"
                  :disabled="!valid || loading"
                  @click="CreateLocation"
                  >CREATE</v-btn
                >
              </v-col>
            </v-row>
          </v-form>
        </div>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
    <Snackbar ref="Snackbar" />
    <Confirm ref="Confirm" @confirm="Confirm" @cancel="Confirm" />
  </v-main>
</template>

<script>
import Appbar from "@/components/Appbar";
import Drawer from "@/components/Drawer";
import Snackbar from "@/components/Snackbar";
export default {
  components: {
    Appbar,
    Drawer,
    Snackbar,
  },
  data() {
    return {
      visible: false,
      valid: false,
      country: null,
      CountryCode: null,
      store: null,
      section: null,
      datastore: {
        stores: [
          {
            store: "TRM",
            code: "TRM",
          },
          {
            store: "Sarit",
            code: "SAR",
          },
          {
            store: "Junction",
            code: "JUN",
          },
        ],
        sections: [
          {
            section: "Men",
            code: "BU",
          },
          {
            section: "Women",
            code: "BG",
          },
          {
            section: "Kids",
            code: "CK",
          },
        ],
      },

      search: "",
      headers: [],
      dataset: [],
      operations: [],
      APIs: [],
      datatypes: [],

      loading: false,
      operation: "POST",
      name: null,
      alias: null,
      color: {
        focused: false,
        value: "#9E9E9E",
      },
      endpoint: null,
      purpose: null,
      subsidiaries: {
        state: false,
        subsidiaries: [],
      },
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
  /*  mounted() {
    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(this.$store.getters.endpoint + "APIs/GetAPImappings.php", {
        method: "post",
        body: JSON.stringify({
          token: this.$store.getters.token,
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
            this.headers = JSON.parse(res).headers;
            this.dataset = JSON.parse(res).dataset;
            this.operations = JSON.parse(res).operations;
            this.APIs = JSON.parse(res).APIs;
            this.datatypes = JSON.parse(res).datatypes;
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
  }, */
  methods: {
    GetCountry(country) {
      this.country = country.name;
      this.CountryCode = country.iso2;
    },
    CreateLocation() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(this.$store.getters.endpoint + "CreateLocation/", {
          method: "post",
          body: JSON.stringify({
            token: this.$store.getters.token,
            country: this.CountryCode,
            store: this.store,
            section: this.section,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Existing API",
                icon: "mdi-progress-alert",
                color: "error",
              });
            } else {
              //this.subsidiaries.subsidiaries = [];
              //this.visible = false;
              //this.$refs.Form.reset();
              //this.$emit("GetAPIs");
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
    },
    Edit(index, key, col, val, old) {
      if (val !== old || !val.trim()) {
        this.dataset[index][col].loading = true;
        this.promiseFetch(this.$store.getters.fetchTimeout)(
          fetch(this.$store.getters.endpoint + "APIs/EditAPImappings.php", {
            method: "post",
            body: JSON.stringify({
              key,
              col,
              val,
            }),
          })
        )
          .then((response) => {
            response.text().then(() => {
              if (response.status > 200) {
                this.$refs.Alert.Alertify({
                  visible: true,
                  content: "Error while updating terms. Kindly try again",
                  title: "Error",
                  icon: "mdi-progress-alert",
                  color: "error",
                });
              } else {
                this.dataset[index][col].loading = true;
                this.$refs.Snackbar.Snackify({
                  visible: true,
                  content: "Changes saved!",
                  color: "primary",
                  timeout: 1000,
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
            this.dataset[index][col].loading = false;
          });
      }
    },
    Delete(index, key, parent) {
      this.Confirm({
        task: "show",
        operation: "delete",
        color: "error",
        icon: "mdi-delete-variant",
        metadata: {
          index,
          key,
          parent,
        },
        content: "Proceed to delete this mapping?",
        title: "Delete mapping",
      });
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
          let index = metadata.index;
          let key = metadata.key;
          let parent = metadata.parent;

          this.promiseFetch(this.$store.getters.fetchTimeout)(
            fetch(this.$store.getters.endpoint + "APIs/DeleteAPImappings.php", {
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
                    content: "Error while deleting API. Kindly try again",
                    title: "Error",
                    icon: "mdi-progress-alert",
                    color: "error",
                  });
                } else {
                  parent
                    ? this.dataset[parent].subsidiaries.splice(index, 1)
                    : this.dataset.splice(index, 1);

                  this.$refs.Snackbar.Snackify({
                    visible: true,
                    content: "API deleted!",
                    color: "primary",
                    timeout: 1000,
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
</style>
