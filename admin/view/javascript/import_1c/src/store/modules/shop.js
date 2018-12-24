import shop from '../../api/shop'
import {isUndefined} from 'lodash'

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

    cancel: '',
    save: '',
    somethingLoading: false,
    loading_progress: 0,
    loading_message: '',
}

// getters
const getters = {
    getToggleStates: (state) => {
        return {
            checked: state.text_enabled,
            unchecked: state.text_disabled
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
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            state[d] = data[d];
        }
    },
    updateSetting(state, {index, value}) {
        state.setting[index] = value
    },
    setLoadingStatus(state, status) {
        state.somethingLoading = status
    },
    setLoadingProgress(state, progress_data) {
        state.loading_progress = progress_data.progress
        state.loading_message = progress_data.message
    },
    clearLoadingProgress(state) {
        state.loading_progress = 0
        state.loading_message = ''
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}