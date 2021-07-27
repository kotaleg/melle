import Vue from 'vue'
import Notifications from 'vue-notification'
import VueTheMask from 'vue-the-mask'
import PrettyCheck from 'pretty-checkbox-vue/check'
import VModal from 'vue-js-modal'
import vSelect from 'vue-select'
import ZoomOnHover from "vue-zoom-on-hover"

import router from './router'
import filterHelper from './router/filterHelper'
import store from './store'
import './plugins'

router.beforeEach((to, from, next) => {
  filterHelper.initQuery(to, from)
  next()
})

Vue.config.productionTip = false
Vue.prototype.$codename = 'melle'

Vue.use(Notifications)
Vue.use(VueTheMask)
Vue.use(ZoomOnHover)
Vue.use(VModal, { dialog: true })
Vue.component('p-check', PrettyCheck)
Vue.component('v-select', vSelect)

Vue.directive('focus', {
  inserted: function (el) {
    el.focus()
  }
})

import Header from './components/header/Header.vue';
Vue.component('melle-header', Header)

import AccountEdit from './components/account/AccountEdit.vue';
Vue.component('melle-account-edit', AccountEdit)

import Product from './components/product/Product.vue';
Vue.component('melle-product', Product)

import ProductReview from './components/product/ProductReview.vue';
Vue.component('melle-product-review', ProductReview)

import ProductImages from './components/product/ProductImages.vue';
Vue.component('melle-product-images', ProductImages)

import Filter from './components/catalog/Filter.vue';
Vue.component('melle-catalog-filter', Filter)

import Catalog from './components/catalog/CatalogWithRouter.vue';
Vue.component('melle-catalog-content', Catalog)

import Sort from './components/catalog/Sort.vue';
Vue.component('melle-catalog-sort', Sort)

import SearchWrapper from './components/search/SearchWrapper.vue';
Vue.component('melle-search-wrapper', SearchWrapper)

import Leadhit from './components/leadhit/Leadhit.vue';
Vue.component('melle-leadhit', Leadhit)

document.addEventListener('DOMContentLoaded', () => {
  new Vue({
    router,
    store,
    el: '#melle-mount',
  })
})
