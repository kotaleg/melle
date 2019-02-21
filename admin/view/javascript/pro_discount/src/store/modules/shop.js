import Vue from 'vue'
import { isUndefined, isEmpty, has } from 'lodash'

import shop from '../../api/shop'
import notify from '../../components/partial/notify'

// initial state
const state = {
    breadcrumbs: {},
    navigation: {},
    setting: {},

    id: '',
    route: '',
    token: '',
    version: '',

    heading_title: '',

    button_save_and_stay: '',
    button_save: '',
    button_cancel: '',

    text_edit: '',
    text_preparing: '',
    text_enabled: '',
    text_disabled: '',
    text_success: '',
    text_no_results: '',

    cancel: '',
    save: '',
    is_loading: false,
    is_edit: false,

    discounts: [],

    all_categories: [],
    all_manufacturers: [],
    all_products: [],
    all_customers: [],
    all_types: [],
    all_signs: [],

    discount: {},
}

// getters
const getters = {
    getToggleStates: (state) => {
        return {
            checked: state.text_enabled,
            unchecked: state.text_disabled,
        }
    },
    getSettingValue: (state) => (index) => {
        return state.setting[index]
    },
    getDiscountValue: (state) => (index) => {
        return state.discount[index]
    },
}

// actions
const actions = {
    initData({ commit }) {
        shop.getInlineState(data => {
            commit('setData', data)
        })
    },
    updateSetting({ commit }, payload) {
        commit('updateSetting', payload)
    },
    updateDiscountValue({ commit }, payload) {
        commit('updateDiscountValue', payload)
    },
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    setEditStatus({ commit }, status) {
        commit('setEditStatus', status)
    },
    getDiscounts({ commit, state }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.get_discounts,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'discounts')) {
                    commit('updateValue',
                        {k:'discounts', v:res.data.discounts})
                }
                notify.messageHandler(res.data)
            }
        )
    },
    editDiscount({ commit, state }, discount_id) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.get_discount,
                discount_id: discount_id,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'discount')) {
                    commit('updateValue',
                        {k:'discount', v:res.data.discount})
                    commit('setEditStatus', true)
                }
                if (has(res.data, 'all_categories')) {
                    commit('updateValue',
                        {k:'all_categories', v:res.data.all_categories})
                }
                if (has(res.data, 'all_manufacturers')) {
                    commit('updateValue',
                        {k:'all_manufacturers', v:res.data.all_manufacturers})
                }
                if (has(res.data, 'all_products')) {
                    commit('updateValue',
                        {k:'all_products', v:res.data.all_products})
                }
                if (has(res.data, 'all_customers')) {
                    commit('updateValue',
                        {k:'all_customers', v:res.data.all_customers})
                }
                notify.messageHandler(res.data)
            }
        )
    },
    saveDiscount({ commit, state, dispatch }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.save_discount,
                discount: state.discount,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'saved')
                && res.data.saved === true) {
                    commit('setEditStatus', true)
                    dispatch('getDiscounts')
                }
                notify.messageHandler(res.data)
            }
        )
    },
    removeDiscount({ commit, state, dispatch }, discount_id) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.remove_discount,
                discount_id: discount_id,
            },
            res => {
                commit('setLoadingStatus', false)
                notify.messageHandler(res.data)
                dispatch('getDiscounts')
            }
        )
    },
    flipDiscount({ commit, state, dispatch }, discount_id) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.flip_discount_status,
                discount_id: discount_id,
            },
            res => {
                commit('setLoadingStatus', false)
                notify.messageHandler(res.data)
                dispatch('getDiscounts')
            }
        )
    },

    searchManufacturersRequest({ commit, state, rootState, dispatch }, query) {
        return new Promise((resolve, reject) => {
            shop.getFromServer(
                {
                    url: state.get_manufacturers,
                    params: {q:query}
                },
                res => {
                    if (!isUndefined(res.manufacturers)) {
                        commit('updateValue',
                            {k:'all_manufacturers', v: res.manufacturers})
                    }
                    notify.messageHandler(res)
                    resolve(state.all_manufacturers)
                }
            )
        })
    },
    searchCategoriesRequest({ commit, state, rootState, dispatch }, query) {
        return new Promise((resolve, reject) => {
            shop.getFromServer(
                {
                    url: state.get_categories,
                    params: {q:query}
                },
                res => {
                    if (!isUndefined(res.categories)) {
                        commit('updateValue',
                            {k:'all_categories', v: res.categories})
                    }
                    notify.messageHandler(res)
                    resolve(state.all_categories)
                }
            )
        })
    },
    searchProductsRequest({ commit, state, rootState, dispatch }, query) {
        return new Promise((resolve, reject) => {
            shop.getFromServer(
                {
                    url: state.get_products,
                    params: {q:query}
                },
                res => {
                    if (!isUndefined(res.products)) {
                        commit('updateValue',
                            {k:'all_products', v: res.products})
                    }
                    notify.messageHandler(res)
                    resolve(state.all_products)
                }
            )
        })
    },
    searchCustomersRequest({ commit, state, rootState, dispatch }, query) {
        return new Promise((resolve, reject) => {
            shop.getFromServer(
                {
                    url: state.get_customers,
                    params: {q:query}
                },
                res => {
                    if (!isUndefined(res.customers)) {
                        commit('updateValue',
                            {k:'all_customers', v: res.customers})
                    }
                    notify.messageHandler(res)
                    resolve(state.all_customers)
                }
            )
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
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    setEditStatus(state, status) {
        Vue.set(state, 'is_edit', status)
    },
    updateSetting(state, {k, v}) {
        Vue.set(state.setting, k, v)
    },
    updateDiscountValue(state, {k, v}) {
        Vue.set(state.discount, k, v)
    },
    updateValue(state, {k, v}) {
        Vue.set(state, k, v)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}