import Vue from 'vue'
import Notifications from 'vue-notification'
import VueTheMask from 'vue-the-mask'
import PrettyCheck from 'pretty-checkbox-vue/check'
import VTooltip from 'v-tooltip'
import VModal from 'vue-js-modal'
import vSelect from 'vue-select'
import 'vue-loading-overlay/dist/vue-loading.min.css'

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
Vue.use(VTooltip)
Vue.use(VModal, { dialog: true })
Vue.component('p-check', PrettyCheck)
Vue.component('v-select', vSelect)

Vue.component('melle-header', require('./components/header/Header.vue'))
Vue.component(
  'melle-account-edit',
  require('./components/account/AccountEdit.vue')
)
Vue.component('melle-product', require('./components/product/Product.vue'))
Vue.component(
  'melle-product-review',
  require('./components/product/ProductReview.vue')
)
Vue.component(
  'melle-catalog-filter',
  require('./components/catalog/Filter.vue')
)
Vue.component(
  'melle-catalog-content',
  require('./components/catalog/CatalogWithRouter.vue')
)
Vue.component('melle-catalog-sort', require('./components/catalog/Sort.vue'))
Vue.component(
  'melle-search-wrapper',
  require('./components/search/SearchWrapper.vue')
)
// Vue.component(
//   'melle-checkout-rp',
//   require('./components/checkout/RelatedProducts.vue')
// )
// Vue.component('melle-leadhit', require('./components/leadhit/Leadhit.vue'))
// Vue.component(
//   'melle-slideshow',
//   require('./components/slideshow/slideshow.vue')
// )

document.addEventListener('DOMContentLoaded', () => {
  new Vue({
    router,
    store,
    el: '#' + Vue.prototype.$codename + '-mount',
  })

  // if (document.getElementById(Vue.prototype.$codename + '-checkout-bottom')) {
  //   new Vue({
  //     store,
  //     el: '#' + Vue.prototype.$codename + '-checkout-bottom',
  //   })
  // }
})
