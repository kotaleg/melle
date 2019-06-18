import Vue from 'vue'
import Vuex from 'vuex'

// HEADER
import header from './modules/header/header'
import cart from './modules/header/cart'
import mail_us from './modules/header/mail_us'
import login from './modules/header/login'
import forgotten from './modules/header/forgotten'
import register from './modules/header/register'
import gtm from './modules/header/gtm'

// ACCOUNT
import account from './modules/account/account'

// PRODUCT
import product from './modules/product/product'
import review from './modules/product/review'

// CATALOG
import catalog from './modules/catalog/catalog'
import filter from './modules/catalog/filter'

// CHECKOUT
import checkoutrp from './modules/checkout/related'

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
        gtm,
        account,
        product,
        review,
        catalog,
        filter,
        checkoutrp,
    },
    strict: debug,
})
