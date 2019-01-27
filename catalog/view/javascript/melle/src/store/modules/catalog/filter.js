import Vue from 'vue'
import { isUndefined, has, forEach } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'

// initial state
const state = {
    filter_data: {
        min_den: null,
        max_den: null,
        min_price: null,
        max_price: null,
        hit: null,
        new: null,
        act: null,
        material: null,
        color: null,
        size: null,
        manufacturers: null,

        category_id: 0,
        search: null,

        page: 1,
        sort: 'p.sort_order',
        order: 'ASC',
    },
}

// getters
const getters = {
    getFilterValue: (state) => (index) => {
        return state.filter_data[index]
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_filter', data => {
            commit('setData', data)
        })
    },
    updateFilterData({ commit }, payload) {
        forEach(payload, (value, key) => {
            commit('updateFilterValue', {key, value})
        })
    },
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    updateFilterValue(state, {key, value}) {
        Vue.set(state.filter_data, key, value)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
