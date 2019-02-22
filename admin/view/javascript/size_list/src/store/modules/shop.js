import Vue from 'vue'
import { isUndefined, isEmpty, has, clone } from 'lodash'

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

    items: [],
    item: {},

    product_id: '',
    size_shit: [],
    product_item: '',
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
    getItemValue: (state) => (index) => {
        return state.item[index]
    },
    getValue: (state) => (index) => {
        return state[index]
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
    updateItemValue({ commit }, payload) {
        commit('updateItemValue', payload)
    },
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    setEditStatus({ commit }, status) {
        commit('setEditStatus', status)
    },
    updateValue({ commit, dispatch }, payload) {
        commit('updateValue', payload)
        dispatch('updateProduct')
    },
    getItems({ commit, state }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.get_items,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'items')) {
                    commit('updateValue',
                        {k:'items', v:res.data.items})
                }
                notify.messageHandler(res.data)
            }
        )
    },
    editItem({ commit, state }, image_id) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.get_item,
                image_id: image_id,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'item')) {
                    commit('updateValue',
                        {k:'item', v:res.data.item})
                    commit('setEditStatus', true)
                }
                notify.messageHandler(res.data)
            }
        )
    },
    saveItem({ commit, state, dispatch }, obj) {

        let item = clone(state.item)

        if (has(obj, 'image')) {
            item['image'] = obj['image']
        }
        if (has(obj, 'image_original')) {
            item['image_original'] = obj['image_original']
        }

        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.save_item,
                item,
            },
            res => {
                commit('setLoadingStatus', false)
                if (has(res.data, 'saved')
                && res.data.saved === true) {
                    commit('setEditStatus', true)
                    dispatch('getItems')
                }
                notify.messageHandler(res.data)
            }
        )
    },
    removeItem({ commit, state, dispatch }, image_id) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.remove_item,
                image_id: image_id,
            },
            res => {
                commit('setLoadingStatus', false)
                notify.messageHandler(res.data)
                dispatch('getItems')
            }
        )
    },

    searchShitRequest({ commit, state, rootState, dispatch }, query) {
        return new Promise((resolve, reject) => {
            shop.getFromServer(
                {
                    url: state.get_size_shit,
                    params: {q:query, product_id}
                },
                res => {
                    if (!isUndefined(res.size_shit)) {
                        commit('updateValue',
                            {k:'size_shit', v: res.size_shit})
                    }
                    notify.messageHandler(res)
                    resolve(state.size_shit)
                }
            )
        })
    },
    updateProduct({ commit, state, dispatch }) {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.update_product_data,
                selected: state.product_item,
                product_id: state.product_id,
            },
            res => {
                commit('setLoadingStatus', false)
                notify.messageHandler(res.data)
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
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    setEditStatus(state, status) {
        Vue.set(state, 'is_edit', status)
    },
    updateSetting(state, {k, v}) {
        Vue.set(state.setting, k, v)
    },
    updateItemValue(state, {k, v}) {
        Vue.set(state.item, k, v)
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