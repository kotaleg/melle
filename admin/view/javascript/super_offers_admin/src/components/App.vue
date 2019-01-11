<template>
    <div>
        <notifications
            :group="this.$codename"
            position="bottom right"/>

        <page-header
            :title="heading_title"
            :version="version">

            <template slot="buttons">
                <button
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
                <a
                    :href="cancel"
                    data-toggle="tooltip"
                    :title="button_cancel"
                    class="btn btn-default">
                    <i class="fa fa-reply"/>
                </a>
            </template>

            <breadcrumbs
                :crumbs="breadcrumbs"/>
        </page-header>

        <div class="container-fluid">
            <panel-default
                :title="text_edit">

                <form class="form-horizontal">
                    <div class="form-group clearfix">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_status }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <toggle-button
                                v-model="status"
                                :width="80"
                                :height="25"
                                :labels="getToggleStates"/>
                        </div>
                    </div>
                </form>

            </panel-default>
        </div>

    </div>
</template>

<script>
import { isUndefined, extend } from 'lodash'
import { mapState, mapGetters, mapMutations } from 'vuex'
import PageHeader from './partial/PageHeader.vue'
import Breadcrumbs from './partial/Breadcrumbs.vue'
import Panel from './partial/Panel.vue'

import shop from '../api/shop'
import notify from './partial/notify'

export default {
    components: {
        'page-header': PageHeader,
        'breadcrumbs': Breadcrumbs,
        'panel-default': Panel,
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

            'cancel',
            'save',
            'setting',
            'somethingLoading',
        ]),
        ...mapGetters('shop', [
            'getToggleStates',
            'getSettingValue',
        ]),
        status: {
            get () { return this.getSettingValue('status') },
            set (value) { this.updateSetting({index: 'status', value}) }
        },
    },
    created() {
        this.$store.dispatch('shop/initData')
    },
    methods: {
        ...mapMutations('shop', [
            'updateSetting',
            'setLoadingStatus',
            'setLoadingProgress',
            'clearLoadingProgress',
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
    }
}
</script>

<style lang="scss">
    // NOTIFICATION
    .vue-notification {
      font-size: 14px;
    }

    .mt-30 {
        margin-top: 30px;
    }
</style>
