import 'es6-promise/auto'
import Vue from 'vue'
import Vuex from 'vuex'
import header from './modules/header'
import cart from './modules/cart'
import mail_us from './modules/mail_us'


Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    modules: {
        cart,
        header,
        mail_us,
    },
    strict: debug,
})
