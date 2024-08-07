import Vue from 'vue';
import searchRepository from "../repositories/searchRepository";

//see: https://shopify.dev/api/ajax/reference/predictive-search

Vue.component('search', {
	props:['autocomplete'],
	data(){
		return{
			visible: false,
			focused: false,
			results: {
				products:[]
			},
			params: {
				q:'',
				resources:{
					type: 'product',
					options: {
						limit:10,
						fields: "title,tag,variants.sku,body"
					}
				}
			}
		}
	},
	computed:{
		count(){
			return this.results.products.length
		},
		version(){
			return this.$getVersion();
		},
		vehicle(){
			return this.$getVehicle();
		},
    collection(){
      return this.version ? this.version : this.vehicle
    }
	},
	methods:{
		focus(){
			this.focused = true;
			if(this.$refs.input)
				this.$refs.input.focus();
		},
		prevent(e){
			if( this.params.q < 3 )
				e.preventDefault();
		},
		open(){
			this.visible = true;
			this.$nextTick(this.focus)
		},
		close(){
			this.visible = false;
			this.params.q = '';
			this.empty()
		},
		empty(){
			this.results = {
				products:[]
			}
		},
		suggest(e){
			this.params.q = e.target.value;

			if( this.autocomplete ){
				if( this.params.q.length > 2 )
					this.request();
				else
					this.empty()
			}
		},
		unfocus(){
			this.focused = false
		},
		request(){
			let params = JSON.parse(JSON.stringify(this.params));

			searchRepository.suggest(params).then(results => {
				let queryWords = params.q.toLowerCase().split(/[ _-]+/);

				queryWords = queryWords.filter(word => word.length > 2);
				
				this.results.products = results.products.filter(product => {
					let productName = product.name.toLowerCase();
					return queryWords.some(word => productName.includes(word));
				});
		
				let event = new CustomEvent('suggest', {detail: {query: params.q, results: results}});
				document.dispatchEvent(event);
			})
		}
	},
	mounted() {

		document.body.addEventListener('click', e=>{
			if( this.focused && this.params.q.length ){
				if( !this.$el.contains(e.target) )
					this.unfocus();
			}
		})

		this.$listen('open-search', this.open)
		this.$listen('close-search', this.close)
	}
});
