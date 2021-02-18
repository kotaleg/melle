import Vue from 'vue'
import Router from 'vue-router'

import CatalogFull from '@/components/catalog/CatalogFull.vue'
import ProductFull from '@/components/product/ProductFull.vue'

Vue.use(Router)

export default new Router({
  mode: 'history',
  routes: [
    {path: '/product/:productId', component: ProductFull, props: true },
    {path: '*', component: CatalogFull, props: true },
  ],
  scrollBehavior (to, from, savedPosition) {
    if (to.params.productId != undefined) {
      return { x: 0, y: 0 }
    }

    return savedPosition
  }
})
