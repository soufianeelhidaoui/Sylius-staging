import Vue from "vue";

Vue.directive("collection", {
	bind(el, binding, vnode) {

		const store = vnode.context.$store;
		let href_origin = el.getAttribute('href');

		let updateUrl = function (){

			let vehicle = store.getters.vehicle();
			let version = store.getters.version();

			let append = version ? version : vehicle;

			if( append ){

				if( binding.value == null || binding.value.indexOf(append) >=0 )
				{
					el.classList.remove('hidden')

					if( href_origin.indexOf(append) === -1 ){

						let href = href_origin.split('?');
						href[0] += '/'+append;
						href = href.join('?');

						el.setAttribute('href', href)
					}
				}
				else{

					el.classList.add('hidden')
				}
			}
			else{

				el.classList.remove('hidden')
				el.setAttribute('href', href_origin)
			}
		}

		store.watch(store.getters.vehicle, updateUrl)
		store.watch(store.getters.version, updateUrl)

		updateUrl();
	}
});


Vue.directive("collection-reset", {
	bind(el, binding, vnode) {
		const store = vnode.context.$store;
		store.commit('version', false)
		store.commit('vehicle', false)
		store.commit('vehicleId', false)
	}
});

Vue.directive("language", {
	bind(el, binding, vnode) {
		el.querySelectorAll('.sn-language-switcher__locale').forEach(element=>{
			element.addEventListener("click", (e) => {
				el.querySelector('.sn-language-switcher__input').value = element.innerHTML;
				el.querySelector('form').submit();
			})
		})
	}
});

Vue.directive("sticky", {
	bind(el, binding, vnode) {

		let update = function (){

			if( placeholder.getBoundingClientRect().top <= 0 ){

				if( !el.classList.contains('sticky') ){

					el.classList.add('sticky')
					placeholder.style.height = el.getBoundingClientRect().height+'px';
				}
			}
			else{

				if( el.classList.contains('sticky') ){

					el.classList.remove('sticky')
					placeholder.style.height = '0px';
				}
			}

			vnode.context.$nextTick(() => {

				document.documentElement.style.setProperty('--'+binding.value+'-height', el.getBoundingClientRect().height+'px');
			})
		}

		let placeholder = document.createElement("div");
		placeholder.classList.add('sticky-placeholder');
		placeholder.classList.add('sticky-placeholder--'+binding.value);
		placeholder.style.height = '0px';

		vnode.context.$nextTick(() => {

			el.parentNode.insertBefore(placeholder, el);
		})

		window.addEventListener("resize", update)
		window.addEventListener("load", update)
		window.addEventListener("scroll", update)
	}
});

Vue.directive("scroll-to", {
	bind(el, binding, vnode) {
		el.addEventListener("click", () => {
				let top;

				if(binding.modifiers.next)
					top = document.querySelector(binding.value).offsetTop +  document.querySelector(binding.value).clientHeight
				else
					top = document.querySelector('#'+binding.value).offsetTop - document.querySelector('.s-header').clientHeight

				window.scrollTo({
					top: top,
					behavior: 'smooth'
				})
			},
			false
		);
	}
});

Vue.directive("toggle-active", {
	bind(el, binding, vnode) {
		el.addEventListener(
				"click",
				() => {

					let active = vnode.context.class_active;
					active = !active;
					vnode.context.class_active = active;

					if(binding.value){
						document.querySelectorAll(binding.value).forEach(item => {
							item.classList.remove("is-active")
						})
						el.classList.add("is-active")
					} else{
						if (!active) {
							el.classList.remove("is-active");
							el.classList.add("is-inactive");
						} else {
							el.classList.remove("is-inactive");
							el.classList.add("is-active");
						}
					}

				},
				false
		);
	}
});

Vue.directive("focus", {
	bind(el, binding, vnode) {
		window.setTimeout(function(){
			if(vnode.elm.value.length > 0)
				el.classList.add("focused");
		},200)

		el.addEventListener('focus', (e) => {
			el.classList.add("focused");
		});
		el.addEventListener('blur', (e) => {
			vnode.elm.dispatchEvent(new CustomEvent('input'));
			if(vnode.elm.value.length === 0){
				el.classList.remove("focused");
			}
		});

	}
});

Vue.directive("match", {
	bind(el, binding, vnode) {
		el.addEventListener('keydown', (e) => {
			let pattern = new RegExp(el.getAttribute('pattern'));
			if (!pattern.test(e.key) && e.key !== 'Backspace' && e.key !== 'Enter')
				e.preventDefault();
		});
	}
});

Vue.directive("collection-nav", {
	bind(el, binding, vnode) {
		if(window.innerWidth < 768){
			el.querySelectorAll('.sn-collection-nav__link.has-child').forEach(element=>{
				element.addEventListener("click", (e) => {
					console.log('click')
					e.preventDefault();
					if(el.querySelector('.is-open'))
						el.querySelector('.is-open').classList.remove('is-open')
					e.target.parentElement.classList.add('is-open')
				})
			})

			el.querySelectorAll('.sn-collection-nav__submenu-overlay').forEach(element=>{
				element.addEventListener("click", (e) => {
					el.querySelector('.is-open').classList.remove('is-open')
				})
			})

			document.addEventListener('scroll', (e) => {
				el.querySelectorAll('.sn-collection-nav__item.is-open').forEach(element=>{
					element.classList.remove('is-open')
				})
			})
		}
	}
});
