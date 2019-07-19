import Vue from 'vue'
import { isUndefined, isEmpty, has, clone, debounce } from 'lodash'

import shop from '../../api/shop'
import notify from '../../components/partial/notify'

// initial state
const state = {
    is_loading: false,

    selectedCategory: null,
    selectedStoreCategories: [],
}

// getters
const getters = {
    getToggleStates: (state) => {
        return {
            checked: state.text_enabled,
            unchecked: state.text_disabled,
        }
    },
    getSettingValue: (state) => (index) => {
        return state.setting[index]
    },
    getValue: (state) => (index) => {
        return state[index]
    },
}

// actions
const actions = {
    initData({ commit }) {
        shop.getInlineState(data => {
            commit('setData', data)
        })
    },
    updateSetting({ commit }, payload) {
        commit('updateSetting', payload)
    },
    setLoadingStatus({ commit }, status) {
        commit('setLoadingStatus', status)
    },
    updateValue({ commit, dispatch }, payload) {
        commit('updateValue', payload)
    },
    updateCategoriesRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.updateCategories,
            },
            res => {
                commit('setLoadingStatus', false)

                if (has(res.data, 'updated')
                && res.data.updated === true) {
                    dispatch('getCategoriesRequest')
                }

                notify.messageHandler(res.data)
            }
        )
    }, 50),
    clearSelectionRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.clearSelection,
            },
            res => {
                commit('setLoadingStatus', false)

                dispatch('getStoreCategoriesRequest')
                dispatch('getUnlinkedCountRequest')

                notify.messageHandler(res.data)
            }
        )
    }, 50),
    getCategoriesRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.getCategories,
            },
            res => {
                commit('setLoadingStatus', false)

                if (has(res.data, 'categories')) {
                    commit('updateValue', {
                        k: 'categories',
                        v: res.data.categories
                    })
                }
                if (has(res.data, 'categoriesCount')) {
                    commit('updateValue', {
                        k: 'categoriesCount',
                        v: res.data.categoriesCount
                    })
                }

                notify.messageHandler(res.data)
            }
        )
    }, 50),
    getStoreCategoriesRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.getStoreCategories,
            },
            res => {
                commit('setLoadingStatus', false)

                if (has(res.data, 'storeCategories')) {
                    commit('updateValue', {
                        k: 'storeCategories',
                        v: res.data.storeCategories
                    })
                }
                if (has(res.data, 'categoriesCount')) {
                    commit('updateValue', {
                        k: 'categoriesCount',
                        v: res.data.categoriesCount
                    })
                }

                notify.messageHandler(res.data)
            }
        )
    }, 50),
    getUnlinkedCountRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.getUnlinkedCount,
            },
            res => {
                commit('setLoadingStatus', false)

                if (has(res.data, 'unlinkedCount')) {
                    commit('updateValue', {
                        k: 'unlinkedCount',
                        v: res.data.unlinkedCount
                    })
                }

                notify.messageHandler(res.data)
            }
        )
    }, 50),
    applyLinkRequest: debounce(({ commit, state, dispatch }) => {
        commit('setLoadingStatus', true)
        shop.makeRequest(
            {
                url: state.applyLink,
                selectedCategory: state.selectedCategory,
                selectedStoreCategories: state.selectedStoreCategories,
            },
            res => {
                commit('setLoadingStatus', false)

                if (has(res.data, 'updated')
                && res.data.updated === true) {
                    dispatch('getStoreCategoriesRequest')
                    dispatch('getUnlinkedCountRequest')
                }

                notify.messageHandler(res.data)
            }
        )
    }, 50),
}

// mutations
const mutations = {
    setData (state, data) {
        for (let d in data) {
            Vue.set(state, d, data[d])
        }
    },
    setLoadingStatus(state, status) {
        Vue.set(state, 'is_loading', status)
    },
    updateSetting(state, {k, v}) {
        Vue.set(state.setting, k, v)
    },
    updateValue(state, {k, v}) {
        Vue.set(state, k, v)
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}