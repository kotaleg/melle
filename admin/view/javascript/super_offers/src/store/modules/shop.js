import Vue from 'vue'
import { isUndefined, isNaN, isArray, isEmpty,  forEach } from 'lodash'

import shop from '../../api/shop'

// initial state
const state = {
    setting: {},

    id: '',
    route: '',
    token: '',
    version: '',

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

    options: [],
    option_values: [],
    combinations: [],
    combinations_data: [],
    active_columns: [],
    default_active_columns: {},
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
    getNextCombinationIndex: (state) => {
        let index = 0
        forEach(state.combinations, (element, i) => {
            let cleared = parseInt(i);
            if (!isNaN(cleared) && (cleared >= index)) {
                index = cleared
            }
        })
        if (!isEmpty(state.combinations)) {
            index++
        }
        return index
    },
    getDefaultCombination: (state) => {
        let combination = {}
        forEach(state.options, (element, i) => {
            let pov = false
            if (isArray(element.product_option_value)) {
                let first_val = Object.keys(element.product_option_value)[0]
                if (!isUndefined(first_val)) {
                    pov = first_val
                }
            }
            combination[i] = pov
        })
        return combination
    },
    getDefaultCombinationData: (state) => {
        let combination_data = {}
        forEach(state.default_active_columns, (element, i) => {
            if (element.active) {
                combination_data[i] = element.default
            }
        })
        return combination_data
    },

    getCombinationDataValue: (state) => (combination_id, key) => {
        return state.combinations_data[combination_id][key]
    },
}

// actions
const actions = {
    initData({ commit }) {
        shop.getInlineState(data => {
            commit('setData', data)
        })
    },
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    addCombination({ commit, getters }) {
        let key = getters.getNextCombinationIndex
        let c = getters.getDefaultCombination
        let cd = getters.getDefaultCombinationData

        console.log('KEY: '+key);
        // console.log(c);
        // console.log(cd);

        commit('addCombination', {key, value: c})
        commit('addCombinationData', {key, value: cd})
    },
    updateCombinationActiveOptionCodename({ commit }, payload) {
        commit('updateCombinationActiveOptionCodename', {
            combination_id: payload.combid,
            active_option_id: payload.acOptionId,
            codename: payload.codename,
        })
    },
    updateCombinationValue({ commit }, payload) {
        commit('updateCombinationValue', payload)
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
    addCombination(state, {key, value}) {
        Vue.set(state.combinations, key, value)
    },
    addCombinationData(state, {key, value}) {
        Vue.set(state.combinations_data, key, value)
    },
    updateCombinationActiveOptionCodename(state, {combination_id, active_option_id, codename}) {
        Vue.set(state.combinations[combination_id], active_option_id, codename)
    },
    updateCombinationValue(state, {combination_id, key, value}) {
        Vue.set(state.combinations_data[combination_id], key, value)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}