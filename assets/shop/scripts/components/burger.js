import Vue from 'vue';

Vue.component('burger', {
	template: '<a class="c-burger" @click="open">' +
		'<span class="c-burger__icon"><i></i></span>' +
		'<span class="c-burger__text"><slot></slot></span>' +
		'</a>',
	methods:{
		open(){
			this.$open('nav-main')
		}
	}
});
