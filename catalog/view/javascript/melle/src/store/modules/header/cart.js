import Vue from 'vue'
import { isUndefined, has, clone } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'

// initial state
const state = {
    'count': 0,
    'products': [],
    'total': 0,
    'totals': [],

    'is_checkout': false,

    'catalog_link': '',
    'checkout_link': '',
}

// getters
const getters = {
    hasProducts: state => {
        return state.count > 0 ? true : false
    },
    getProductsForGTM: state => {
        let products = []
        state.products.forEach((item) => {
            products.push({ 
                'id': item.product_id,
                'name': item.name,
                'brand': item.manufacturer,
                'price': item.price,
                'quantity': item.quantity,
            })
        })
        return products
    },
    getProductForGTM: state => cart_id => {
        let product = {}
        state.products.forEach((item) => {
            if (item.cart_id === cart_id) {
                product = {
                    'id': item.product_id,
                    'name': item.name,
                    'brand': item.manufacturer,
                    'price': item.price,
                    'quantity': item.quantity,
                }
            }
        })
        return product
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_cart', data => {
            commit('setData', data)
        })
    },

    updateCartDataRequest({ commit, state, rootState, dispatch, getters }) {
        this.dispatch('header/setSidebarLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.get_data,
            },
            res => {
                this.dispatch('header/setSidebarLoadingStatus', false)

                if (has(res.data, 'count')) {
                    commit('setCount', res.data.count)
                }

                if (has(res.data, 'total')) {
                    commit('setTotal', res.data.total)
                }

                if (has(res.data, 'totals')) {
                    commit('setTotals', res.data.totals)
                }

                if (has(res.data, 'products')) {
                    commit('setProducts', res.data.products)
                }

                notify.messageHandler(res.data, '_header')
            }
        )
    },

    clearCartRequest({ commit, state, rootState, dispatch, getters }) {
        this.dispatch('header/setSidebarLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.clear,
            },
            res => {
                this.dispatch('header/setSidebarLoadingStatus', false)
                notify.messageHandler(res.data, '_header')

                let removed_items = clone(getters.getProductsForGTM)

                dispatch('updateCartDataRequest')

                if (has(res.data, 'cleared') && res.data.cleared === true) {
                    // GTM
                    this.dispatch('gtm/removeFromCart', removed_items)
                }

                if (state.is_checkout === true) {
                    window.location = state.checkout_link
                }
            }
        )
    },

    updateCartItemRequest({ commit, state, rootState, dispatch, getters }, payload) {
        this.dispatch('header/setSidebarLoadingStatus', true)

        let quantity_data = {}
        quantity_data[payload.cart_id] = payload.quantity

        shop.makeRequest(
            {
                url: state.update,
                quantity_data,
            },
            res => {
                this.dispatch('header/setSidebarLoadingStatus', false)
                notify.messageHandler(res.data, '_header')

                dispatch('updateCartDataRequest')

                if (state.is_checkout === true) {
                    window.location = state.checkout_link
                }
            }
        )
    },

    removeCartItemRequest({ commit, state, rootState, dispatch, getters }, cart_id) {
        this.dispatch('header/setSidebarLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.remove,
                cart_id,
            },
            res => {
                this.dispatch('header/setSidebarLoadingStatus', false)
                notify.messageHandler(res.data, '_header')

                let removed_item = clone(getters.getProductForGTM(cart_id))

                dispatch('updateCartDataRequest')

                if (has(res.data, 'removed') && res.data.removed === true) {
                    // GTM
                    this.dispatch('gtm/removeFromCart', [removed_item])
                }

                if (state.is_checkout === true) {
                    window.location = state.checkout_link
                }
            }
        )
    },

}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    setCount(state, count) {
        Vue.set(state, 'count', count)
    },
    setTotal(state, total) {
        Vue.set(state, 'total', total)
    },
    setTotals(state, totals) {
        Vue.set(state, 'totals', totals)
    },
    setProducts(state, products) {
        Vue.set(state, 'products', products)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
