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
                    @click="saveAndStay"
                    data-toggle="tooltip"
                    :title="button_save_and_stay"
                    class="btn btn-success">
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

                <form class="form-horizontal">
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
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_language }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <select class="form-control" v-model="languageCode">
                                <option v-for="l in languages"
                                    :value="l.code">{{ l.code }}</option>
                            </select>
                        </div>
                    </div>
                </form>

                <categories />

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
import Categories from './Categories.vue'

export default {
    components: {
        Loading,
        'page-header': PageHeader,
        'breadcrumbs': Breadcrumbs,
        'panel-default': Panel,
        Categories,
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
            'text_language',

            'cancel',
            'save',
            'setting',
            'is_loading',
            'languages',
        ]),
        ...mapGetters('shop', [
            'getToggleStates',
            'getSettingValue',
        ]),
        status: {
            get () { return this.getSettingValue('status') },
            set (v) { this.updateSetting({k: 'status', v}) }
        },
        languageCode: {
            get () { return this.getSettingValue('languageCode') },
            set (v) { this.updateSetting({k: 'languageCode', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'setLoadingStatus',
            'updateSetting',
        ]),

        saveAndStay() {
            this.setLoadingStatus(true)
            const data = extend({}, this.setting, { url: this.save })
            shop.makeRequest(data, res => {
                this.setLoadingStatus(false)
                notify.messageHandler(res.data)
            })
        },
        cancelRoutine() {
            window.location.href = this.cancel
        }
    },
    created() {
        this.$store.dispatch('shop/initData')
    },
}
</script>

<style lang="scss">
// NOTIFICATION
.vue-notification {
  font-size: 14px;
}
</style>
