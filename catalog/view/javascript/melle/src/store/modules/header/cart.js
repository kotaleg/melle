import Vue from 'vue'
import { isUndefined } from 'lodash'

import shop from '../../../api/shop'

// initial state
const state = {
    'count': 0,
    'products': [],
    'total': 0,
    'totals': [],

    'catalog_link': '',
    'cart_link': '',
    'checkout_link': '',
}

// getters
const getters = {
    hasProducts: state => {
        return state.count > 0 ? true : false
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_cart', data => {
            commit('setData', data)
        })
    },

    updateDataRequest({ commit }) {

    },
}

// mutations
const mutations = {
    setData (state, data) {
        console.log(data);
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
