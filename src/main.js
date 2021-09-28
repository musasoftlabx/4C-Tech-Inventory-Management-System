import Vue from "vue";
import App from "./App.vue";
import "./registerServiceWorker";
import router from "./router";
import store from "./store";
import vuetify from "./plugins/vuetify";
import "roboto-fontface/css/roboto/roboto-fontface.css";
import "@mdi/font/css/materialdesignicons.css";
import "@babel/polyfill";
import Mixins from "@/mixins/";
import Alert from "@/components/Dialogs/Alert";
import Confirm from "@/components/Dialogs/Confirm";
import vuescroll from "vuescroll";
import VueTheMask from "vue-the-mask";
import VueProgressBar from "vue-progressbar";
import VuePageTransition from "vue-page-transition";
import VueNumberAnimation from "vue-number-animation";
import TelephoneInput from "vue-tel-input-vuetify/lib";
import VueHtmlToPaper from "vue-html-to-paper";

Vue.mixin(Mixins);
Vue.component("Alert", Alert);
Vue.component("Confirm", Confirm);
Vue.use(vuescroll);
Vue.use(VueTheMask);
Vue.use(VueProgressBar, {
  color: "rgb(143, 255, 199)",
  failedColor: "red",
  height: "2px",
});
Vue.use(VuePageTransition);
Vue.use(TelephoneInput, { vuetify });
Vue.use(VueNumberAnimation);
Vue.use(VueHtmlToPaper, {
  name: "_blank",
  specs: ["fullscreen=no", "titlebar=no", "scrollbars=no"],
  autoClose: true, // if false, the window will not close after printing
  windowTitle: window.document.title, // override the window title
});

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  vuetify,
  render: (h) => h(App),
}).$mount("#app");
