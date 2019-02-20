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

    button_save_and_stay: '',
    button_save: '',
    button_cancel: '',

    text_edit: '',
    text_preparing: '',
    text_enabled: '',
    text_disabled: '',
    text_success: '',

    cancel: '',
    save: '',
    is_loading: false,

    sales: [],
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
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    fetchImports({ commit }) {
        commit('setUpdateStatus', true)
        shop.makeRequest(
            {
                url: state.get_running_imports,
            },
            res => {
                commit('setUpdateStatus', false)
                if (!isUndefined(res.data.imports)) {
                    commit('updateImports', res.data.imports)
                }
                notify.messageHandler(res.data)
            }
        )
    },
    importSEOData({ commit }) {
        commit('setUpdateStatus', true)
        shop.makeRequest(
            {
                url: state.import_seo_data,
            },
            res => {
                commit('setUpdateStatus', false)
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
    setUpdateStatus(state, status) {
        Vue.set(state, 'is_updating', status)
    },
    updateSetting(state, {index, value}) {
        Vue.set(state.setting, index, value)
    },
    updateImports(state, imports) {
        Vue.set(state, 'imports', imports)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}