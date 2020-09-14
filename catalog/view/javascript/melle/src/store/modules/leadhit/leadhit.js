import Vue from 'vue'
import { isArray, has } from 'lodash'

import shop from '../../../api/shop'

// initial state
const state = {
  initialised: false,
  lead_uid: '',
  site_id: '',
  base_url: '',

  type_hits: '',
  type_recommend: '',

  hits: [],
  recommend: [],
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

  async getProductsRequest({ commit, state }, service_name) {
    return new Promise((resolve, reject) => {
      shop.makeGetRequest(
        {
          url: state.base_url,
          lead_uid: state.lead_uid,
          clid: state.site_id,
          service_name,
          offer_url: window.location.href,
        },
        (res) => {
          resolve(res)
        }
      )
    })
  },

  async getHits({ commit, state, dispatch }) {
    const res = await dispatch('getProductsRequest', state.type_hits).catch(
      (error) => {
        if (has(error.response, 'data')) {
          console.log(error.response.data)
        }
      }
    )

    if (res && has(res, 'data') && isArray(res.data)) {
      commit('setValue', { k: 'hits', v: res.data.slice(0,6) })
    }
  },

  async getRecommend({ commit, state, dispatch }) {
    const res = await dispatch(
      'getProductsRequest',
      state.type_recommend
    ).catch((error) => {
      if (has(error.response, 'data')) {
        console.log(error.response.data)
      }
    })

    if (res && has(res, 'data') && isArray(res.data)) {
      commit('setValue', { k: 'recommend', v: res.data.slice(0,6) })
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
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
