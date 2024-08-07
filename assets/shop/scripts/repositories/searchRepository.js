import Vue from "vue";

export default {

	suggest(params) {

    if( params.q )
      params.query = params.q

		return new Promise((resolve, reject) => {

			Vue.http.get(shop.routes.root+'/search/suggest.json', {params:params}).then(response=>{

				resolve({products:response.body.items});

			}, reject)
		});
	}
};
