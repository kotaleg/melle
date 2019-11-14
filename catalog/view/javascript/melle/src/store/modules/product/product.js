import Vue from 'vue'
import { isUndefined, isInteger, isEmpty, isArray, isString, isEqual, has, first } from 'lodash'

import shop from '../../../api/shop'
import productApi from '../../../api/productApi'
import notify from '../../../components/partial/notify'
import Errors from '../../../components/partial/errors'

// initial state
const state = {
    form: {

    },
    errors: new Errors(),

    product_id: '',
    name: '',
    manufacturer: '',
    current_category: '',
    quantity: 1,

    is_options_for_product: false,
    options: [],
    full_combinations: [],
    default_values: {
        rating: 0,
        price: 0,
        special: false,
        min_quantity: 0,
        max_quantity: 0,
    },
}

// getters
const getters = {
    getStateValue: state => index => {
        return state[index]
    },
    getFormValue: state => index => {
        return state.form[index]
    },
    fieldHasError: state => field => {
        return state.errors.has(field)
    },
    getFieldError: state => field => {
        return state.errors.first(field)
    },
    getProductForGTM: state => {
        return {
            id: state.product_id,
            name: state.name,
            price: state.default_values.price,
            brand: state.manufacturer,
            category: state.current_category,
        }
    },
    getRating: state => {
        let rating = []
        for (let r in [1,2,3,4,5]) {
            if (state.default_values.rating > 0
            && state.default_values.rating > parseInt(r)) {
                rating.push(true)
            } else {
                rating.push(false)
            }
        }
        return rating
    },
    getPrice: state => {
        return state.default_values.price
    },
    isSpecial: (state, getters) => {
        let t = getters.getSpecial
        if (isString(t)) {
            t = t.replace(/\s+/g, '')
        }
        return getters.getSpecial !== false && t > 0
    },
    getSpecial: state => {
        return state.default_values.special
    },
    getTotalMaxQuantity: state => {
        return state.default_values.max_quantity
    },
    getActiveMaxQuantity: (state, getters) => {
        let q = false
        let active_comb = getters.isCombinationActive
        if (active_comb !== false) {
            q = state.full_combinations[active_comb].quantity
        }
        if (q === false) {
            return getters.getTotalMaxQuantity
        }
        return q
    },
    getActivePrice: (state, getters) => {
        let p = false
        let active_comb = getters.isCombinationActive
        if (active_comb !== false) {
            p = state.full_combinations[active_comb].price
        }
        if (p === false) {
            return getters.getPrice
        }
        return p
    },
    getActiveImageHash: (state, getters) => {
        let p = false
        let active_comb = getters.isCombinationActive
        if (active_comb !== false) {
            p = state.full_combinations[active_comb].imageHash
        }
        if (p === false) {
            return 'default'
        }
        return p
    },
    getActiveOptions: state => {
        let options = []
        state.options.forEach((option) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value) => {
                if (option_value.selected === true) {
                    options.push({
                        option_a: option.option_id,
                        option_value_a: option_value.option_value_id,
                    })
                }
            })
        })
        return options
    },
    getOptionsForOneClick: state => {
        let options = []
        state.options.forEach((option) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value) => {
                if (option_value.selected === true) {
                    options.push({
                        option_name: option.name,
                        option_value_name: option_value.name,
                    })
                }
            })
        })
        return options
    },
    getActiveOptionsKeys: state => {
        let options_keys = []
        state.options.forEach((option, k) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value, kk) => {
                if (option_value.selected === true) {
                    options.push({o_key: k, ov_key: kk})
                }
            })
        })
        return options_keys
    },
    isCombinationActive: (state, getters) => {
        let result = false
        let options = getters.getActiveOptions
        options = productApi.clearOptions(options)
        state.full_combinations.forEach((comb, index) => {
            let rr = productApi.clearOptions(comb.required)
            if (isEqual(options, rr)) {
                result = index
            }
        })
        return result
    },
    isAnythingSelected: state => {
        let status = false
        state.options.forEach((option) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value) => {
                if (option_value.selected === true) {
                    status = true
                }
            })
        })
        return status
    },
    isAllSelectedElementsDisabled: state => {
        let status = true
        state.options.forEach((option) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value) => {
                if (option_value.selected === true
                && option_value.disabled_by_selection !== true) {
                    status = false
                }
            })
        })
        return status
    },
    getOptionsForCart: state => {
        let options = {}
        state.options.forEach((option) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value) => {
                if (option_value.selected === true) {
                    options[option.product_option_id] = option_value.product_option_value_id
                }
            })
        })
        return options
    },
    getKeysForRealOptions: state => (payload) => {
        let result = {o_key: false, ov_key: false}
        state.options.forEach((option, k) => {
            if (option.option_id !== payload.option_a) { return }
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value, kk) => {
                if (option_value.option_value_id !== payload.option_value_a) {
                    return
                }
                result.o_key = k
                result.ov_key = kk
            })
        })
        return result
    }
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_product', data => {
            commit('setData', data)
        })
    },
    updateFormValue({ commit }, payload) {
        commit('updateFormValue', payload)
    },
    updateQuantity({ commit, getters }, q) {
        q = parseInt(q)
        if (getters.getTotalMaxQuantity < q) {
            q = getters.getTotalMaxQuantity
        }
        commit('setQuantity', q)
    },
    quantityHandler({ commit, state, getters }, type) {
        let q = state.quantity
        switch (type) {
            case '+':
                q += 1
                break;
            case '-':
                q -= 1
                break;
        }
        if (q < 1) { q = 1 }
        if (getters.getTotalMaxQuantity < q) {
            q = getters.getTotalMaxQuantity
        }
        commit('setQuantity', q)
    },
    radioHandler({ commit, state, dispatch, getters }, payload) {
        let o_key = payload.o_key
        let ov_key = payload.ov_key
        let status = payload.status

        dispatch('clearSelectionForOption', o_key)
        commit('setOptionSelectStatus', {o_key, ov_key, status:true})

        let active_comb = getters.isCombinationActive
        if (active_comb === false) {
            dispatch('unselectAllBut', o_key)
            dispatch('findCombination', o_key)
        }

        dispatch('clearDisabled')
        dispatch('updateDisabled')
    },
    clearSelectionForOption({ commit, state }, o_key) {
        if (!has(state.options, o_key)) { return }
        let option = state.options[o_key]
        if (!isArray(option.product_option_value)
        || isEmpty(option.product_option_value)) {
            return
        }
        option.product_option_value.forEach((value, ov_key) => {
            if (value.selected === true) {
                commit('setOptionSelectStatus', {o_key, ov_key, status:false})
            }
        })
    },
    unselectAllBut({ commit, state, dispatch }, o_key) {
        if (!has(state.options, o_key)) { return }
        state.options.forEach((value, key) => {
            if (key !== o_key) {
                dispatch('clearSelectionForOption', key)
            }
        })
    },
    findCombination({ commit, state, getters }) {
        let one = first(getters.getActiveOptions)
        let find = false

        if (!isUndefined(one)) {
            state.full_combinations.forEach((comb, i) => {
                if (find !== false) { return }

                comb.required.forEach((req) => {
                    if (find !== false) { return }
                    if (isEqual(one, req)) {
                        find = true

                        // MAKE COMBINATION ACTIVE
                        comb.required.forEach((req_) => {
                            if (!isEqual(one, req_)) {
                                let real_keys = getters.getKeysForRealOptions(
                                    {option_a:req_.option_a, option_value_a:req_.option_value_a})

                                if (real_keys.o_key !== false && real_keys.ov_key !== false) {
                                    commit('setOptionSelectStatus',
                                        {o_key:real_keys.o_key, ov_key:real_keys.ov_key, status:true})
                                }
                            }
                        })

                    }
                })
            })
        }
    },
    clearDisabled({ commit, state }) {
        state.options.forEach((option, o_key) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value, ov_key) => {
                if (option_value.disabled_by_selection === true) {
                    commit('setOptionDisabledStatus',
                        {o_key:o_key, ov_key:ov_key, status:false})
                }
            })
        })
    },
    updateDisabled({ commit, state, getters }) {
        let options = getters.getActiveOptions
        options = productApi.clearOptions(options)

        let allowed = []
        options.forEach((o) => {
            state.full_combinations.forEach((comb) => {
                comb.required.forEach((req) => {
                    if (comb.quantity > 0 && isEqual(req, o)) {
                        comb.required.forEach((req_) => {
                            allowed.push({
                                option_a: req_.option_a,
                                option_value_a: req_.option_value_a,
                            })
                        })
                    }
                })
            })
        })

        state.options.forEach((option, o_key) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }
            option.product_option_value.forEach((option_value, ov_key) => {
                let check = false
                allowed.forEach((a) => {
                    if (a.option_a == option.option_id
                    && option_value.option_value_id === a.option_value_a) {
                        check = true
                    }
                })

                if (check === false) {
                    commit('setOptionDisabledStatus',
                        {o_key:o_key, ov_key:ov_key, status:true})
                }
            })
        })
    },
    selectFirstCombination({ commit, state, dispatch, getters }) {
        let picked = false
        state.full_combinations.forEach((comb) => {
            if (picked !== false) { return }
            if (comb.quantity > 0) {
                comb.required.forEach((req) => {
                    picked = true
                    let real_keys = getters.getKeysForRealOptions(
                        {option_a:req.option_a, option_value_a:req.option_value_a})

                    if (real_keys.o_key !== false && real_keys.ov_key !== false) {
                        commit('setOptionSelectStatus',
                            {o_key:real_keys.o_key, ov_key:real_keys.ov_key, status:true})
                    }
                })
            }
        })

        if (picked === true) {
            dispatch('clearDisabled')
            dispatch('updateDisabled')
        }
    },

    addToCartRequest({ commit, state, rootState, dispatch, getters }) {
        this.dispatch('header/setLoadingStatus', true)
        shop.makeRequest(
            {
                url: rootState.cart.add_to_cart,
                product_id: state.product_id,
                quantity: state.quantity,
                options: getters.getOptionsForCart,
            },
            res => {
                this.dispatch('header/setLoadingStatus', false)
                notify.messageHandler(res.data, '_header')

                this.dispatch('cart/updateCartDataRequest')


                if (has(res.data, 'added') && res.data.added === true) {
                    // GTM
                    this.dispatch('gtm/addToCart', getters.getProductForGTM)
                }
            }
        )
    },

    oneClickRequest({ commit, state, rootState, dispatch, getters }, payload) {
        this.dispatch('header/setLoadingStatus', true)
        shop.makeRequest(
            {
                url: rootState.cart.buy_one_click,
                product_id: state.product_id,
                quantity: state.quantity,
                options: getters.getOptionsForOneClick,
                name: payload.name,
                phone: payload.phone,
                agree: payload.agree,
            },
            res => {
                this.dispatch('header/setLoadingStatus', false)

                if (has(res.data, 'sent') && res.data.sent === true) {
                    Vue.prototype.$modal.hide('one-click-modal', {})
                }

                notify.messageHandler(res.data, '_header')
            }
        )
    },
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    updateFormValue(state, { k, v }) {
        Vue.set(state.form, k, v)
        state.errors.clear(k)
    },
    clearFormErrors(state) {
        state.errors.clear()
    },
    setFormErrors(state, errors) {
        state.errors.record(errors)
    },

    setQuantity(state, value) {
        if (isInteger(value) && value >= 1) {
            Vue.set(state, 'quantity', value)
        }
    },

    setOptionSelectStatus(state, { o_key, ov_key, status }) {
        Vue.set(state.options[o_key]['product_option_value'][ov_key], 'selected', status)
    },
    setOptionDisabledStatus(state, { o_key, ov_key, status }) {
        Vue.set(state.options[o_key]['product_option_value'][ov_key], 'disabled_by_selection', status)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
