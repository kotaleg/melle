import Vue from 'vue'
import { isUndefined, isInteger, isEmpty, isArray, has, clone } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'
import Errors from '../../../components/partial/errors'

// initial state
const state = {
    form: {
        name: '',
        email: '',
        message: '',
        rating: 5,
    },
    errors: new Errors(),

    review_link: '',
    product_id: 0,
}

// getters
const getters = {
    getStateValue: state => index => {
        return state[index]
    },
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
    initData({ commit, state }) {
        shop.getInlineState('_review', data => {
            commit('setData', data)
        })
    },
    updateFormValue({ commit }, payload) {
        commit('updateFormValue', payload)
    },
    addReviewRequest({ commit, state, rootGetters, dispatch }) {
        this.dispatch('header/setLoadingStatus', true)

        return new Promise((resolve, reject) => {
            commit('clearFormErrors')

            let form = clone(state.form)
            form['product_id'] = state.product_id
            form['options'] = rootGetters['product/getActiveOptions']

            shop.makeRequest(
                {
                    url: state.review_link,
                    form,
                },
                res => {
                    this.dispatch('header/setLoadingStatus', false)
                    notify.messageHandler(res.data, '_header')

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
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
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
