import Vue from 'vue'
import { isUndefined, isEmpty } from 'lodash'

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

    text_edit: '',
    text_warning: '',
    text_success: '',
    text_enabled: '',
    text_disabled: '',

    button_save_and_stay: '',
    button_save: '',
    button_cancel: '',

    cancel: '',
    save: '',

    is_loading: false,
    is_updating: false,
    is_products_updating: false,
    is_store_products_updating: false,

    loading_progress: 0,
    loading_message: '',

    tables: [],
}

// getters
const getters = {
    getToggleStates: state => {
        return {
            checked: state.text_enabled,
            unchecked: state.text_disabled,
        }
    },
    getValue: state => index => {
        return state[index]
    },
    getSettingValue: state => index => {
        return state.setting[index]
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState(data => {
            commit('setData', data)
        })
    },
    updateValue({ commit }, payload) {
        commit('updateValue', payload)
    },
    updateSetting({ commit }, payload) {
        commit('updateSetting', payload)
    },
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    setUpdateStatus({ commit }, status) {
        commit('setUpdateStatus', status)
    },
    setProductsUpdateStatus({ commit }, status) {
        commit('setProductsUpdateStatus', status)
    },
    setStoreProductsUpdateStatus({ commit }, status) {
        commit('setStoreProductsUpdateStatus', status)
    },

    checkBeforeTablesUpdate({ commit, state, rootState, dispatch }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.is_credentials_valid,
            },
            res => {
                commit('setLoadingStatus', false)

                if (!isUndefined(res.data.valid) && res.data.valid == true) {
                    if (!isUndefined(res.data.tables)) {
                        dispatch('updateValue', {key:'tables', value: res.data.tables})
                    }
                    Vue.prototype.$modal.show('sync-modal', {});
                    dispatch('updateTablesRequest')
                }

                notify.messageHandler(res.data)
            }
        )
    },

    updateTablesRequest({ commit, state, rootState, dispatch }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.update_big_buy,
            },
            res => {
                if (isUndefined(res.data.continue) || res.data.continue == false) {
                    commit('setLoadingStatus', false)
                    Vue.prototype.$modal.hide('sync-modal', {});

                    setTimeout(() => {
                        window.location.reload(false)
                    }, 1500)
                } else {
                    commit('setLoadingStatus', true)
                    if (!isUndefined(res.data.tables)) {
                        dispatch('updateValue', {key:'tables', value: res.data.tables})
                    }
                    dispatch('updateTablesRequest')
                }

                notify.messageHandler(res.data)
            }
        )
    },

    updateStoreProductsRequest({ commit, state, rootState, dispatch }) {
        commit('setProductsUpdateStatus', true)
        commit('setStoreProductsUpdateStatus', true)
        shop.makeRequest(
            {
                url: state.update_store_products,
            },
            res => {
                if (isUndefined(res.data.continue) || res.data.continue == false) {
                    commit('setProductsUpdateStatus', false)
                    commit('setStoreProductsUpdateStatus', false)
                } else {
                    commit('setProductsUpdateStatus', true)
                    commit('setStoreProductsUpdateStatus', true)
                    if (!isUndefined(res.data.tables)) {
                        dispatch('updateValue', {key:'tables', value: res.data.tables})
                    }
                    dispatch('updateValue', {key:'loading_progress', value: res.data.loading_progress})
                    dispatch('updateValue', {key:'loading_message', value: res.data.loading_message})
                    dispatch('updateStoreProductsRequest')
                }

                if (!isUndefined(res.data.should_get) && res.data.should_get == true) {
                    this.dispatch('products/getProductsDataThrottled')
                }

                notify.messageHandler(res.data)
            }
        )
    },

    cancelUpdateRequest({ commit, state, rootState, dispatch }) {
        shop.makeRequest(
            {
                url: state.cancel_big_buy_update,
            },
            res => {
                notify.messageHandler(res.data)
            }
        )
    },
}

// mutations
const mutations = {
    setData(state, data) {
        for (let d in data) {
            state[d] = data[d]
        }
    },
    updateValue(state, { key, value }) {
        Vue.set(state, key, value)
    },
    updateSetting(state, { key, value }) {
        Vue.set(state.setting, key, value)
    },
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    setUpdateStatus(state, status) {
        Vue.set(state, 'is_updating', status)
    },
    setProductsUpdateStatus(state, status) {
        Vue.set(state, 'is_products_updating', status)
    },
    setStoreProductsUpdateStatus(state, status) {
        Vue.set(state, 'is_store_products_updating', status)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
