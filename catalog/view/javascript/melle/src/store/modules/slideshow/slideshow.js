import Vue from 'vue'

import shop from '../../../api/shop'

// initial state
const state = {
  slidesData: {},
}

// getters
const getters = {
  getSlidesForModule: (state) => (moduleId) => {
    return state.slidesData[moduleId]
  },
}

// actions
const actions = {
  initData({ commit, state }, moduleId) {
    shop.getInlineState(`_slideshow_${moduleId}`, (data) => {
      commit('setSlideData', { moduleId, data })
    })
  },
}

// mutations
const mutations = {
  setSlideData(state, { moduleId, data }) {
    Vue.set(state.slidesData, moduleId, data)
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
