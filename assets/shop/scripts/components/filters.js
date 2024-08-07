import Vue from 'vue';
import VueSlider from 'vue-slider-component'

Vue.component('filters', {
	props:['url'],
	components: {
		VueSlider
	},
	data(){
		return{
			maxPrice: window.maxPrice, 
			all_filters : window.shop.filters.default,
			loading : false,
			params:{
				vehicle: false,
				version: false,
				range_price: [0,0],
				filters : window.shop.filters.current
			},
			active: false,
			maxPrix: localStorage.getItem('max')
		}
	},
	watch:{
		'params.filters':{
			deep: true,
			handler(){
				if( this.active !== 'price')
					this.active = false

				//this.$nextTick(this.getFilters);
			}
		},
		'params.vehicle'(newVal, oldVal){
			if( oldVal)
				this.params.version = false
		}
	},
  computed:{
    collection(){
      return this.params.version ? this.params.version : this.params.vehicle
    }
  },
	methods:{
		save(){
			this.$setVehicle(this.params.vehicle);
			this.$setVersion(this.params.version);
		},
		priceFormat(v){

			return v+'€'
		},
		setPriceRange(handle){
			this.params.filters[handle] = this.params.range_price;
			this.active=false
		},
		reset(){

			this.params.vehicle = false;
			this.$setVehicle(false);
			this.$nextTick(()=>{
				document.location.href = this.$refs.filtersForm.getAttribute('action')
			})
		},
		show(active, param, value){

			if( this.active === active ){

				if( active !== 'price' )
					this.active = false;
			}
			else{

				this.active = active;

				if( param && value && (!this.params.filters[param].length || JSON.stringify(this.params.filters[param]) === JSON.stringify([0,0])) )
					this.params.filters[param] = value
			}
		},
		getName(param, version){

			return window.shop.vehicles[version][param]
		},
		getUrl(base){

			if( this.params.version )
				return base+'/'+this.params.version
			else if( this.params.vehicle )
				return base+'/'+this.params.vehicle
			else
				return base
		},
		getTerms(term){

			let terms = term.split(' ');
			terms = terms.filter(term=>{
				return typeof window.shop.vehicles[term] === 'undefined'
			});
			term = terms.join(' ')

			if( this.params.version )
				return term+' '+this.params.version
			else if( this.params.vehicle )
				return term+' '+this.params.vehicle
			else
				return term
		},
		remove(type, param, value){

			this.active = false;

			if( param === 'vehicle' ){

				this.params.vehicle = false;
				this.params.version = false;
			}
			else if( param === 'version' ){

				this.params.version = false;
			}
			else if( type === 'price_range' ){

				this.params.filters[param] = [0,0]
				this.params.range_price = [0,0]
			}
			else{

				const index = this.params.filters[param].indexOf(value);
				if (index > -1)
					this.params.filters[param].splice(index, 1);
			}
		},
		closeList(e){

			let parent = e.target.closest('.sn-filters__group') ? e.target.closest('.sn-filters__group').classList.contains : false;

			if( !e.target.classList.contains('sn-filters__list') &&  !e.target.classList.contains('sn-filters__title') && !parent)
				this.active = false
		},
		getFilters(){

			this.loading = true;

			let params = {};
			let formData = new FormData(this.$refs.filtersForm)

			for (let key of formData.keys())
				params[key] = formData.get(key);
		}
	},
	mounted() {

		let version = this.$getVersion();
		let vehicle = this.$getVehicle();

		if( version ){

			let vehicle = window.shop.vehicles[version];
			this.params.version = version;
			this.params.vehicle = vehicle.parent;
		}
		else if(vehicle ){

			this.params.vehicle = vehicle;
		}

		this.params.range_price = window.shop.filters.current.price
	},
	created(){
		document.body.addEventListener('click',this.closeList)
	},
	destroyed() {
		document.body.removeEventListener('click',this.closeList)
	}
});
