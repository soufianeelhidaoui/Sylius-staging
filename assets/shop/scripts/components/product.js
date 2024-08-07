import cartRepository from '../repositories/cartRepository';

import Vue from 'vue';
import productRepository from '../repositories/productRepository';

Vue.component('product', {
  props:['token'],
  data() {
    return {
      zoomed: false,
      product: false,
      options: {
        1: '',
        2: '',
        3: '',
      },
      imagesFromOptions: {},
      quantity: 1,
      slideIndex: 1,
      replaceState: true,
    };
  },
  computed: {
    cart() {
      return this.$store.getters.cart();
    },
    mode() {
      return this.$store.getters.taxMode();
    },
    current_variant() {
      return this.product ? this.product.variants.find(variant => variant.id === this.product.selected_variant) : false;
    },
    current_variant_quantity() {
      return this.product.quantity[this.current_variant.id];
    },
  },
  watch: {
    current_variant(variant) {
      if (variant && variant.featured_media) { this.slideTo(variant.featured_media.position); }
    },
  },
  methods: {
    zoom() {
      this.zoomed = true;
      this.$nextTick(() => {
        if( this.$refs.swiper_zoom )
          this.$refs.swiper_zoom.$swiper.slideTo(this.slideIndex - 1, 0);
      });
    },
    getPrice(price) {
      if (this.mode === 'ttc') { return price; }
      return price / 1.2;
    },
    isAvailable(position, value) {
      const options = this.availableValues(position);
      return options.indexOf(value) > -1;
    },
    adjustQuantity(quantity) {
      quantity = this.quantity + quantity;
      this.quantity = Math.max(1, quantity);
    },
    availableValues(position) {
      if (position === 1) {
        return this.product.options_with_values[0].values;
      } else if (position === 2) {
        const options = [];
        this.product.variants.forEach((variant) => {
          if (variant.option1 === this.options[1]) { options.push(variant.option2); }
        });
        return options;
      } else if (position === 3) {
        const options = [];
        this.product.variants.forEach((variant) => {
          if (variant.option1 === this.options[1] && variant.option2 === this.options[2]) { options.push(variant.option3); }
        });
        return options;
      }
    },
    addToCart() {
      cartRepository.add(this.product.id, false, this.quantity, this.token);
    },
    slideTo(index) {
      this.slideIndex = index;

      if ('swiper' in this.$refs && this.$refs.swiper) { this.$refs.swiper.$swiper.slideTo(index - 1, 400); }
    },
    updateSelected(position) {
      if (position === 1) { this.options[2] = this.availableValues(2)[0]; }

      if (position === 2) { this.options[3] = this.availableValues(3)[0]; }

      const variant = this.product.variants.find((variant) => {
        let isCurrent = true;
        variant.options.forEach((option, index) => {
          isCurrent = isCurrent && this.options[index + 1] === option;
        });
        return isCurrent;
      });

      this.product.selected_variant = variant ? variant.id : false;

      if (variant && window.history.replaceState && this.replaceState) {
        window.history.replaceState({}, variant.name, `${window.location.origin + window.location.pathname}?variant=${this.product.selected_variant}`);
      }
    },
    load(product) {
      this.product = product;

      this.product.variants.forEach((variant, i) => {
        variant.options.forEach((option, j) => {
          if ('featured_image' in variant && variant.featured_image) {
            if (typeof this.imagesFromOptions[option] === 'undefined') { this.imagesFromOptions[option] = []; }

            this.imagesFromOptions[option].push(variant.featured_image.src);
          }
        });
      });

      if (this.current_variant) {
        this.current_variant.options.forEach((option, index) => {
          this.options[index + 1] = option;
        });

        if (this.current_variant.featured_media) { this.slideTo(this.current_variant.featured_media.position); }
      }

      this.$nextTick(() => {
        if ('swiper' in this.$refs && this.$refs.swiper) {
          this.$refs.swiper.$swiper.on('activeIndexChange', (swiper) => {
            this.slideIndex = this.$refs.swiper.$swiper.activeIndex + 1;
          });
        }
      });
    },
  },
  mounted() {
    // todo: try to use props instead of parent args
    if (this.$parent.$options.name === 'popin') {
      this.replaceState = false;
      productRepository.get(this.$parent.args).then(this.load);
    } else {
      this.load(shop.product);
    }
  },
});
