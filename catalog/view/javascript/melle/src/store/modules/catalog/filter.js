import Vue from 'vue'
import { isUndefined, isEqual, has, forEach } from 'lodash'

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

    last_filter: {},
}

// getters
const getters = {
    getFilterValue: state => index => {
        return state.filter_data[index]
    },
    isFilterChanged: state => {
        return !isEqual(state.filter_data, state.last_filter)
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_filter', data => {
            commit('setData', data)
        })
    },
    updateFilterValue({ commit }, payload) {
        commit('updateFilterValue', payload)
    },
    updateFilterData({ commit }, payload) {
        forEach(payload, (v, k) => {
            commit('updateFilterValue', {k, v})
        })
    },
    updateLastFilterData({ commit }, payload) {
        forEach(payload, (v, k) => {
            commit('updateLastFilterValue', {k, v})
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
    updateFilterValue(state, {k, v}) {
        Vue.set(state.filter_data, k, v)
    },
    updateLastFilterValue(state, {k, v}) {
        Vue.set(state.last_filter, k, v)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
