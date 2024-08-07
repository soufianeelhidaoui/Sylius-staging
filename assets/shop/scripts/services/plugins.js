import eventBus from "./event-bus";

export default {

	install (Vue, options) {

		Vue.prototype.$open = function(type, params){

			document.body.classList.add('has-'+type)
			eventBus.$emit('open-'+type, params)
		};

		Vue.prototype.$close = function(type){

			document.body.classList.remove('has-'+type)
			eventBus.$emit('close-'+type)
		};

		Vue.prototype.$listen = function(type, callback){

			eventBus.$on(type, callback)
		};

		Vue.prototype.$trigger = function(type){

			eventBus.$emit(type)
		};

		Vue.prototype.$getCart = function(){

			return this.$store.getters.cart()
		};

		Vue.prototype.$updateUrl = function(name, value){

			const urlParams = new URLSearchParams(window.location.search);
			urlParams.set(name, value);
			window.location.search = urlParams;
		};

		Vue.prototype.$setVehicle = function(vehicle){

			if( vehicle === 'undefined' )
				vehicle = false;

			this.$store.commit('vehicle', vehicle)

			if( vehicle ){

				this.$store.commit('vehicleId', shop.vehicles[vehicle].id)
			}
			else{

				this.$store.commit('version', false)
				this.$store.commit('vehicleId', false)
			}
		};

		Vue.prototype.$setVersion = function(version){

			if( version === 'undefined' )
				version = false;

			this.$store.commit('version', version)

			if( version ){

				this.$setVehicle(shop.vehicles[version].parent)
				this.$store.commit('vehicleId', shop.vehicles[version].id)
			}
			else{

				let vehicle = this.$store.getters.vehicle()

				if( vehicle )
					this.$store.commit('vehicleId', shop.vehicles[vehicle].id)
			}
		};

		Vue.prototype.$getVehicle = function(){
			return this.$store.getters.vehicle()
		};

		Vue.prototype.$getVehicleId = function(){
			return this.$store.getters.vehicleId()
		};

		Vue.prototype.$getVersion = function(){
			return this.$store.getters.version()
		};

		Vue.prototype.$isVehicle = function(tag){
			return typeof window.shop.vehicles[tag] !== 'undefined'
		};
	}
}
