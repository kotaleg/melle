import Vue from 'vue'
import { isUndefined, has } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'
import Errors from '../../../components/partial/errors'

// initial state
const state = {
    form: {
        email: '',
    },
    errors: new Errors(),
}

// getters
const getters = {
    getFormValue: state => field => {
        return state.form[field]
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
    sendRequest({ commit, state, rootState, dispatch }) {
        return new Promise((resolve, reject) => {
            this.dispatch('header/setSidebarLoadingStatus', true)
            shop.makeRequest(
                {
                    url: rootState.header.forgotten_link,
                    email: state.form.email,
                },
                res => {
                    this.dispatch('header/setSidebarLoadingStatus', false)

                    if (has(res.data, 'redirect') && res.data.redirect !== false) {
                        window.location = res.data.redirect
                    }

                    notify.messageHandler(res.data, '_sidebar')

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
        state.form[k] = v
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
