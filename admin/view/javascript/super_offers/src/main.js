import Vue from 'vue'
import axios from 'axios'

import Notifications from 'vue-notification'
import ToggleButton from 'vue-js-toggle-button'
import VModal from 'vue-js-modal'

import store from './store'
import App from './components/App.vue'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'super_offers'

Vue.use(Notifications)
Vue.use(ToggleButton)
Vue.use(VModal, { dynamic: true })

document.addEventListener("DOMContentLoaded", () => {
    new Vue({
        store,
        render: h => h(App)
    }).$mount('#'+Vue.prototype.$codename)
})

