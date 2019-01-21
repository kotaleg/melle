import 'es6-promise/auto'
import Vue from 'vue'
import Vuex from 'vuex'
import header from './modules/header'
import cart from './modules/cart'
import mail_us from './modules/mail_us'
import login from './modules/login'
import forgotten from './modules/forgotten'
import register from './modules/register'


Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    modules: {
        header,
        cart,
        login,
        mail_us,
        forgotten,
        register,
    },
    strict: debug,
})
