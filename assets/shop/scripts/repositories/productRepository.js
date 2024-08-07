import Vue from "vue";

export default {

	getRecommendations(product_id, limit) {

		return new Promise((resolve, reject) => {

			Vue.http.get(shop.routes.root+"recommendations/products.json?product_id="+product_id+"&limit="+limit).then(response=>{
				resolve(response.body);
			});
		});
	},

	get(handle) {

		return new Promise((resolve, reject) => {

			Vue.http.get(shop.routes.root+"products/"+handle+".js").then(response=>{
				resolve(response.body);
			});
		});
	}
};
