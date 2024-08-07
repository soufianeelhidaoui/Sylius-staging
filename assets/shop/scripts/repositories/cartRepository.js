import Vue from "vue";
import store from "../services/store";

export default {

	get() {

		return new Promise((resolve, reject) => {

			Vue.http.get(shop.routes.root+'/cart.js').then(response=>{

				store.commit('cart', response.body);
				resolve(response.body);

			}, reject)
		});
	},

	refresh() {

		return new Promise((resolve, reject) => {

			let partner = JSON.parse(localStorage.getItem('partner'))

			Vue.http.post(shop.routes.root+'/cart/refresh.js'+(partner?'?partnerId='+partner.kvps:'')).then(response=>{

				store.commit('cart', response.body);
				resolve(response.body);

				if( shop.template === "cart" )
					window.location.reload()

			}, reject)
		});
	},

	clear() {

		return new Promise((resolve, reject) => {

			Vue.http.post(shop.routes.root+'/cart/clear.js').then(response=>{

				store.commit('cart', response.body);

				let event = new CustomEvent("cart-clear");
				document.dispatchEvent(event);

				resolve(response.body);

			}, reject)
		});
	},

	remove(line) {

		return this.change(line, 0);
	},

	add(product_id, variant, quantity, token) {

		return new Promise((resolve, reject) => {

			let params = {
				_token: token,
				cartItem:{
					quantity: quantity,
				}
			};

			let partner = JSON.parse(localStorage.getItem('partner'))

			if( variant )
				params.cartItem.variant = variant;

			Vue.http.post(shop.routes.root+'/cart/add.js?productId='+product_id+(partner?'&partnerId='+partner.kvps:''), {sylius_add_to_cart:params}).then(response=>{

				let item = response.body;

				Vue.http.get(shop.routes.root+'/cart.js').then(response=>{

					response.body.items.forEach(item=>{

						if( item.id === product_id ){

							let event = new CustomEvent("cart-change", {detail: item});
							document.dispatchEvent(event);
						}
					});

					store.commit('cart', response.body);
					resolve(item);

				}, reject)

			}, reject)
		});
	},

	change(line, quantity) {

		let params = {'quantity': quantity, 'line': line }

		return new Promise((resolve, reject) => {

			let partner = JSON.parse(localStorage.getItem('partner'))

			Vue.http.post(shop.routes.root+'/cart/change.js'+(partner?'?partnerId='+partner.kvps:''), params).then(response=>{

				store.commit('cart', response.body);

				response.body.items.forEach((i, item)=>{

					if( i === line ){

						let event = new CustomEvent("cart-change", {detail: item});
						document.dispatchEvent(event);
					}
				});

				resolve(response.body);

			}, reject=>{

				Vue.http.get(shop.routes.root+'/cart.js').then(response=>{

					store.commit('cart', response.body)
					reject();

				}, reject)
			})
		});
	},
};
