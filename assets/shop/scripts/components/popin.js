import Vue from 'vue';
import eventBus from "../services/event-bus";

Vue.component('popin', {
	template: '<transition name="fade">' +
		'<div class="c-popin" v-if="show">' +
		'<div class="c-popin__content">' +
		'<a class="c-popin__close icon-close" aria-label="close" @click="$close(type)"></a>' +
		'<slot></slot>' +
		'</div>' +
		'</div>' +
		'</transition>',
	data(){
		return{
			show: false,
			args:false
		}
	},
	props:['type'],
	methods:{
		close(){
			this.show = false
		},
		open(e){
			this.args = e;
			this.show = true;
		},
		toggle(){
			this.show = !this.show
		}
	},
	mounted() {
		eventBus.$on('open-'+this.type, this.open)
		eventBus.$on('close-'+this.type, this.close)
		eventBus.$on('toggle-'+this.type, this.toggle)
	}
});
