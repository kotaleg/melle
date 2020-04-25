import Vue from 'vue'
import { isUndefined, has } from 'lodash'

import shop from '../../../api/shop'

// initial state
const state = {
  page_type: '',
  products: [],
  related_products: [],
}

// getters
const getters = {
  getPageType: (state) => {
    return state.page_type
  },
}

// actions
const actions = {
  initData({ commit, state }) {
    shop.getInlineState('_gtm', (data) => {
      commit('setData', data)
    })
  },
  productClick({ commit, state }, payload) {
    let page = state.page_type
    if (payload.page !== false) {
      page = payload.page
    }
    let product = payload.product

    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        event: 'productClick',
        ecommerce: {
          click: {
            actionField: {
              list: page,
            },
            products: [product],
          },
        },
        event: 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Product Clicks',
        'gtm-ee-event-non-interaction': 'False',
      })

      console.log('-- product click --')
    }
  },
  addToCart({ commit }, product) {
    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        event: 'addToCart',
        ecommerce: {
          currencyCode: 'RUB',
          add: {
            products: [product],
          },
        },
        event: 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Adding a Product to a Shopping Cart',
        'gtm-ee-event-non-interaction': 'False',
      })

      console.log('-- add to cart --')
    }
  },
  removeFromCart({ commit }, products) {
    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        event: 'removeFromCart',
        ecommerce: {
          currencyCode: 'RUB',
          add: {
            products: products,
          },
        },
        event: 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Removing a Product from a Shopping Cart',
        'gtm-ee-event-non-interaction': 'False',
      })

      console.log('-- remove from cart --')
    }
  },
  openCheckoutPage(
    { commit, state, rootState, rootGetters, dispatch },
    force = false
  ) {
    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        event: 'checkout',
        ecommerce: {
          checkout: {
            actionField: {
              step: 1,
            },
            products: rootGetters['cart/getProductsForGTM'],
          },
        },
        event: 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Checkout Step 1',
        'gtm-ee-event-non-interaction': 'False',
      })

      console.log('-- open checkout page --')
      dispatch('ecommShittyPush')
    }
  },
  ecommShittyPush({ commit, state, rootState, rootGetters }) {
    let ecomm_items = []
    let ecomm_categories = []
    let ecomm_values = []

    if (state.page_type != 'other') {
      rootGetters['cart/getProductsForGTM'].forEach((product) => {
        ecomm_items.push(product.id)
        ecomm_categories.push(product.brand)
        ecomm_values.push(product.price)
      })
    }

    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        event: 'rem',
        ecomm_prodid: ecomm_items,
        ecomm_pagetype: state.page_type,
        ecomm_category: ecomm_categories,
        ecomm_totalvalue: ecomm_values,
      })

      console.log('-- ECOMM --')
    }
  },

  loadCatalog({ commit, state, rootState, rootGetters }) {
    let products = []
    rootGetters['catalog/getProductsForGTM'].forEach((product) => {
      product['list'] = state.page_type
      products.push(product)
    })

    if (!isUndefined(dataLayer)) {
      dataLayer.push({
        ecommerce: {
          currencyCode: 'RUB',
          impressions: products,
        },
        event: 'gtm-ee-event',
        'gtm-ee-event-category': 'Enhanced Ecommerce',
        'gtm-ee-event-action': 'Product Impressions',
        'gtm-ee-event-non-interaction': 'True',
      })

      console.log('-- load catalog products --')
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
}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
