import Vue from 'vue';
import eventBus from "../services/event-bus";
import proxyRepository from "../repositories/proxyRepository";
import cartRepository from "../repositories/cartRepository";

Vue.component('ra-map', {
	data(){
		return{
			display: false,
			partner: false,
			selected: false
		}
	},
	computed:{
		closable(){
			return shop.template==='index' || (this.partner && this.selected)
		},
		isDisabledRoute() {
			const disabledRoutes = ['/cart/validate/','/checkout/address','/checkout/select-payment','/checkout/complete','/order/thank-you','/payment/capture/'];
			return disabledRoutes.includes(window.location.pathname);
		  }
	},
	methods:{
		select(){

			this.selected = true;

			proxyRepository.getDealer(this.partner.kvps).then(data=>{

				this.$store.commit('partner', this.partner)
				this.$store.commit('partner_data', data)

				cartRepository.refresh()

			}).catch(()=>{})

			setTimeout(()=>{ this.display = false }, 400)
		},
		deselect(){

			this.selected = false;
			this.partner = false;
		}
	},
	mounted() {

		eventBus.$on('open-map', ()=>{

			if(this.isDisabledRoute){
				this.display = false
			} 
			else{
				this.display = true
			}
		})

		eventBus.$on('close-map', ()=>{
			this.display = false
		})

		eventBus.$on('dealer', dealer=>{
			this.partner = dealer
		})

		this.partner = this.$store.getters.partner();

		if( this.partner )
			this.selected = true;

		if( !this.partner && !this.closable )
			this.display = true;
	}
});
