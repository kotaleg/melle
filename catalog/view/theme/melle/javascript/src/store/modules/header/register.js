import Vue from 'vue'
import { has } from 'lodash'

import shop from '@/api/shop'
import notify from '@/components/partial/notify'
import Errors from '@/components/partial/errors'

// initial state
const state = {
  form: {
    name: '',
    email: '',
    phone: '',
    password: '',
    confirm: '',
    birth: '',
    discount_card: '',
    newsletter: false,
    agree: false,
  },
  errors: new Errors(),
}

// getters
const getters = {
  getFormValue: (state) => (index) => {
    return state.form[index]
  },
  fieldHasError: (state) => (field) => {
    return state.errors.has(field)
  },
  getFieldError: (state) => (field) => {
    return state.errors.first(field)
  },
}

// actions
const actions = {
  updateFormValue({ commit }, payload) {
    commit('updateFormValue', payload)
  },
  registerRequest({ commit, state, rootState, dispatch }) {
    return new Promise((resolve, reject) => {
      commit('clearFormErrors')
      this.dispatch('header/setSidebarLoadingStatus', true)
      shop.makeRequest(
        {
          url: rootState.header.register_link,
          form: state.form,
        },
        (res) => {
          this.dispatch('header/setSidebarLoadingStatus', false)

          if (has(res.data, 'form_error')) {
            commit('setFormErrors', res.data.form_error)
          }

          if (has(res.data, 'redirect') && res.data.redirect !== false) {
            window.location = res.data.redirect
          }

          notify.messageHandler(res.data, '_sidebar')

          // RR START
          if (state.form.newsletter) {
            try {
              rrApi.setEmail(state.form.email)
            } catch (e) {}
          }
          // RR END
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
