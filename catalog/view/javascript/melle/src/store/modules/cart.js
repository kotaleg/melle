import Vue from 'vue'
import { isUndefined, isEmpty } from 'lodash'

import shop from '../../api/shop'

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

}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_header', data => {
            commit('setData', data)
        })
    },
    hasProducts: state => {
        return this.count > 0 ? true : false
    },
}

// mutations
const mutations = {
    setData (state, data) {
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
