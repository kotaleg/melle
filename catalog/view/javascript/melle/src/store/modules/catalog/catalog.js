import Vue from 'vue'
import { isUndefined, isArray, isEmpty, has, clone, debounce, forEach, some } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'

import filterHelper from '../../../router/filterHelper'
import router from '../../../router/index'

// initial state
const state = {
    products: [],
    product_total: 0,

    design_col: true,
    current_category: '',
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
    isSpecial: state => key => {
        return state.products[key].default_values.special !== false
            && state.products[key].default_values.special > 0
    },
    getSpecial: state => key => {
        return state.products[key].default_values.special
    },
    getProductsForGTM: state => {
        let products = []
        state.products.forEach((item, i) => {
            products.push({
                id: item.product_id,
                name: item.name,
                brand: item.manufacturer,
                category: state.current_category,
                price: item.default_values.price,
                position: i,
                quantity: item.quantity,
                list: '',
            })
        })
        return products
    },
    getProductForGTM: state => index => {
        let product = {}
        state.products.forEach((item, i) => {
            if (index === i) {
                product = {
                    id: item.product_id,
                    name: item.name,
                    brand: item.manufacturer,
                    category: state.current_category,
                    price: item.default_values.price,
                    position: i,
                    quantity: item.quantity,
                    list: '',
                }
            }
        })
        return product
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_catalog', data => {
            commit('setData', data)
        })
    },
    loadMoreRequest: debounce(({ commit, state, rootState, rootGetters, dispatch, getters }, payload) => {
        dispatch('header/setLoadingStatus', true, {root:true})
        dispatch('header/setSidebarLoadingStatus', true, {root:true})

        let filter_data = clone(rootState.filter.filter_data)
        if (has(payload, 'reload')
        && payload.reload !== true) {
            filter_data.page += 1
        }

        if (rootGetters['filter/isFilterChanged']
        || (has(payload, 'reload') && payload.reload == true)) {
            filter_data.page = 1
        }

        if (has(payload, 'clear')
        && payload.clear === true) {
            filter_data = {}
        }

        dispatch('updateRouterParams')

        shop.makeRequest(
            {
                url: state.get_link,
                filter_data,
            },
            res => {
                if (has(res.data, 'products') && isArray(res.data.products)) {
                    if ((has(payload, 'reload') && payload.reload !== true)
                    && !rootGetters['filter/isFilterChanged']) {
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

                dispatch('header/setLoadingStatus', false, {root:true})
                dispatch('header/setSidebarLoadingStatus', false, {root:true})
                notify.messageHandler(res.data, '_header')
            }
        )
    }, 500),
    updateRouterParams({ commit, state, rootState, rootGetters, dispatch, getters }) {
        let query = filterHelper.prepareFullQuery(rootState.filter.filter_data);
        router.push({ path: Vue.prototype.$storePath , query })
    },
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
