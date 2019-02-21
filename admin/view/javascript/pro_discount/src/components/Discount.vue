<template>
    <form class="form-horizontal">


            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Название</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="name" placeholder="Название" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Тип скидки</label>
                <div class="col-sm-9 col-lg-5">
                    <treeselect
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Тип скидки"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :options="all_types"
                        v-model="type" />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Сортировка</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="sort_order" placeholder="Сортировка" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Действует с</label>
                <div class="col-sm-9 col-lg-5">
                    <date-picker
                        v-model="start_date"
                        format="YYYY-MM-DD"
                        placeholder="Действует с"
                        lang="ru" confirm />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Действует до</label>
                <div class="col-sm-9 col-lg-5">
                    <date-picker
                        v-model="finish_date"
                        format="YYYY-MM-DD"
                        placeholder="Действует до"
                        lang="ru" confirm />
                </div>
            </div>

            <div v-show="typeSale" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Сумма заказа</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="start_sum" class="form-control">
                </div>
            </div>

            <div v-show="typeSale" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Сумма и количество</label>
                <div class="col-sm-9 col-lg-5">
                    <toggle-button
                        v-model="sum_and_count"
                        :width="100"
                        :height="25"
                        :labels="scToggle"/>
                </div>
            </div>

            <div v-show="typeSale" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Количество товаров в заказе</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="start_count" class="form-control">
                </div>
            </div>

            <div v-show="typeSale" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Значение скидки</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="value" class="form-control">
                </div>
            </div>

            <div v-show="typeSale" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Единица измерения скидки</label>
                <div class="col-sm-9 col-lg-5">
                    <treeselect
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Единица измерения скидки"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :options="all_signs"
                        v-model="sign" />
                </div>
            </div>

            <div v-show="typeSaleCount" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Количество товаров</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="products_count" placeholder="Количество товаров" class="form-control">
                </div>
            </div>

            <div v-show="typeSaleCount" class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">Считаются как</label>
                <div class="col-sm-9 col-lg-5">
                    <input type="text" v-model="count_like" placeholder="Считаются как" class="form-control">
                </div>
            </div>


            <div class="form-group marked-shit">
                <label class="col-sm-3 col-lg-2 control-label">Категории</label>
                <div class="col-sm-9">
                    <treeselect
                        :multiple="true"
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Категории"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :defaultOptions="all_categories"
                        :load-options="loadCategories"
                        :async="true"
                        v-model="categories" />
                </div>
            </div>

            <div class="form-group marked-shit">
                <label class="col-sm-3 col-lg-2 control-label">Производители</label>
                <div class="col-sm-9">
                    <treeselect
                        :multiple="true"
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Производители"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :defaultOptions="all_manufacturers"
                        :load-options="loadManufacturers"
                        :async="true"
                        v-model="manufacturers" />
                </div>
            </div>

            <div class="form-group marked-shit">
                <label class="col-sm-3 col-lg-2 control-label">Товары</label>
                <div class="col-sm-9">
                    <treeselect
                        :multiple="true"
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Товары"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :defaultOptions="all_products"
                        :load-options="loadProducts"
                        :async="true"
                        v-model="products" />
                </div>
            </div>

            <div class="form-group marked-shit">
                <label class="col-sm-3 col-lg-2 control-label">Пользователи</label>
                <div class="col-sm-9">
                    <treeselect
                        :multiple="true"
                        :maxHeight="200"
                        :alwaysOpen="false"
                        placeholder="Пользователи"
                        :noResultsText="text_no_results"
                        :loadingText="text_loading"
                        :defaultOptions="all_products"
                        :load-options="loadCustomers"
                        :async="true"
                        v-model="customers" />
                </div>
            </div>








    </form>
</template>

<script>
import { isEmpty, isString, trim } from 'lodash'
import { mapState, mapActions, mapGetters } from 'vuex'
import DatePicker from 'vue2-datepicker'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { ASYNC_SEARCH } from '@riophae/vue-treeselect'

export default {
    components: {
        DatePicker,
        Treeselect,
    },
    computed: {
        ...mapState('shop', [
            'text_cancel',
            'text_no_results',
            'text_loading',

            'all_categories',
            'all_products',
            'all_manufacturers',
            'all_customers',
            'all_types',
            'all_signs',
        ]),
        ...mapGetters('shop', [
            'getDiscountValue',
        ]),

        typeSale() {
            return this.type === 'sale'
        },
        typeSaleCount() {
            return this.type === 'sale_count'
        },
        scToggle() {
            return {
                checked: 'И',
                unchecked: 'ИЛИ',
            }
        },

        name: {
            get () { return this.getDiscountValue('name') },
            set (v) { this.updateDiscountValue({k: 'name', v}) }
        },
        type: {
            get () { return this.getDiscountValue('type') },
            set (v) { this.updateDiscountValue({k: 'type', v}) }
        },
        sort_order: {
            get () { return this.getDiscountValue('sort_order') },
            set (v) { this.updateDiscountValue({k: 'sort_order', v}) }
        },
        start_sum: {
            get () { return this.getDiscountValue('start_sum') },
            set (v) { this.updateDiscountValue({k: 'start_sum', v}) }
        },
        start_count: {
            get () { return this.getDiscountValue('start_count') },
            set (v) { this.updateDiscountValue({k: 'start_count', v}) }
        },
        sum_and_count: {
            get () { return this.getDiscountValue('sum_and_count') },
            set (v) { this.updateDiscountValue({k: 'sum_and_count', v}) }
        },
        registered_only: {
            get () { return this.getDiscountValue('registered_only') },
            set (v) { this.updateDiscountValue({k: 'registered_only', v}) }
        },
        value: {
            get () { return this.getDiscountValue('value') },
            set (v) { this.updateDiscountValue({k: 'value', v}) }
        },
        sign: {
            get () { return this.getDiscountValue('sign') },
            set (v) { this.updateDiscountValue({k: 'sign', v}) }
        },
        products_count: {
            get () { return this.getDiscountValue('products_count') },
            set (v) { this.updateDiscountValue({k: 'products_count', v}) }
        },
        count_like: {
            get () { return this.getDiscountValue('count_like') },
            set (v) { this.updateDiscountValue({k: 'count_like', v}) }
        },
        categories: {
            get () { return this.getDiscountValue('categories') },
            set (v) { this.updateDiscountValue({k: 'categories', v}) }
        },
        manufacturers: {
            get () { return this.getDiscountValue('manufacturers') },
            set (v) { this.updateDiscountValue({k: 'manufacturers', v}) }
        },
        customers: {
            get () { return this.getDiscountValue('customers') },
            set (v) { this.updateDiscountValue({k: 'customers', v}) }
        },
        products: {
            get () { return this.getDiscountValue('products') },
            set (v) { this.updateDiscountValue({k: 'products', v}) }
        },
        start_date: {
            get () { return this.getDiscountValue('start_date') },
            set (v) { this.updateDiscountValue({k: 'start_date', v}) }
        },
        finish_date: {
            get () { return this.getDiscountValue('finish_date') },
            set (v) { this.updateDiscountValue({k: 'finish_date', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateDiscountValue',
            'searchManufacturersRequest',
            'searchCategoriesRequest',
            'searchProductsRequest',
            'searchCustomersRequest'
        ]),

        loadManufacturers({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH) {
                this.searchManufacturersRequest(searchQuery)
                    .then(res => {
                        callback(null, res)
                    })
            }
        },
        loadCategories({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH) {
                this.searchCategoriesRequest(searchQuery)
                    .then(res => {
                        callback(null, res)
                    })
            }
        },
        loadProducts({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH) {
                this.searchProductsRequest(searchQuery)
                    .then(res => {
                        callback(null, res)
                    })
            }
        },
        loadCustomers({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH) {
                this.searchCustomersRequest(searchQuery)
                    .then(res => {
                        callback(null, res)
                    })
            }
        },
    },
}
</script>

<style lang="scss">
.marked-shit {
    background: #eee;
}
</style>
