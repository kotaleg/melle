import Vue from 'vue'
import { isArray, has } from 'lodash'

import shop from '@/api/shop'

// initial state
const state = {
  initialised: false,
  lead_uid: '',
  site_id: '',
  base_url: '',
  productsContainer: {},
}

// getters
const getters = {}

// actions
const actions = {
  initData({ commit, state }) {
    if (state.initialised === true) {
      return
    }
    shop.getInlineState('_leadhit', (data) => {
      commit('setData', data)
      commit('setValue', { k: 'initialised', v: true })
    })
  },

  async getProductsRequest({ state }, serviceName) {
    return new Promise((resolve, reject) => {
      shop.makeGetRequest(
        {
          url: state.base_url,
          lead_uid: state.lead_uid,
          clid: state.site_id,
          service_name: serviceName,
          offer_url: window.location.href,
        },
        (res) => {
          resolve(res)
        }
      )
    })
  },

  async getProductsSliceFor({ commit, dispatch }, payload) {
    const res = await dispatch('getProductsRequest', payload.serviceName).catch(
      (error) => {
        if (has(error.response, 'data')) {
          console.log(error.response.data)
        }
      }
    )

    if (res && has(res, 'data') && isArray(res.data)) {
      commit('setProductsFor', {
        id: payload.id,
        products: res.data.slice(0, 6),
      })
    }
  },
}

// mutations
const mutations = {
  setData(state, data) {
    for (let d in data) {
      Vue.set(state, d, data[d])
    }
  },
  setValue(state, { k, v }) {
    Vue.set(state, k, v)
  },
  setProductsFor(state, { id, products }) {
    Vue.set(state.productsContainer, id, products)
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
