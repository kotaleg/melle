import 'es6-promise/auto'
import Vue from 'vue'
import Vuex from 'vuex'
import shop from './modules/shop'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    modules: {
        shop,
    },
    strict: debug,
})