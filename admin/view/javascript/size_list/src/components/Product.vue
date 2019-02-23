<template>
    <div>
        <notifications
            :group="this.$codename"
            position="bottom right"/>

        <loading
            :active.sync="is_loading"
            :is-full-page="true" />

        <div class="form-group marked-shit">
            <label class="col-sm-3 col-lg-2 control-label">Таблица размеров</label>
            <div class="col-sm-9">
                <treeselect
                    :multiple="false"
                    :maxHeight="400"
                    :alwaysOpen="false"
                    placeholder="Таблица размеров"
                    :noResultsText="text_no_results"
                    :loadingText="text_loading"
                    :defaultOptions="size_shit"
                    :load-options="loadShit"
                    :async="true"
                    v-model="product_item" />
            </div>
        </div>
    </div>
</template>

<script>
import { isEmpty, isString, trim } from 'lodash'
import { mapState, mapActions, mapGetters } from 'vuex'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { ASYNC_SEARCH } from '@riophae/vue-treeselect'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'

export default {
    components: {
        Treeselect,
        Loading,
    },
    computed: {
        ...mapState('shop', [
            'text_cancel',
            'text_no_results',
            'text_loading',

            'is_loading',
            'size_shit',
        ]),
        ...mapGetters('shop', [
            'getItemValue',
            'getValue',
        ]),

        product_item: {
            get () { return this.getValue('product_item') },
            set (v) { this.updateValue({k: 'product_item', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateItemValue',
            'updateValue',
            'searchShitRequest',
        ]),

        loadShit({ action, searchQuery, callback }) {
            if (action === ASYNC_SEARCH) {
                this.searchShitRequest(searchQuery)
                    .then(res => {
                        callback(null, res)
                    })
            }
        },
    },
    created() {
        this.$store.dispatch('shop/initData')
    },
}
</script>

<style lang="scss">

</style>
