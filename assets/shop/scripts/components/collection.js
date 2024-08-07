import Vue from 'vue';

Vue.component('collection', {
	props:['handle', 'tags', 'template'],
	mounted() {

		let vehicle_tag = this.handle;

		if( this.tags && this.tags.length && typeof shop.vehicles[this.tags[0]] !== 'undefined' )
			vehicle_tag = this.tags[0];

		if( typeof shop.vehicles[vehicle_tag] !== 'undefined'  ){

			let vehicle = shop.vehicles[vehicle_tag];

			if( typeof vehicle.parent !== 'undefined' )
				this.$setVersion(vehicle_tag)
			else
				this.$setVehicle(vehicle_tag)
		}
		else{

			this.$setVehicle(false)
		}
	}
});
