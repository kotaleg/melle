import Vue from 'vue'
import { isUndefined, has } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'
import Errors from '../../../components/partial/errors'

// initial state
const state = {
    form: {
        name: '',
        email: '',
        phone: '',
        message: '',
        agree: false,
    },
    errors: new Errors(),
}

// getters
const getters = {
    getFormValue: state => index => {
        return state.form[index]
    },
    fieldHasError: state => field => {
        return state.errors.has(field)
    },
    getFieldError: state => field => {
        return state.errors.first(field)
    },
}

// actions
const actions = {
    updateFormValue({ commit }, payload) {
        commit('updateFormValue', payload)
    },
    mailUsRequest({ commit, state, rootState, dispatch }) {
        return new Promise((resolve, reject) => {
            commit('clearFormErrors')
            this.dispatch('header/setSidebarLoadingStatus', true)
            shop.makeRequest(
                {
                    url: rootState.header.mail_us_link,
                    form: state.form,
                },
                res => {
                    this.dispatch('header/setSidebarLoadingStatus', false)
                    notify.messageHandler(res.data, '_sidebar')

                    if (has(res.data, 'form_error')) {
                        commit('setFormErrors', res.data.form_error)
                    }

                    if (has(res.data, 'sent') && res.data.sent === true) {
                        resolve(true)
                    }
                }
            )
        })
    },
}

// mutations
const mutations = {
    updateFormValue(state, { k, v }) {
        Vue.set(state.form, k, v)
        state.errors.clear(k)
    },
    clearFormErrors(state) {
        state.errors.clear()
    },
    setFormErrors(state, errors) {
        state.errors.record(errors)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
