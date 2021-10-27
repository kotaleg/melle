import Vue from 'vue'
import { isUndefined, has } from 'lodash'

import shop from '@/api/shop'
import notify from '@/api/components/partial/notify'
import Errors from '@/api/components/partial/errors'

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
  },
  errors: new Errors(),

  edit_link: '',
  customerActivated: false,
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
  initData({ commit, state }) {
    shop.getInlineState('_account', (data) => {
      commit('setData', data)
    })
  },
  updateFormValue({ commit }, payload) {
    commit('updateFormValue', payload)
  },
  editRequest({ commit, state, rootState, dispatch }) {
    commit('clearFormErrors')
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: state.edit_link,
        form: state.form,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)

        if (has(res.data, 'form_error')) {
          commit('setFormErrors', res.data.form_error)
        }

        if (has(res.data, 'redirect') && res.data.redirect !== false) {
          window.location = res.data.redirect
        }

        notify.messageHandler(res.data, '_header')
      }
    )
  },
}

// mutations
const mutations = {
  setData(state, data) {
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
