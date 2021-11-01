import Vue from 'vue'
import { has, clone } from 'lodash'
import shop from '@/api/shop'
import notify from '@/components/partial/notify'
import gtag from '@/plugins/gtag'

// initial state
const state = {
  count: 0,
  products: [],
  total: 0,
  totals: [],
  is_checkout: false,
  catalog_link: '',
  checkout_link: '',
}

// getters
const getters = {
  hasProducts: (state) => {
    return state.count > 0 ? true : false
  },
}

// actions
const actions = {
  initData({ commit }) {
    shop.getInlineState('_cart', (data) => {
      commit('setData', data)
    })
  },

  updateCartDataRequest({ commit, state }) {
    this.dispatch('header/setSidebarLoadingStatus', true)
    shop.makeRequest(
      {
        url: state.get_data,
      },
      (res) => {
        this.dispatch('header/setSidebarLoadingStatus', false)

        if (has(res.data, 'count')) {
          commit('setCount', res.data.count)
        }

        if (has(res.data, 'total')) {
          commit('setTotal', res.data.total)
        }

        if (has(res.data, 'totals')) {
          commit('setTotals', res.data.totals)
        }

        if (has(res.data, 'products')) {
          commit('setProducts', res.data.products)
        }

        notify.messageHandler(res.data, '_header')
      }
    )
  },

  clearCartRequest({ state, dispatch }) {
    this.dispatch('header/setSidebarLoadingStatus', true)
    shop.makeRequest(
      {
        url: state.clear,
      },
      (res) => {
        this.dispatch('header/setSidebarLoadingStatus', false)
        notify.messageHandler(res.data, '_header')

        const removed_items = clone(state.products)

        dispatch('updateCartDataRequest')

        if (has(res.data, 'cleared') && res.data.cleared === true) {
          gtag.removeFromCart({ products: removed_items })
        }

        if (state.is_checkout === true) {
          window.location = state.checkout_link
        }
      }
    )
  },

  updateCartItemRequest({ state, dispatch }, payload) {
    this.dispatch('header/setSidebarLoadingStatus', true)

    let quantity_data = {}
    quantity_data[payload.cart_id] = payload.quantity

    shop.makeRequest(
      {
        url: state.update,
        quantity_data,
      },
      (res) => {
        this.dispatch('header/setSidebarLoadingStatus', false)
        notify.messageHandler(res.data, '_header')

        dispatch('updateCartDataRequest')

        if (state.is_checkout === true) {
          window.location = state.checkout_link
        }
      }
    )
  },

  removeCartItemRequest({ state, dispatch }, cart_id) {
    this.dispatch('header/setSidebarLoadingStatus', true)
    shop.makeRequest(
      {
        url: state.remove,
        cart_id,
      },
      (res) => {
        this.dispatch('header/setSidebarLoadingStatus', false)
        notify.messageHandler(res.data, '_header')

        const removed_items = state.products.filter(
          (product) => product.cart_id === cart_id
        )

        dispatch('updateCartDataRequest')

        if (has(res.data, 'removed') && res.data.removed === true) {
          gtag.removeFromCart({ products: removed_items })
        }

        if (state.is_checkout === true) {
          window.location = state.checkout_link
        }
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
  setCount(state, count) {
    Vue.set(state, 'count', count)
  },
  setTotal(state, total) {
    Vue.set(state, 'total', total)
  },
  setTotals(state, totals) {
    Vue.set(state, 'totals', totals)
  },
  setProducts(state, products) {
    Vue.set(state, 'products', products)
  },
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
