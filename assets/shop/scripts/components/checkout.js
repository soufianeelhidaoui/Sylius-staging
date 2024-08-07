import Vue from 'vue';
import eventBus from "../services/event-bus";
import proxyRepository from "../repositories/proxyRepository";
import cartRepository from "../repositories/cartRepository";

Vue.component('checkout', {
	data(){
		return{
			search: '',
			sent: false,
			sending: false,
			error: false,
			params:{
				title:'',
				first_name:'',
				last_name:'',
				phone_number:'',
        zip_code:'',
        city:'',
        street_name:'',
				email:''
			}
		}
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
		vehicle(){
			let family = this.$getVehicle();
			let id = this.$getVehicleId();

			return {
				family: family?family:shop.vehicles.default_family,
				id: id?id:shop.vehicles.default_id
			}
		},
	},
	methods:{
		send(brand){

			let data = this.params;

			data.dealer = this.partner.kvps;
			data.vehicles = [parseInt(this.vehicle.id)];
			data.family = this.vehicle.family;

			this.error = false;
			this.sending = true;

			proxyRepository.createLead(brand, data).then((response)=>{

				this.sending = false;

				if( typeof response.error != 'undefined' ){

					this.error = response.error;
				}
				else{

					this.sent = true;

					cartRepository.clear();

					this.$nextTick(()=>{
						window.scroll(0, 0)
					})
				}
			})
		}
	}
});
