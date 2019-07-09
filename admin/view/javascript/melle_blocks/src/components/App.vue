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

                <item />

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
import Item from './Item.vue'

export default {
    components: {
        Loading,
        'page-header': PageHeader,
        'breadcrumbs': Breadcrumbs,
        'panel-default': Panel,
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
        ]),
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
            const data = this.serializeItemForm()
            console.log(data);
            this.saveItem(data)
        },
        saveAndGo() {
            const data = this.serializeItemForm()

            this.saveItem(data)
            .then(() => {
                setTimeout(() => {
                    window.location.href = this.cancel
                }, 1500)
            })
        },
        serializeItemForm() {
            const arr = $('#mb-form').serializeArray()
            let data = {}

            arr.forEach((item) => {
                data[item.name] = item.value
            })

            return data
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
