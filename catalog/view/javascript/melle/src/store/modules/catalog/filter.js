import Vue from 'vue'
import { isUndefined, isEqual, isArray, isObject, has, forEach, debounce } from 'lodash'

import shop from '../../../api/shop'
import notify from '../../../components/partial/notify'

// initial state
const state = {
    filter_data: {
        min_den: '',
        max_den: '',
        min_price: '',
        max_price: '',
        hit: false,
        neww: false,
        act: false,
        material: '',
        color: '',
        size: '',
        manufacturers: [],

        category_id: 0,
        search: null,

        page: 1,
        sort: {'label': 'Наименование', 'value': 'pd.name'},
        all_sorts: [],
        order: 'ASC',
    },

    last_filter: {},
    slider_options: {den: {}, price: {}},
}

// getters
const getters = {
    getFilterValue: state => index => {
        return state.filter_data[index]
    },
    getSliderOptions: state => key => {
        return state.slider_options[key]
    },
    isFilterChanged: state => {
        return !isEqual(state.filter_data, state.last_filter)
    },
    isManufacturerSelected: state => key => {
        return state.filter_data.manufacturers[key].checked
    },
}

// actions
const actions = {
    initData({ commit, state }) {
        shop.getInlineState('_filter', data => {
            commit('setData', data)
        })
    },
    updateFilterValue({ commit, state, getters }, payload) {
        if (getters.getFilterValue(payload.k) != payload.v) {
            commit('updateFilterValue', payload)
            this.dispatch('catalog/loadMoreRequest')
        }
    },
    updateFromSlider({ commit, state, getters }, payload) {
        let type = payload.type
        let value = payload.v
        if (isArray(value)) {
            if (!isUndefined(value[0])
            && getters.getFilterValue(`min_${type}`) != value[0]) {
                commit('updateFilterValue', {k: `min_${type}`, v: value[0]})
                this.dispatch('catalog/loadMoreRequest')
            }

            if (!isUndefined(value[1])
            && getters.getFilterValue(`max_${type}`) != value[1]) {
                commit('updateFilterValue', {k: `max_${type}`, v: value[1]})
                this.dispatch('catalog/loadMoreRequest')
            }
        }
    },
    updateManufacturerStatus({ commit, state, getters }, k) {
        let v = !state.filter_data.manufacturers[k].checked
        commit('updateManufacturerCheckedStatus', {k, v})
        this.dispatch('catalog/loadMoreRequest')
    },
    updateFilterData({ commit }, payload) {
        forEach(payload, (v, k) => {
            commit('updateFilterValue', {k, v})
        })
    },
    updateLastFilterData({ commit }, payload) {
        forEach(payload, (v, k) => {
            commit('updateLastFilterValue', {k, v})
        })
    },
    flipSortOrder: debounce(({ commit, state, dispatch }) => {
        let v = state.filter_data.order
        if (v == 'ASC') {
            v = 'DESC'
        } else {
            v = 'ASC'
        }
        commit('updateFilterValue', {k:'order', v})
        dispatch('catalog/loadMoreRequest', null, {root:true})
    }, 100),
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    updateFilterValue(state, {k, v}) {
        Vue.set(state.filter_data, k, v)
    },
    updateLastFilterValue(state, {k, v}) {
        Vue.set(state.last_filter, k, v)
    },
    updateManufacturerCheckedStatus(state, {k, v}) {
        Vue.set(state.filter_data.manufacturers[k], 'checked', v)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations,
}
