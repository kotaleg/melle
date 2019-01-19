import Vue from 'vue'
import { isUndefined, isEmpty } from 'lodash'

import shop from '../../api/shop'

// initial state
const state = {
    base: '',
    logo: '',
    phone: '',
    menu: [],

    sidebar_opened: false,
    elements: {
        mail_us: false,
        login: false,
        register: false,
        filter: false,
        cart: false,
    },
}

// getters
const getters = {
    isElementActive: state => index => {
        return state.elements[index]
    },
    phoneLink: state => {
        let phone = 'tel:'+state.phone
        return phone.replace(/\s/g,'')
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_header', data => {
            commit('setData', data)
        })
    },
    openSidebar({ commit, dispatch, state }, status) {
        commit('openSidebar', status)
        if (status === false) {
            dispatch('disableAllElements')
        }
    },
    menuHandler({ commit }, payload) {
        commit('setMenuItemStatus', payload)
    },
    enableElement({ commit, dispatch }, index) {
        dispatch('disableAllElements')
        commit('setElementStatus', {i:index, status: true})
        commit('openSidebar', true)
    },
    disableAllElements({ commit }) {
        for (let e in state.elements) {
            commit('setElementStatus', {i:e, status: false})
        }
    },
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    openSidebar(state, status) {
        Vue.set(state, 'sidebar_opened', status)
    },
    setMenuItemStatus(state, {i, status}) {
        Vue.set(state.menu[i], 'active', status)
    },
    setElementStatus(state, {i, status}) {
        Vue.set(state.elements, i, status)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
