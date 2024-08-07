import Vue from 'vue';
import productRepository from "../repositories/productRepository";

Vue.component('products-recommendations', {
	props:['product_id', 'limit', 'url'],
	data(){
		return{
			products: []
		}
	},
	mounted() {

		productRepository.getRecommendations(this.product_id, this.limit).then(response=>{
			// Filter products where total_quantity > 0
			let inStockProducts = response.body.products.filter(product => product.total_quantity > 0);
			// let products = response.body.products;
			inStockProducts.forEach(product => {
				if( typeof product.options_with_values != 'undefined'){
					product.options_with_values.forEach(option=> {
						product[this.handleize(option.name)] = option.values
					})
				}
			})
			this.products = inStockProducts;
		});
	}
});
