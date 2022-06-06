import Vue from 'vue';
import _ from 'lodash'
import Calculation from "./vue-components/calculation.vue";
import CalculationResult from "./vue-components/calculation-result.vue";
import {ApiController} from "./components/ApiController";
import VueNumericInput from 'vue-numeric-input'

require('./bootstrap');
require('./front')

window.API = new ApiController()
window.USER_ROLE = USER_ROLE

window.isFloat = n => {
    return Number(n) === n && n % 1 !== 0;
}

window.toNumRazr = x => {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

window.isMobile = () => screen.width <= 600

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.config.productionTip = false

Vue.component('calculation', Calculation)
Vue.component('calculation-result', CalculationResult)

Vue.use(VueNumericInput)

new Vue().$mount('#app')
