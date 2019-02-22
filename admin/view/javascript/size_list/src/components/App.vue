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
                    @click="editItem(false)"
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
                </form>

                <items v-if="!is_edit" />
                <item v-if="is_edit" />

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
import Items from './Items.vue'
import Item from './Item.vue'

export default {
    components: {
        Loading,
        'page-header': PageHeader,
        'breadcrumbs': Breadcrumbs,
        'panel-default': Panel,
        Items,
        Item,
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
    },
    methods: {
        ...mapActions('shop', [
            'setLoadingStatus',
            'editItem',
            'updateSetting',
            'setEditStatus',
            'saveItem',
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
                let arr = $('#sl-form').serializeArray()
                let data = {}

                arr.forEach((item) => {
                    data[item.name] = item.value
                })

                this.saveItem(data)
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
        this.$store.dispatch('shop/getItems')
    },
}
</script>

<style lang="scss">
// NOTIFICATION
.vue-notification {
  font-size: 14px;
}
</style>
