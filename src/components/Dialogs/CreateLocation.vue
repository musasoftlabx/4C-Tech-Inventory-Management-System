<template>
  <section>
    <v-dialog
      v-model="visible"
      max-width="400"
      persistent
      transition="dialog-top-transition"
    >
      <v-card flat class="rounded-lg">
        <div id="Modal-divider-top">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 1000 125"
            preserveAspectRatio="none"
          >
            <path
              class="divider-fill"
              style="opacity: 0.5"
              d="m 0,81.659348 c 81.99918,0 88.739113,19.697032 141.99858,19.697032 46.67953,0 33.12967,-19.551122 85.12915,-19.551122 57.49943,0 75.87924,34.141512 143.87856,34.141512 55.99944,0 98.99901,-36.475982 149.49851,-36.475982 76.49923,0 77.32923,29.482322 131.49868,43.284832 55.99944,14.2694 72.82927,-44.169982 142.82857,-44.169982 41.37959,0 74.35926,28.033012 103.68897,28.033012 C 942.67057,106.61865 1000,75.093678 1000,75.093678 V 24.999998 H 0 Z"
            />
            <path
              class="divider-fill"
              d="m 0,81.988666 c 97.331947,-14.74806 115.33231,19.667594 176.67353,19.667594 53.33107,0 60.14121,-51.965834 122.64246,-51.965834 84.00168,0 116.35232,90.668944 200.004,73.034484 80.0016,-16.85492 86.00172,-63.205964 153.34306,-63.205964 49.661,0 86.65174,33.00405 124.00248,33.00405 33.33067,0 51.27103,-31.16054 78.00156,-31.16054 43.93088,0 51.28103,31.0868 97.90196,28.83245 C 976.99954,89.015066 1000,74.267006 1000,74.267006 V 5.8499091e-6 H 0 Z"
            />
          </svg>
        </div>
        <v-card dark flat color="transparent" class="px-8 pt-6">
          <v-row no-gutters>
            <v-col cols="10">
              <div class="text-h5 font-weight-thin">
                <v-icon size="26" class="mt-n1">mdi-target-variant</v-icon>
                Create Location
              </div>
              <div style="color: #bdffff" class="text-caption mt-0 ml-1">
                Create a new location by entering the details below
              </div>
            </v-col>
            <v-col cols="2" align="end"
              ><v-icon size="24" @click="visible = false"
                >mdi-close</v-icon
              ></v-col
            >
          </v-row>
        </v-card>
        <div
          class="pt-2 px-5 pb-0"
          style="
            overflow-y: auto;
            overflow-x: hidden;
            padding-top: 150px !important;
          "
        >
          <v-form ref="AddLocation" v-model="valid">
            <v-row no-gutters>
              <v-col cols="12" class="px-2">
                <v-select
                  v-model="store"
                  :rules="[validators.required]"
                  :items="FormData.stores"
                  label="Store *"
                  item-text="name"
                  item-value="code"
                  return-object
                  filled
                  rounded
                  prepend-inner-icon="mdi-store"
                />
              </v-col>
              <v-col cols="12" class="px-2">
                <v-select
                  v-model="section"
                  :rules="[validators.required]"
                  :items="FormData.sections"
                  label="Section *"
                  item-text="name"
                  item-value="code"
                  return-object
                  filled
                  rounded
                  prepend-inner-icon="mdi-family-tree"
                />
              </v-col>
              <v-col cols="12" align="end" class="px-2 pt-3 pb-6">
                <v-btn
                  color="primary"
                  height="35"
                  rounded
                  text
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
  </section>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
      loading: false,
      valid: false,
      store: null,
      section: null,
      FormData: {},
    };
  },
  created() {
    this.promiseFetch(this.$store.getters.fetchTimeout)(
      fetch(`${this.$store.getters.endpoint}Locations/GetFormData/`, {
        method: "post",
        body: JSON.stringify({
          token: this.$store.getters.token,
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
            this.FormData = JSON.parse(res);
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
        //this.loading = false;
      });
  },
  methods: {
    CreateLocation() {
      this.loading = true;
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Locations/Add/`, {
          method: "post",
          body: JSON.stringify({
            token: this.$store.getters.token,
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
              this.$refs.AddLocation.reset();
              this.$emit("NewLocation", JSON.parse(res));
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

<style>
#Modal {
  background-color: transparent;
  background-image: none;
}
#Modal-divider-top {
  overflow: hidden;
  position: absolute;
  top: 0;
  width: 100%;
  height: 230px;
  line-height: 0;
  left: 0;
  transform: scaleX(-1);
}
#Modal-divider-top svg {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
}
#Modal-divider-top .divider-fill {
  fill: #7b68ee;
  transform-origin: bottom;
  transform: rotateY(0deg);
}
</style>
