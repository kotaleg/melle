import Vue from 'vue'

import shop from '@/api/shop'

// initial state
const state = {
  priceLists: [],
}

// getters
const getters = {}

// actions
const actions = {
  initData({ commit, state }) {
    shop.getInlineState('_pricelist', (data) => {
      commit('setData', data)
    })
  },
}

// mutations
const mutations = {
  setData(state, data) {
    for (let d in data) {
      Vue.set(state, d, data[d])
    }
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
