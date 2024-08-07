import Vue from "vue";
import {Mixins} from './mixins'

Vue.filter('money', function (cents, format) {

	return Mixins.methods.formatMoney(cents, format)
})