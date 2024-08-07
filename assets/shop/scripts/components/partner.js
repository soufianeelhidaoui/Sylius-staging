import Vue from 'vue';

Vue.component('partner', {
  computed:{
    partner(){
      return this.$store.getters.partner()
    },
    partner_data(){
      return this.$store.getters.partner_data()
    }
  }
});
