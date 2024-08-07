import Vue from 'vue';
import cartRepository from '../repositories/cartRepository';

Vue.component('cart', {
  data() {
    return {
      enableLeadMode: false // Set this to false to deactivate the lead mode
    };
  },
  computed:{
    cart(){
      return this.$getCart()
    },
    partner(){
      return this.$store.getters.partner()
    },
    partner_data(){
      return this.$store.getters.partner_data()
    },
    mode(){

      if( !this.cart || !this.cart.items.length )
        return false;

      let mode = 'payment';
      if (!this.partner_data.has_term || !this.partner_data.has_payment) {
        mode = 'lead';
      }

      if (this.cart && mode === 'payment') {
        this.cart.items.forEach(item => {
          if ((item.quotation || !item.in_stock))
            mode = 'lead'
        })
      }

      return mode;
    }
  },
  methods:{
    updateCart(line, quantity){
      window.location.reload();
      return cartRepository.change(line, quantity);
    }
  }
});
