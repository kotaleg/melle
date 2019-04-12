<template>
    <div>
        <notifications
            :group="this.$codename"
            position="bottom right"/>

        <loading
            :active.sync="is_loading"
            :is-full-page="true" />

        <page-header
            :title="heading_title"
            :version="version">

            <template slot="buttons">
                <button
                    v-if="!is_edit"
                    @click="editDiscount(false)"
                    data-toggle="tooltip"
                    title="Добавить скидку"
                    class="btn btn-danger">
                    <i class="fa fa-plus"/>
                </button>
                <button
                    v-if="!is_edit"
                    @click="saveAndStay"
                    data-toggle="tooltip"
                    :title="button_save_and_stay"
                    class="btn btn-success">
                    <i class="fa fa-save"/>
                </button>
                <button
                    @click="saveAndGo"
                    data-toggle="tooltip"
                    :title="button_save"
                    class="btn btn-primary">
                    <i class="fa fa-save"/>
                </button>
                <button
                    @click="cancelRoutine"
                    data-toggle="tooltip"
                    class="btn btn-default">
                    <i class="fa fa-reply"/>
                </button>
            </template>

            <breadcrumbs
                :crumbs="breadcrumbs"/>
        </page-header>

        <div class="container-fluid">
            <panel-default
                :title="text_edit">

                <form v-if="!is_edit" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_status }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <toggle-button
                                v-model="status"
                                :width="100"
                                :height="25"
                                :labels="getToggleStates"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_sort_order }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <input type="text" v-model="sort_order" :placeholder="text_sort_order" class="form-control">
                        </div>
                    </div>
                </form>

                <discounts v-if="!is_edit" />
                <discount v-if="is_edit" />

            </panel-default>
        </div>

    </div>
</template>

<script>
import { isUndefined, isEmpty, extend } from 'lodash'
import { mapState, mapGetters, mapActions } from 'vuex'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'

import shop from '../api/shop'
import notify from './partial/notify'
import PageHeader from './partial/PageHeader.vue'
import Breadcrumbs from './partial/Breadcrumbs.vue'
import Panel from './partial/Panel.vue'
import Discounts from './Discounts.vue'
import Discount from './Discount.vue'

export default {
    components: {
        Loading,
        'page-header': PageHeader,
        'breadcrumbs': Breadcrumbs,
        'panel-default': Panel,
        Discounts,
        Discount,
    },
    computed: {
        ...mapState('shop', [
            'breadcrumbs',
            'version',
            'heading_title',

            'button_save_and_stay',
            'button_save',
            'button_cancel',

            'text_edit',
            'text_status',
            'text_sort_order',
            'text_close',
            'text_cancel',
            'text_warning',
            'text_actions',

            'cancel',
            'save',
            'setting',
            'is_loading',
            'is_edit',
        ]),
        ...mapGetters('shop', [
            'getToggleStates',
            'getSettingValue',
        ]),
        status: {
            get () { return this.getSettingValue('status') },
            set (v) { this.updateSetting({k: 'status', v}) }
        },
        sort_order: {
            get () { return this.getSettingValue('sort_order') },
            set (v) { this.updateSetting({k: 'sort_order', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'setLoadingStatus',
            'editDiscount',
            'updateSetting',
            'setEditStatus',
            'saveDiscount',
        ]),

        saveAndStay() {
            this.setLoadingStatus(true)
            let data = extend({}, this.setting, { url: this.save })
            shop.postSettingData(data, res => {
                this.setLoadingStatus(false)
                notify.messageHandler(res.data)
            })
        },
        saveAndGo() {
            if (this.is_edit) {
                this.saveDiscount()
                return
            }

            this.setLoadingStatus(true)
            let data = extend({}, this.setting, { url: this.save })
            shop.postSettingData(data, res => {
                this.setLoadingStatus(false)
                notify.messageHandler(res.data)

                setTimeout(() => {
                    window.location.href = this.cancel
                }, 1500)
            })
        },
        cancelRoutine() {
            if (this.is_edit) {
                this.setEditStatus(false)
                return
            }
            window.location.href = this.cancel
        }
    },
    created() {
        this.$store.dispatch('shop/initData')
        this.$store.dispatch('shop/getDiscounts')
    },
}
</script>

<style lang="scss">
// NOTIFICATION
.vue-notification {
  font-size: 14px;
}
</style>
