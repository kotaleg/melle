import Vue from 'vue'
import { make } from 'vuex-pathify'
import { has } from 'lodash'

import shop from '@/api/shop'
import notify from '@/components/partial/notify'

// initial state
const state = {
  breadcrumbs: [],
  images: [],
  znachek: '',
  description: '',
  manufacturer: '',
  manufacturers: '',
  den: '',
  sostav: '',
  extra_description: '',
  extra_description_hidden: '',
  add_to_cart: '',
  buy_one_click: '',
  getProductStock: '',

  productId: false,
  name: '',
  manufacturer: '',
  currentCategory: '',
  sizeList: false,
  quantity: 1,
  options: [],
  images: [],
  stock: {
    inStock: false,
    isSpecial: false,
    maxQuantity: 1,
    star: false,
    optionsForCart: {},
    optionsForOneClick: {},
  },

  productPreview: {
    productId: false,
    quantity: 1,
    options: [],
    stock: {
      inStock: false,
      isSpecial: false,
      maxQuantity: 1,
      star: false,
      optionsForCart: {},
    },
  },
}

// getters
const getters = {
  getProductForGTM: (state) => {
    return {
      id: state.productId,
      name: state.name,
      price: state.stock.price,
      brand: state.manufacturer,
      category: state.currentCategory,
    }
  },
}

// actions
const actions = {
  INIT_DATA({ commit, state }) {
    shop.getInlineState('_product', (data) => {
      commit('SET_DATA', data)
    })
  },
  FETCH_DATA({ commit, dispatch, rootState }, productId) {
    dispatch('CLEAR_DATA')
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.catalog.getProductFullData,
        productId,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        if (has(res.data, 'data')) {
          commit('SET_DATA', res.data.data)
          dispatch('getProductStockRequest', {productId})
        }
        notify.messageHandler(res.data, '_header')
      }
    )
  },
  CLEAR_DATA({ commit }) {
    commit('SET_PRODUCT_ID', false)
    commit('SET_NAME', '')
    commit('SET_IMAGES', [])
    commit('SET_OPTIONS', [])
  },
  ENABLE_IMAGE({ commit, state }, index) {
    for (const i in state.images) {
      if (i == index) {
        commit('SET_IMAGE_STATUS', {index: i, status: true})
      } else {
        commit('SET_IMAGE_STATUS', {index: i, status: false})
      }
    }
  },
  quantityHandler({ commit, state }, operation) {
    let q = state.quantity
    if (operation == '+') {
      q += 1
    } else if (operation == '-') {
      q -= 1
    }
    if (q < 1) {
      q = 1
    }
    if (state.stock.maxQuantity < q) {
      q = state.stock.maxQuantity
    }
    commit('SET_QUANTITY', q)
  },
  addToCartRequest({ rootState, getters }, payload) {
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.cart.add_to_cart,
        ...payload,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        notify.messageHandler(res.data, '_header')

        this.dispatch('cart/updateCartDataRequest')

        if (has(res.data, 'added') && res.data.added === true) {
          // GTM
          this.dispatch('gtm/addToCart', getters.getProductForGTM)

          // RETAIL R START
          if (has(res.data, 'rr_product_id') && res.data.rr_product_id != null) {
            try { rrApi.addToBasket(res.data.rr_product_id) } catch(e) {}
          }
          // RETAIL R END
        }
      }
    )
  },

  oneClickRequest({ state, rootState }, payload) {
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.cart.buy_one_click,
        product_id: state.productId,
        quantity: state.quantity,
        options: state.stock.optionsForOneClick,
        ...payload,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        notify.messageHandler(res.data, '_header')

        if (has(res.data, 'sent') && res.data.sent === true) {
          Vue.prototype.$modal.hide('one-click-modal', {})
        }
      }
    )
  },

  getProductPreviewDataRequest({ dispatch, rootState, commit }, productId) {
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.catalog.getProductPreviewData,
        productId,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        if (has(res.data, 'data')) {
          commit('SET_PRODUCT_PREVIEW', res.data.data)
          dispatch('getProductPreviewStockRequest', {productId})
        }
        notify.messageHandler(res.data, '_header')
      }
    )
  },
  getProductStockRequest({ state, commit }, payload) {
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: state.getProductStock,
        ...payload,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        if (has(res.data, 'options')) {
          commit('SET_OPTIONS', res.data.options)
        }
        if (has(res.data, 'stock')) {
          commit('SET_STOCK', res.data.stock)
        }
        notify.messageHandler(res.data, '_header')
      }
    )
  },
  getProductPreviewStockRequest({ rootState, commit }, payload) {
    this.dispatch('header/setLoadingStatus', true)
    shop.makeRequest(
      {
        url: rootState.catalog.getProductPreviewStock,
        ...payload,
      },
      (res) => {
        this.dispatch('header/setLoadingStatus', false)
        if (has(res.data, 'options')) {
          commit('SET_PRODUCT_PREVIEW_OPTIONS', res.data.options)
        }
        if (has(res.data, 'stock')) {
          commit('SET_PRODUCT_PREVIEW_STOCK', res.data.stock)
        }
        if (has(res.data, 'image')) {
          commit('SET_PRODUCT_PREVIEW_IMAGE', res.data.image)
        }
        notify.messageHandler(res.data, '_header')
      }
    )
  },
  CLEAR_PREVIEW({ commit }) {
    commit('SET_PRODUCT_PREVIEW_OPTIONS', [])
    commit('SET_PRODUCT_PREVIEW_STOCK', {})
    commit('SET_PRODUCT_PREVIEW_IMAGE', false)
  },
}

// mutations
const mutations = {
  ...make.mutations(state),

  SET_PRODUCT_PREVIEW_QUANTITY(state, quantity) {
    Vue.set(state.productPreview, 'quantity', quantity)
  },
  SET_PRODUCT_PREVIEW_IMAGE(state, imagePath) {
    Vue.set(state.productPreview, 'image', imagePath)
  },
  SET_PRODUCT_PREVIEW_OPTIONS(state, data) {
    Vue.set(state.productPreview, 'options', data)
  },
  SET_PRODUCT_PREVIEW_STOCK(state, data) {
    Vue.set(state.productPreview, 'stock', data)
  },
  SET_IMAGE_STATUS(state, payload) {
    Vue.set(state.images[payload.index], 'enabled', payload.status)
  },
  SET_DATA(state, data) {
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
