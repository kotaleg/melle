import Vue from 'vue'
import { has } from 'lodash'

import shop from '@/api/shop'
import notify from '@/components/partial/notify'
import Errors from '@/components/partial/errors'

// initial state
const state = {
  form: {
    email: '',
    password: '',
  },
  errors: new Errors(),
}

// getters
const getters = {
  getFormValue: (state) => (field) => {
    return state.form[field]
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
  loginRequest({ commit, state, rootState, dispatch }) {
    this.dispatch('header/setSidebarLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.header.login_link,
        email: state.form.email,
        password: state.form.password,
      },
      (res) => {
        this.dispatch('header/setSidebarLoadingStatus', false)

        if (has(res.data, 'redirect') && res.data.redirect !== false) {
          window.location = res.data.redirect
        }

        notify.messageHandler(res.data, '_sidebar')

        // RR START
        if (has(res.data, 'isNewsletter') && res.data.isNewsletter === true) {
          try {
            rrApi.setEmail(state.form.email)
            console.log(state.form.email)
          } catch (e) {}
        }
        // RR END
      }
    )
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
