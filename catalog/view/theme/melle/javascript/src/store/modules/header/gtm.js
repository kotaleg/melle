import Vue from 'vue'
import shop from '@/api/shop'

const isDataLayer =
  typeof dataLayer === 'object' && typeof dataLayer.push === 'function'

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
  initData({ commit }) {
    shop.getInlineState('_gtm', (data) => {
      commit('setData', data)
    })
  },
  productClick({ state }, product) {
    if (!isDataLayer) {
      return
    }

    dataLayer.push({ ecommerce: null })
    dataLayer.push({
      ecommerce: {
        click: {
          actionField: {
            list: state.page_type,
          },
          products: [product],
        },
      },
      event: 'gtm-ee-event',
      'gtm-ee-event-category': 'Enhanced Ecommerce',
      'gtm-ee-event-action': 'Product Clicks',
      'gtm-ee-event-non-interaction': 'False',
    })
  },
  addToCart({}, product) {
    if (!isDataLayer) {
      return
    }

    dataLayer.push({ ecommerce: null })
    dataLayer.push({
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
  },
  removeFromCart({}, products) {
    if (!isDataLayer) {
      return
    }

    dataLayer.push({ ecommerce: null })
    dataLayer.push({
      ecommerce: {
        remove: {
          products: products,
        },
      },
      event: 'gtm-ee-event',
      'gtm-ee-event-category': 'Enhanced Ecommerce',
      'gtm-ee-event-action': 'Removing a Product from a Shopping Cart',
      'gtm-ee-event-non-interaction': 'False',
    })
  },
  openCheckoutPage({ rootGetters }) {
    if (!isDataLayer) {
      return
    }

    dataLayer.push({ ecommerce: null })
    dataLayer.push({
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
  },
  loadCatalog({ state, rootGetters }) {
    if (!isDataLayer) {
      return
    }

    let products = []
    rootGetters['catalog/getProductsForGTM'].forEach((product) => {
      product['list'] = state.page_type
      products.push(product)
    })

    dataLayer.push({ ecommerce: null })
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
