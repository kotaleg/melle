<template>
    <div class="row">
        <notifications
            :group="this.$codename"
            position="bottom right"/>

        <div class="col-sm-2">
            <ul class="nav nav-pills nav-stacked">
                <li @click="openOriginalOptions"
                    v-for="(opt, k) in options"
                    :key="k+'-option'"
                    class="option-handler active">
                    <a data-toggle="tab"><i class="fa fa-minus-circle"></i> {{opt.name}}</a>
                </li>
            </ul>
        </div>

        <div class="col-sm-10">
            <div>
                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <tr v-if="combinations">
                            <td v-for="(ac, k) in active_columns"
                                :key="k+'-ac'"
                                v-if="ac.active && (isUndefined(ac.code) || !isUndefined(ac.code))"
                                class="text-left">
                                <input type="hidden" name="so_columns[{k}]" value="{(ac.active) ? '1' : '0'}">
                                {{ac.name}}
                            </td>
                            <td v-if="active_columns"></td>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(option, id) in combinations"
                            :key="id+'-combination'"
                            :class="[{'hide': !isUndefined(combinations_data[id]) && combinations_data[id].hided_by_filter}]">

                            <input type="hidden" name="combination" :value="id">

                            <td v-for="(ac, k) in active_columns" :key="k+'-acv'"
                                v-if="ac.active && (isUndefined(ac.code) || !isUndefined(ac.code))">
                                <so_custom v-if="isUndefined(ac.code)" :combid="id" :colid="k" />
                                <so_model v-if="ac.code == 'model'" :combid="id" :colid="k" />
                                <so_price v-if="ac.code == 'price'" :combid="id" :colid="k" />
                                <so_quantity v-if="ac.code == 'quantity'" :combid="id" :colid="k" />
                                <so_subtract v-if="ac.code == 'subtract'" :combid="id" :colid="k" />
                            </td>

                            <td>
                                <div @click="deleteCombination" class="btn btn-danger">
                                    <i class="fa fa-minus-circle"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <tr id="so-not-found"
                            :class="[{'hide': !combinations || !options}]"
                            <td :colspan="full_colspan" class="text-center"><span v-if="!options">{{text_no_options}}</span><span v-if="options && !combinations">{{text_no_combinations}}</span></td>
                        </tr>
                        <tr v-if="options">
                            <td id="so-colspan" :colspan="full_colspan - 1"></td>
                            <td class="text-left">
                                <div @click="addCombination" type="button" id="so-add" data-toggle="tooltip" class="btn btn-primary" :title="button_add_option"><i class="fa fa-plus-circle"></i></div>
                            </td>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>

        <div @click="updateTrigger" id="trigger_so_update" style="display: none;"></div>
    </div>
</template>

<script>
import { isUndefined } from 'lodash'
import { mapState, mapGetters, mapActions } from 'vuex'

import shop from '../api/shop'
import notify from './partial/notify'
import Custom from './columns/Custom.vue'
import Model from './columns/Model.vue'
import Price from './columns/Price.vue'
import Quantity from './columns/Quantity.vue'
import Subtract from './columns/Subtract.vue'

export default {
    components: {
        'so_custom': Custom,
        'so_model': Model,
        'so_price': Price,
        'so_quantity': Quantity,
        'so_subtract': Subtract,
    },
    computed: {
        ...mapState('shop', [
            'is_loading',
            'options',
            'combinations',
            'active_columns',
            'combinations_data',
            'full_colspan',

            'text_no_options',
            'text_no_combinations',

            'button_add_option',
        ]),
    },
    created() {
        this.$store.dispatch('shop/initData')
    },
    methods: {
        ...mapActions('shop', [
            'setLoadingStatus',
            'addCombination',
        ]),

        openOriginalOptions() {
            $('a[href$="#tab-option"]').click();
        },
        deleteCombination(id) {

        },
        updateTrigger() {

        },
        isUndefined(value) {
            return isUndefined(value)
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

    .option-handler {
        cursor: pointer;
    }
    .not-option {
        background: #f9fbff;
    }
</style>
