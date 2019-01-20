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
        forgotten: false,
    },

    is_logged: false,
    is_loading: false,
    is_sidebar_loading: false,

    login_link: '',
    logout_link: '',
    register_link: '',
    forgotten_link: '',
    account_link: '',
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
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    setSidebarLoadingStatus({ commit }, status) {
        commit('setSidebarLoadingStatus', status)
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
    enableElement({ commit, dispatch, state }, index) {
        if (state.elements[index] === true) { return }
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
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    setSidebarLoadingStatus(state, status) {
        Vue.set(state, 'is_sidebar_loading', status)
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
