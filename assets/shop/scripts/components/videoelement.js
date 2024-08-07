import Vue from 'vue';

Vue.component('videoelement', {
	data(){
		return{
			overlay: false,
			soundMode: false,
			playMode: true
		}
	},
	props: ['poster', 'vimeo_id'],
	methods: {
		playVideo:function(){
			this.overlay = false;
		},

		soundControl() {
			const player = document.getElementById('videoPlayer');
			if(this.soundMode) {
				player.contentWindow.postMessage('{"method":"setVolume", "value":0}', '*');
				this.soundMode = !this.soundMode;
			}else {
				player.contentWindow.postMessage('{"method":"setVolume", "value":1}', '*');
				this.soundMode = !this.soundMode;
			}
		},

		playControl() {
			const player = document.getElementById('videoPlayer');
			if(this.playMode) {
				player.contentWindow.postMessage('{"method":"pause", "value":0}', '*');
				this.playMode = !this.playMode;
			}else {
				player.contentWindow.postMessage('{"method":"play", "value":1}', '*');
				this.playMode = !this.playMode;
			}
		}
	}
});