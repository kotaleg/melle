import Vue from 'vue'
import { isUndefined, isEmpty } from 'lodash'

import shop from '../../api/shop'

// initial state
const state = {
    form: {
        name: {
            value: '',
            error: [],
        },
        email: {
            value: '',
            error: [],
        },
        phone: {
            value: '',
            error: [],
        },
        message: {
            value: '',
            error: [],
        },
        captcha: {
            value: false,
            error: [],
        },
        agree: {
            value: false,
            error: [],
        },
    },
}

// getters
const getters = {
    getFormValue: state => index => {
        return state.form[index]
    },
}

// actions
const actions = {
    updateFormValue({ commit }, payload) {
        commit('updateFormValue', payload)
    },
}

// mutations
const mutations = {
    updateFormValue(state, { k, v }) {
        Vue.set(state.form, k, v)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
