<template>
  <section>
    <v-dialog
      v-model="visible"
      persistent
      max-width="400"
      transition="dialog-top-transition"
    >
      <v-card flat class="rounded-lg">
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
              <h2 style="color: #757b2a">&#11208; Add Store</h2>
              <div style="color: #1d6954" class="text-caption mt-0 ml-1">
                Create a new store by entering the details below
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
          <v-form ref="AddStore" v-model="valid" class="CustomFormModifier">
            <v-row no-gutters>
              <v-col cols="4" class="px-2">
                <vue-tel-input-vuetify
                  v-model="country.name"
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
                  {{ country.name }}
                </div></v-col
              >
              <v-col cols="12" class="px-2">
                <v-text-field
                  ref="StoreName"
                  v-model="StoreName"
                  :rules="[validators.required]"
                  label="Store Name *"
                  filled
                  rounded
                  dense
                  prepend-inner-icon="mdi-store"
                  @keyup.enter="AddStore"
                />
              </v-col>
              <v-col cols="12" align="end" class="px-2 pt-3 pb-6">
                <v-btn
                  color="primary"
                  height="35"
                  rounded
                  :loading="loading"
                  :disabled="!valid || loading"
                  @click="AddStore"
                  >CREATE</v-btn
                >
              </v-col>
            </v-row>
          </v-form>
        </div>
      </v-card>
    </v-dialog>
    <Alert ref="Alert" />
  </section>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
      loading: false,
      valid: false,
      country: {
        name: null,
        code: null,
      },
      StoreName: null,
    };
  },
  methods: {
    GetCountry(country) {
      this.country.name = country.name;
      this.country.code = country.iso2;
    },
    AddStore() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Stores/Add/`, {
          method: "post",
          body: JSON.stringify({
            token: this.$store.getters.token,
            country: this.country,
            StoreName: this.StoreName,
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                title: "Error occurred!",
                content: res,
                visible: true,
                icon: "mdi-wifi-strength-1-alert",
                color: "error",
              });
            } else {
              this.$refs.AddStore.reset();
              this.$emit("NewStore", JSON.parse(res));
              this.visible = false;
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
  },
};
</script>
