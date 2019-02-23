import Vue from 'vue'
import axios from 'axios'

import Notifications from 'vue-notification'
import ToggleButton from 'vue-js-toggle-button'
import FileUpload from 'v-file-upload'

import store from './store'
import App from './components/App.vue'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'size_list'

Vue.use(Notifications)
Vue.use(ToggleButton)
Vue.use(FileUpload)

Vue.component(Vue.prototype.$codename, require('./components/App.vue'))
Vue.component(Vue.prototype.$codename + '_product', require('./components/Product.vue'))


document.addEventListener("DOMContentLoaded", () => {
    new Vue({
        store,
        el: '#' + Vue.prototype.$codename + '-mount',
    })
})
