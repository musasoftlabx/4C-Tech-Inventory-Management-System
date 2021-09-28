<template>
  <section v-resize="OnResize">
    <v-app-bar app fixed dark color="transparent" class="elevation-0 px-3">
      <div class="text-h4 grey--text font-weight-bold mb-1 mt-4">4C-Tech</div>
      <v-spacer />
      <v-btn text class="mt-4" color="primary">
        FORGOT{{ $vuetify.breakpoint.smAndUp ? " PASSWORD" : "" }}?
      </v-btn>
    </v-app-bar>

    <div id="LoginSVG">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 1000 125"
        preserveAspectRatio="none"
      >
        <defs>
          <linearGradient id="LoginSVGGradient">
            <stop offset="0%" stop-color="#b33bf5"></stop>
            <stop offset="80%" stop-color="#7b68ee"></stop>
          </linearGradient>
        </defs>
        <path
          class="divider-fill"
          style="opacity: 0.3"
          d="m 0,81.659348 c 81.99918,0 88.739113,19.697032 141.99858,19.697032 46.67953,0 33.12967,-19.551122 85.12915,-19.551122 57.49943,0 75.87924,34.141512 143.87856,34.141512 55.99944,0 98.99901,-36.475982 149.49851,-36.475982 76.49923,0 77.32923,29.482322 131.49868,43.284832 55.99944,14.2694 72.82927,-44.169982 142.82857,-44.169982 41.37959,0 74.35926,28.033012 103.68897,28.033012 C 942.67057,106.61865 1000,75.093678 1000,75.093678 V 24.999998 H 0 Z"
        />
        <path
          class="divider-fill"
          d="m 0,81.988666 c 97.331947,-14.74806 115.33231,19.667594 176.67353,19.667594 53.33107,0 60.14121,-51.965834 122.64246,-51.965834 84.00168,0 116.35232,90.668944 200.004,73.034484 80.0016,-16.85492 86.00172,-63.205964 153.34306,-63.205964 49.661,0 86.65174,33.00405 124.00248,33.00405 33.33067,0 51.27103,-31.16054 78.00156,-31.16054 43.93088,0 51.28103,31.0868 97.90196,28.83245 C 976.99954,89.015066 1000,74.267006 1000,74.267006 V 5.8499091e-6 H 0 Z"
        />
      </svg>
    </div>

    <v-container>
      <v-row
        align="center"
        class="mt-n16"
        :class="$vuetify.breakpoint.smAndUp && 'mx-3'"
        style="height: 100vh"
      >
        <v-col cols="12" md="5">
          <v-card max-width="600" flat color="transparent">
            <v-row>
              <v-col class="text-center">
                <img
                  v-if="WindowSize.y > 700"
                  width="200"
                  class="mt-n5"
                  :src="`${$store.getters.server}img/scanner.jpg`"
                />
                <div class="text-h5 mt-n6 mb-5">Let's get you signed in</div>
              </v-col>
            </v-row>
            <v-form ref="Login" v-model="login.valid" class="px-5">
              <v-select
                v-model="login.domain"
                :rules="[validators.required]"
                :items="login.domains"
                label="Domain *"
                filled
                rounded
                prepend-inner-icon="mdi-domain"
              />
              <v-text-field
                ref="LoginUsername"
                v-model="login.username"
                :rules="[validators.required]"
                name="username"
                label="Username *"
                filled
                rounded
                clearable
                autofocus
                prepend-inner-icon="mdi-account-circle"
                @keyup.enter="Login"
              />
              <v-text-field
                v-model="login.password"
                :rules="[validators.required]"
                name="password"
                label="Password *"
                filled
                rounded
                clearable
                counter
                prepend-inner-icon="mdi-shield-key"
                :append-icon="textpass ? 'mdi-eye' : 'mdi-eye-off'"
                :type="textpass ? 'text' : 'password'"
                @click:append="TextPass"
                @keyup.enter="Login"
              />
              <div class="mb-3 d-flex justify-center">
                <v-btn
                  color="primary"
                  width="200"
                  class="GradientButton"
                  rounded
                  large
                  :dark="!login.valid || login.loading"
                  :disabled="!login.valid || login.loading"
                  :loading="login.loading"
                  @click="Login"
                  >LOGIN</v-btn
                >
              </div>
            </v-form>
          </v-card>
        </v-col>
        <v-col cols="12" md="7" align="end" class="hidden-sm-and-down">
          <img
            width="90%"
            :src="`${$store.getters.server}img/management.png`"
            class="mt-n8"
          />
          <div class="text-h3 font-weight-thin">4C Technologies</div>
          <div class="teal--text text-h6 my-3">Inventory Management System</div>
        </v-col>
      </v-row>
    </v-container>
    <Alert ref="Alert" />
  </section>
</template>

<script>
export default {
  data() {
    return {
      WindowSize: {
        x: 0,
        y: 0,
      },
      login: {
        visible: true,
        valid: false,
        loading: false,
        domain: null,
        domains: [],
        username: null,
        password: null,
      },
    };
  },
  beforeCreate() {
    clearInterval(window.tokenify);
    localStorage.removeItem("_4c_token_");
  },
  mounted() {
    this.OnResize();
    this.$refs.LoginUsername.$el.focus();
    localStorage.domains
      ? ((this.login.domains = JSON.parse(JSON.parse(localStorage.domains))),
        (this.login.domain = JSON.parse(JSON.parse(localStorage.domains))[0]))
      : this.Domains();
  },
  methods: {
    OnResize() {
      this.WindowSize = { x: window.innerWidth, y: window.innerHeight };
    },
    Domains() {
      this.$Progress.start();
      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Login/Domains/`)
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$Progress.fail();
            } else {
              this.login.domains = JSON.parse(res);
              this.login.domain = JSON.parse(res)[0];
              localStorage.domains = JSON.stringify(res);
            }
          });
        })
        .catch(() => {
          this.$Progress.fail();
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
    Login() {
      this.login.loading = true;

      this.promiseFetch(this.$store.getters.fetchTimeout)(
        fetch(`${this.$store.getters.endpoint}Login/`, {
          method: "post",
          body: JSON.stringify({
            username: this.login.username,
            password: Buffer(this.login.password).toString("base64"),
          }),
        })
      )
        .then((response) => {
          response.text().then((res) => {
            if (response.status > 200) {
              this.$refs.Alert.Alertify({
                visible: true,
                content: res,
                title: "Invalid Credentials",
                icon: "mdi-shield-account",
                color: "error",
              });
            } else {
              localStorage.setItem("_4c_token_", res);
              this.$store.dispatch("StoreToken", res);
              this.$router.push("/");
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
          this.login.loading = false;
        });
    },
  },
};
</script>

<style>
html {
  overflow: auto;
}
input:-webkit-autofill {
  -webkit-background-clip: text;
  background-clip: text; /* Change the white autofill to transparent */
}
#LoginSVG {
  overflow: hidden;
  position: absolute;
  bottom: 0;
  width: 100%;
  height: 200px;
  line-height: 0;
  transform: rotate(180deg);
  left: 0;
}
#LoginSVG svg {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  left: 50%;
  transform: translateX(-50%);
}
#LoginSVG .divider-fill {
  fill: url("#LoginSVGGradient");
  transform-origin: bottom;
  transform: rotateY(0deg);
}
</style>
