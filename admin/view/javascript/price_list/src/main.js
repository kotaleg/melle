import Vue from 'vue'
import axios from 'axios'

import Notifications from 'vue-notification'
import ToggleButton from 'vue-js-toggle-button'

import store from './store'
import './plugins'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'price_list'

Vue.use(Notifications)
Vue.use(ToggleButton)

Vue.component(Vue.prototype.$codename, require('./components/App.vue'))

document.addEventListener("DOMContentLoaded", () => {
    new Vue({
        store,
        el: '#' + Vue.prototype.$codename + '-mount',
    })
})
