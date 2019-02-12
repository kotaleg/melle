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
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
                        <file-upload
                            :url="upload_seo_file"
                            accept=".xml"
                            btn-label="Выберите SEO файл"
                            btn-uploading-label="Обработка.."
                            @change="onFileChange"
                            @success="onFileSuccess"
                            @error="onFileError" />
                    </div>
                </form>

                <div v-if="imports">
                    <hr>

                    <div v-for="(imp, k) in imports" class="import-box">
                        <div class="col-sm-12 imp-head">
                            <h3>Прогресс № {{ imp.id }} <i v-if="is_updating" class="fa fa-cog fa-spin fa-fw"></i></h3>
                            <span>Загружено файлов: {{ imp.files_uploaded }}</span>
                            <span>Обработано файлов: {{ imp.files_processed }}</span>
                        </div>

                        <div class="col-md-6">
                            <h4>Вызовы:</h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                      <tr>
                                        <th>Тип</th>
                                        <th>Режим</th>
                                        <th>Файл</th>
                                        <th>Дата</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(act) in imp.actions">
                                            <td>{{ act.type }}</td>
                                            <td>{{ act.mode }}</td>
                                            <td>{{ act.filename }}</td>
                                            <td>{{ act.create_date }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <br>
                        <div class="col-md-6">
                            <h4>Логи:</h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                      <tr>
                                        <th>Сообщение</th>
                                        <th>Дата</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(log) in imp.logs" :class="log.type">
                                            <td>{{ log.message }}</td>
                                            <td>{{ log.create_date }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </panel-default>
        </div>

    </div>
</template>

<script>
import { isUndefined, extend } from 'lodash'
import { mapState, mapGetters, mapActions } from 'vuex'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'
import ProgressBar from 'vue-simple-progress'

import shop from '../api/shop'
import notify from './partial/notify'
import PageHeader from './partial/PageHeader.vue'
import Breadcrumbs from './partial/Breadcrumbs.vue'
import Panel from './partial/Panel.vue'

export default {
    components: {
        ProgressBar,
        Loading,
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
            'text_logs',
            'text_actions',

            'cancel',
            'save',
            'setting',
            'is_loading',
            'is_updating',
            'imports',
            'upload_seo_file',
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
    data () {
        return {
            polling: null,
        }
    },
    methods: {
        ...mapActions('shop', [
            'updateSetting',
            'setLoadingStatus',
            'fetchImports',
            'importSEOData',
        ]),

        pollData () {
            this.polling = setInterval(() => {
                this.fetchImports()
            }, 3000)
        },

        onFileChange(res) {
            notify.messageHandler(res)
            this.setLoadingStatus(false)
        },
        onFileSuccess(e) {
            this.importSEOData()
        },
        onFileError() {
            console.log('UPLOAD FILE ERROR');
        },

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
    },
    created() {
        this.$store.dispatch('shop/initData')
        this.pollData()
    },
    beforeDestroy () {
        clearInterval(this.polling)
    },
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

.import-box {
    .imp-head {
        margin-bottom: 20px;
    }
    margin-bottom: 50px;
}

.file-upload {
    .input-wrapper {
        height: 45px !important;
        .file-upload-label {
            .file-upload-icon {
                display: none !important;
            }
        }
    }
}
</style>
