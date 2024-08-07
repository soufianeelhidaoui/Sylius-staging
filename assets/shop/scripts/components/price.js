import Vue from 'vue';

Vue.component('price', {
	props:['code'],
	computed:{
		custom(){
			return this.partner_data && typeof this.partner_data.prices[this.code] != "undefined"
		},
		price(){
			return this.custom?this.partner_data.prices[this.code]:0
		},
		mode(){
			return this.$store.getters.taxMode()
		},
    partner_data(){
			return this.$store.getters.partner_data()
		}
	}
});
