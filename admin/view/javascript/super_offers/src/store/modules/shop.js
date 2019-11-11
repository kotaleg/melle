import Vue from 'vue'
import { isNil, isArray, isEmpty,  forEach, clone } from 'lodash'

import shop from '../../api/shop'

// initial state
const state = {
    setting: {},

    id: '',
    route: '',
    token: '',
    version: '',

    is_loading: false,

    options: [],
    option_values: [],
    combinations: [],
    combinations_data: [],
    active_columns: [],
    default_active_columns: {},
    product_images: [],
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
            if (!isNil(cleared) && (cleared >= index)) {
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
                if (!isNil(first_val)) {
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

    isCombinations: (state) => {
        let status = false
        if (isArray(state.combinations) && !isEmpty(state.combinations)) {
            state.combinations.forEach((element, i) => {
                if (!isNil(element)) {
                    status = true
                }
            })
        }
        return status
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
        let c = clone(getters.getDefaultCombination)
        let cd = clone(getters.getDefaultCombinationData)

        commit('addCombination', {key, value: c})
        commit('addCombinationData', {key, value: cd})

        console.log('CREATED WITH KEY: '+key);
        // console.log(c);
        // console.log(cd);
    },
    deleteCombinationAndData({ commit, getters }, id) {
        commit('deleteCombination', id)
        commit('deleteCombinationData', id)

        console.log('DELETE: '+id);
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

    fetchCombinations({ commit }) {

    },
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
        console.log(data);
    },
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    addCombination(state, {key, value}) {
        Vue.set(state.combinations, key, value)
    },
    deleteCombination(state, id) {
        delete state.combinations[id]
        Vue.delete(state.combinations, id)
    },
    deleteCombinationData(state, id) {
        delete state.combinations_data[id]
        Vue.delete(state.combinations_data, id)
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