import Vue from 'vue'
import axios from 'axios'
import Notifications from 'vue-notification'
import vClickOutside from 'v-click-outside'

import store from './store'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'melle'

Vue.use(Notifications)
Vue.use(vClickOutside)

Vue.component('melle-header', require('./components/header/Header.vue'))

document.addEventListener('DOMContentLoaded', () => {
    new Vue({
        store,
        el: '#' + Vue.prototype.$codename + '-mount',
    })
})
