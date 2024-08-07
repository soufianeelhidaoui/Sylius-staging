import { ResizeObserver } from '@juggle/resize-observer';

import Vue from 'vue';

Vue.component('responsivePicture', {
	data(){
		return {
			src: false,
			sizes: {},
			observer: false,
			$img: false
		}
	},
	methods: {
		resize(){

			let width = this.$el.offsetWidth;
			let source = this.src;

			for (let size in this.sizes) {
				if( width < size )
					source = this.sizes[size]
			}

			if( this.$img.getAttribute('src') !== source ){

				this.$img.addEventListener('load', function (){
					window.dispatchEvent(new Event('resize'));
				})

				this.$img.setAttribute('src', source);
				this.$img.removeAttribute('data-src');
			}
		}
	},
	mounted() {
		let sources = this.$el.querySelectorAll('source');
		sources.forEach(source=>{
			if( source.media && source.media.indexOf('parent-width') !== -1 ){
				let size = parseInt(source.media.replace('(parent-width:','').replace('px)',''))
				this.sizes[size] = source.srcset;
				this.$el.removeChild(source)
			}
		})

		this.$img = this.$el.querySelector('img');

		if( this.$img.getAttribute('loading') === 'lazy' )
			this.src = this.$img.getAttribute('data-src');
		else
			this.src = this.$img.getAttribute('src');

		this.observer = new ResizeObserver(this.resize);
		this.observer.observe(this.$img);

		this.resize();
	},
	destroyed() {
		this.observer.unobserve(this.$img)
	}
});