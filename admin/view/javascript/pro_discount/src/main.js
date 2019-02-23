import Vue from 'vue'
import axios from 'axios'

import Notifications from 'vue-notification'
import ToggleButton from 'vue-js-toggle-button'

import store from './store'
import App from './components/App.vue'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'pro_discount'

Vue.use(Notifications)
Vue.use(ToggleButton)

document.addEventListener("DOMContentLoaded", () => {
    new Vue({
        store,
        render: h => h(App)
    }).$mount('#'+Vue.prototype.$codename)
})

