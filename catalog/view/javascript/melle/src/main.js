import Vue from 'vue'
import axios from 'axios'
import Notifications from 'vue-notification'
import vClickOutside from 'v-click-outside'
import VueTheMask from 'vue-the-mask'
import PrettyCheck from 'pretty-checkbox-vue/check'
import VTooltip from 'v-tooltip'
import VModal from 'vue-js-modal'
import vSelect from 'vue-select'

import store from './store'

Vue.config.productionTip = false
Vue.prototype.$http = axios
Vue.prototype.$codename = 'melle'

Vue.use(Notifications)
Vue.use(vClickOutside)
Vue.use(VueTheMask)
Vue.use(VTooltip)
Vue.use(VModal, { dialog: true })
Vue.component('p-check', PrettyCheck)
Vue.component('v-select', vSelect)

Vue.component('melle-header', require('./components/header/Header.vue'))
Vue.component('melle-account-edit', require('./components/account/AccountEdit.vue'))
Vue.component('melle-product', require('./components/product/Product.vue'))
Vue.component('melle-product-review', require('./components/product/ProductReview.vue'))
Vue.component('melle-catalog-filter', require('./components/catalog/Filter.vue'))
Vue.component('melle-catalog-content', require('./components/catalog/Catalog.vue'))
Vue.component('melle-catalog-sort', require('./components/catalog/Sort.vue'))
Vue.component('melle-search-form', require('./components/catalog/SearchForm.vue'))


document.addEventListener('DOMContentLoaded', () => {
    new Vue({
        store,
        el: '#' + Vue.prototype.$codename + '-mount',
    })
})
