import Vue from 'vue';

Vue.component('vehicle', {
	methods:{
		count(value){
			return value+(this.vehicle?1:0) + (this.version?1:0)
		}
	},
	computed: {
		vehicle(){ return typeof window.shop.vehicles[this.$getVehicle()] !== 'undefined' ? window.shop.vehicles[this.$getVehicle()] : false },
		version(){ return typeof window.shop.vehicles[this.$getVersion()] !== 'undefined' ? window.shop.vehicles[this.$getVersion()] : false }
	}
});
