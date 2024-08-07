// Import scss
import '../styles/theme.scss';

// load Vuejs
import Vue from 'vue';

import VueAwesomeSwiper from 'vue-awesome-swiper';

import * as Sentry from "@sentry/vue";

import VueAOS from './plugins/aos';

import VueResource from 'vue-resource';

import { Mixins } from './services/mixins';

import Plugins from './services/plugins';

import store from './services/store';

import './services/directives';
import './services/filters';

import cartRepository from './repositories/cartRepository';

Vue.use(VueAwesomeSwiper, {
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
});

Vue.use(VueAOS);
Vue.use(VueResource);
Vue.mixin(Mixins);
Vue.use(Plugins);

Sentry.init({ Vue, dsn: 'https://6509e4208b3d4bcca8e33d83142b0d05@o1170562.ingest.sentry.io/6374986' });

// load components
const components = require.context('./components', true, /^\.\/.*\.js/);
components.keys().forEach(components);

// start app
const bundle = new Vue({
  store,
  el: '#app',
  data() {
    return {
      isMobile: false,
      isTablet: false,
      hasScrolled: false,
      isHomePage: window.shop.template === 'index',
    };
  },
  delimiters: ['[[', ']]'],
  methods: {
    catchResize(event) {
      this.isMobile = window.innerWidth <= 768;
      this.isTablet = window.innerWidth < 1024;
    },
    catchScroll(event) {
      this.hasScrolled = this.isHomePage ? window.scrollY > 965 : window.scrollY > 0;
    },
  },
  computed: {
    cart() {
      return this.$store.getters.cart();
    },
    partner() {
      return this.$store.getters.partner();
    },
    partner_data(){
      return this.$store.getters.partner_data()
    },
  },
  mounted() {

    this.catchResize();
    this.catchScroll();

    if (window.shop.template === 'index') { this.$setVehicle(false); }

    document.body.classList.remove('loading');
    document.body.classList.add('loaded');

    let sHeaderShadow= document.querySelector('.s-header__shadow');

    if (!this.isHomePage && sHeaderShadow) {
        sHeaderShadow.style.display = 'none';
    }

    cartRepository.get();
  },
  created() {
    Vue.http.interceptors.push(request => function (response) {
      if (response.status >= 400) {
        if (typeof response.body.message !== 'undefined') { window.alert(`${response.body.message}: ${response.body.description}`); } else { window.alert(response.body); }
      }
    });

    window.addEventListener('scroll', this.catchScroll);
    window.addEventListener('resize', this.catchResize);
  },
});

window.onbeforeprint = function () {
  document.querySelectorAll('img').forEach((el) => {
    el.removeAttribute('loading');
  });
};
