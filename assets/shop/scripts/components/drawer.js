import Vue from 'vue';
import eventBus from "../services/event-bus";

Vue.component('drawer', {
	template: '<transition name="drawer">' +
		'<div class="c-drawer" :class="\'c-drawer--\'+position" v-if="show" ref="drawer">' +
		'<div class="c-drawer__content">' +
		'<a class="c-drawer__close icon_after-close" aria-label="close" @click="$close(type)"><span>Fermer</span></a>' +
		'<div class="c-drawer__title">{{ title }}</div>' +
		'<div class="c-drawer__element"><slot></slot></div>' +
		'</div>' +
		'<a class="c-drawer__outside" @click="$close(type)"></a>' +
		'</div>' +
		'</transition>',
	data(){
		return{
			show: false
		}
	},
	props:['title','position','type'],
	methods:{
		close(){
			this.show = false
		},
		open(){
			this.show = true
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
