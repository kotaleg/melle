import Vue from 'vue'
import Router from 'vue-router'

import Catalog from './../components/catalog/Catalog.vue'

Vue.use(Router)
export default new Router({
    mode: 'history',
    routes: [
        { path: '*', component: Catalog, props: true },
    ]
})