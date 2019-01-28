import Vue from 'vue'
import { isUndefined, isArray, has, clone, debounce } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'

// initial state
const state = {
    products: [],
    product_total: 0,

    design_col: true,
    get_link: '',
}

// getters
const getters = {
    canLoadMore: state => {
        return state.product_total > 0
            && state.product_total > state.products.length
    },
    getRating: state => key => {
        let rating = []
        for (let r in [1,2,3,4,5]) {
            if (state.products[key].default_values.rating > 0
            && state.products[key].default_values.rating > parseInt(r)) {
                rating.push(true)
            } else {
                rating.push(false)
            }
        }
        return rating
    },
    getPrice: state => key => {
        return state.products[key].default_values.price
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_catalog', data => {
            commit('setData', data)
        })
    },
    loadMoreRequest: debounce(({ commit, state, rootState, rootGetters, dispatch, getters }, reload) => {
        let filter_data = clone(rootState.filter.filter_data)
        if (reload !== true) { filter_data.page += 1 }
        if (rootGetters['filter/isFilterChanged']) {
            filter_data.page = 1
        }

        dispatch('header/setLoadingStatus', true, {root:true})
        shop.makeRequest(
            {
                url: state.get_link,
                filter_data,
            },
            res => {
                dispatch('header/setLoadingStatus', false, {root:true})

                if (has(res.data, 'products') && isArray(res.data.products)) {
                    if (reload !== true && !rootGetters['filter/isFilterChanged']) {
                        res.data.products.forEach((product) => {
                            commit('addProduct', product)
                        })
                    } else {
                        commit('setProducts', res.data.products)
                    }
                }

                if (has(res.data, 'product_total')) {
                    commit('setProductTotal', res.data.product_total)
                }

                if (has(res.data, 'filter_data')) {
                    dispatch('filter/updateFilterData', res.data.filter_data, {root:true})
                    dispatch('filter/updateLastFilterData', res.data.filter_data, {root:true})
                }

                notify.messageHandler(res.data, '_header')
            }
        )
    }, 500),
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    setProducts(state, products) {
        Vue.set(state, 'products', products)
    },
    addProduct(state, product) {
        let check = true
        state.products.forEach((p) => {
            if (p.product_id == product.product_id) {
                check = false
            }
        })

        if (check === true) {
            state.products.push(product)
        }
    },
    setProductTotal(state, product_total) {
        Vue.set(state, 'product_total', product_total)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
