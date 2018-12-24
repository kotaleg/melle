<template>
    <div>
        <notifications
            group="pro_related_shuffle"
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
                    <div class="form-group clearfix">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_product_number }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <input
                                type="text"
                                class="form-control"
                                v-model="product_number">
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_proceed_count }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <input
                                type="text"
                                class="form-control"
                                v-model="proceed_count">
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_most_close_only }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <toggle-button
                                v-model="most_close_only"
                                :width="80"
                                :height="25"
                                :labels="getToggleStates"/>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="col-sm-3 col-lg-2 control-label">{{ text_relate_new_product }}</label>
                        <div class="col-sm-9 col-lg-5">
                            <toggle-button
                                v-model="relate_new_product"
                                :width="80"
                                :height="25"
                                :labels="getToggleStates"/>
                        </div>
                    </div>
                </form>

                <hr>

                <div v-if="somethingLoading">
                    <progress-bar
                        size="large"
                        :val="loading_progress"
                        :text="loading_message" />
                </div>

                <div class="text-center mt-30">
                    <button
                        @click="shuffleAll"
                        class="btn btn-primary"
                        :disabled="somethingLoading">
                        <i v-if="somethingLoading" class="fa fa-cog fa-spin fa-fw"></i>
                        {{ text_reshuffle_all }}
                    </button>

                    <button v-if="somethingLoading"
                        @click="cancelAll"
                        class="btn btn-default">
                        {{ text_cancel }}
                    </button>
                </div>


            </panel-default>
        </div>

        <v-dialog/>
    </div>
</template>

<script>
import shop from '../api/shop'
import { isUndefined, extend } from 'lodash'
import { mapState, mapGetters, mapMutations } from 'vuex'
import ProgressBar from 'vue-simple-progress'
import PageHeader from './partial/PageHeader.vue'
import Breadcrumbs from './partial/Breadcrumbs.vue'
import Panel from './partial/Panel.vue'

export default {
    components: {
        ProgressBar,
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
            'text_reshuffle_all',
            'text_product_number',
            'text_proceed_count',
            'text_are_you_sure',
            'text_most_close_only',
            'text_relate_new_product',

            'cancel',
            'save',
            'setting',
            'shuffle_all_products',
            'cancel_shuffle',
            'somethingLoading',
            'loading_progress',
            'loading_message',
        ]),
        ...mapGetters('shop', [
            'getToggleStates',
            'getSettingValue',
        ]),
        status: {
            get () { return this.getSettingValue('status') },
            set (value) { this.updateSetting({index: 'status', value}) }
        },
        product_number: {
            get () { return this.getSettingValue('product_number') },
            set (value) { this.updateSetting({index: 'product_number', value}) }
        },
        proceed_count: {
            get () { return this.getSettingValue('proceed_count') },
            set (value) { this.updateSetting({index: 'proceed_count', value}) }
        },
        most_close_only: {
            get () { return this.getSettingValue('most_close_only') },
            set (value) { this.updateSetting({index: 'most_close_only', value}) }
        },
        relate_new_product: {
            get () { return this.getSettingValue('relate_new_product') },
            set (value) { this.updateSetting({index: 'relate_new_product', value}) }
        }
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

        shuffleAll() {
            this.$modal.show('dialog', {
                title: this.text_warning,
                text: this.text_are_you_sure,
                buttons: [
                    {
                        title: this.text_reshuffle_all,
                        handler: () => {
                            this.setLoadingStatus(true)
                            this.$modal.hide('dialog')
                            this.shuffleAllRequest()
                        }
                    },
                    {
                        title: this.text_close,
                        default: true,
                    }
                ]
            })
        },

        shuffleAllRequest() {
            shop.operateAllRequest(
                {url: this.shuffle_all_products},
                res => {
                    if (isUndefined(res.data.continue) || res.data.continue == false) {
                        this.setLoadingStatus(false)
                        this.clearLoadingProgress()
                    } else {
                        this.setLoadingStatus(true)
                        this.setLoadingProgress({
                            progress: res.data.loading_progress,
                            message: res.data.loading_message,
                        })
                        this.shuffleAllRequest()
                    }
                    this.messageHandler(res)
                },
                res => {
                    this.setLoadingStatus(false)
                    this.messageHandler(res)
                }
            )
        },

        cancelAll() {
            shop.operateAllRequest(
                {url: this.cancel_shuffle},
                res => {
                    this.messageHandler(res)
                },
                res => {
                    this.setLoadingStatus(false)
                    this.messageHandler(res)
                }
            )
        },

        saveAndStay() {
            let data = extend({}, this.setting, {url: this.save})
            shop.postSettingData(
                data,
                res => this.messageHandler(res)
            )
        },
        saveAndGo() {
            let data = extend({}, this.setting, {url: this.save})
            shop.postSettingData(
                data,
                res => {
                    this.messageHandler(res)

                    setTimeout(() => {
                        window.location.href = this.cancel;
                    }, 1500)
                }
            )
        },

        messageHandler(response) {
            if (isUndefined(response.data)) { return; }
            if (response.data.success) {
                response.data.success.forEach(function(element) {
                    this.$notify({
                        group: this.$codename,
                        type: 'success',
                        title: 'Success',
                        text: element
                    });
                }, this);
            } else if (response.data.error) {
                response.data.error.forEach(function(element) {
                    this.$notify({
                        group: this.$codename,
                        type: 'warn',
                        title: 'Success',
                        text: element
                    });
                }, this);
            } else if (response.data.message) {
                response.data.message.forEach(function(element) {
                    this.$notify({
                        group: this.$codename,
                        title: 'Info',
                        text: element
                    });
                }, this);
            }
        }
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
