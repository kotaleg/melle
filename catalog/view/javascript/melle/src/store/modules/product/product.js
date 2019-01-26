import Vue from 'vue'
import { isUndefined, isInteger, isEmpty, isArray, has } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'
import Errors from '../../../components/partial/errors'

// initial state
const state = {
    form: {

    },
    errors: new Errors(),

    product_id: '',
    quantity: 1,

    is_options_for_product: false,
    options: [],
    combinations_for_options: [],
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
    isSpecial: state => {
        return state.default_values.special === true
    },
    getSpecial: state => {
        return state.default_values.special
    },
    getAvailableQuantity: state => {
        return state.default_values.max_quantity
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
        if (getters.getAvailableQuantity < q) {
            q = getters.getAvailableQuantity
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
        if (getters.getAvailableQuantity < q) {
            q = getters.getAvailableQuantity
        }

        commit('setQuantity', q)
    },
    selectFirstAvailable({ commit }) {

    },

    radioHandler({ commit, dispatch, getters }, payload) {
        let o_key = payload.o_key
        let ov_key = payload.ov_key
        let status = payload.status

        dispatch('clearSelectionForOption', o_key)
        if (!state.options[o_key].product_option_value[ov_key].disabled_by_selection) {
            commit('setOptionSelectStatus', {o_key, ov_key, status})

            if (status === true) {
                dispatch('updateSelectionFromGenerated', {o_key, ov_key})
                dispatch('makeOnlyOneActive', o_key)
            }
        }

        if (!getters.isAnythingSelected) {
            dispatch('clearDisableForAll')
        }
    },

    clearSelectionForOption({ commit, state }, o_key) {
        if (!has(state.options, o_key)) {
            return
        }

        let option = state.options[o_key]

        if (!isArray(option.product_option_value)
        || isEmpty(option.product_option_value)) {
            return
        }

        option.product_option_value.forEach((value, ov_key) => {
            commit('setOptionSelectStatus', {o_key, ov_key, status:false})
        })
    },

    clearDisableForAll({ commit, state }) {
        state.options.forEach((option, o_key) => {
            if (!isArray(option.product_option_value)
            || isEmpty(option.product_option_value)) {
                return
            }

            option.product_option_value.forEach((option_value, ov_key) => {
                commit('setOptionDisabledStatus', {o_key, ov_key, status:false})
            })
        })
    },

    updateSelectionFromGenerated({ commit, state }, payload) {
        if (!has(state.options, payload.o_key)) {
            return
        }

        let option = state.options[payload.o_key]

        if (!isArray(option.product_option_value)
        || isEmpty(option.product_option_value)) {
            return
        }

        option.product_option_value.forEach((option_value, ov_key) => {

            if (payload.ov_key !== ov_key) {
                return
            }

            let active_option = option.option_id
            let active_option_value = option_value.option_value_id

            state.combinations_for_options.forEach((comb) => {
                if (comb.active_option === active_option
                && comb.active_option_value === active_option_value) {

                    comb.generated_statuses.forEach((g_option_value, g_o_key) => {
                        g_option_value.product_option_value.forEach((g_status, g_ov_key) => {
                            commit('setOptionDisabledStatus',
                                {o_key:g_o_key, ov_key:g_ov_key, status:!g_status})
                        })
                    })

                }
            })

        })
    },

    makeOnlyOneActive({ commit, state }, o_key) {
        if (!has(state.options, o_key)) {
            return
        }

        let option = state.options[o_key]

        if (!isArray(option.product_option_value)
        || isEmpty(option.product_option_value)) {
            return
        }

        let check = false

        option.product_option_value.forEach((option_value) => {
            if (option_value.selected === true) {
                check = true
            }
        })

        if (check === true) {
            option.product_option_value.forEach((option_value, ov_key) => {
                if (option_value.selected !== true) {
                    commit('setOptionDisabledStatus', {o_key, ov_key, status:true})
                }
            })
        }
    },

    addToCartRequest({ commit, state, rootState, dispatch }) {
        this.dispatch('header/setLoadingStatus', true)

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

        shop.makeRequest(
            {
                url: state.add_to_cart,
                product_id: state.product_id,
                quantity: state.quantity,
                options: options,
            },
            res => {
                this.dispatch('header/setLoadingStatus', false)

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
