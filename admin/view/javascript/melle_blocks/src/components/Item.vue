<template>
    <form class="form-horizontal" id="mb-form">
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
            <label class="col-sm-3 col-lg-2 control-label">Название</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="name" placeholder="Название" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Высота</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="height" placeholder="Высота" class="form-control">
            </div>
        </div>

        <hr/>
        <div class="col-sm-12 melleb-pb">
            <progress-bar
                size="large"
                :val="widthCount"
                :text="widthDescription" />
        </div>

        <div class="col-sm-6"><h2>Блоки</h2></div>
        <div class="col-sm-6 text-right">
            <button @click="showBlockTypes" type="button" class="btn btn-info">Добавить блок</button>
        </div>

        <div class="col-sm-12">
            <div v-for="(b, i) in blocks" class="melleb-block row">
                <hr>
                <div class="col-sm-6">{{ b.typeDescription }}</div>
                <div class="col-sm-6 text-right">
                    <button @click="removeBlock(i)" type="button" class="btn btn-danger">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="col-sm-12">

                    <component
                        v-bind:is="getBlockFields(b.type)"
                        :index="i"
                        :block="b" />

                </div>
            </div>
        </div>


        <pick-block-type-modal dir="ltr"
            :width="500"
            :scrollable="false" />
    </form>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import ProgressBar from 'vue-simple-progress'
import PickBlockTypeModal from './modal/PickBlockTypeModal.vue'
import TypeOne from './block/TypeOne.vue'
import TypeTwo from './block/TypeTwo.vue'
import TypeThree from './block/TypeThree.vue'

export default {
    components: {
        ProgressBar,
        PickBlockTypeModal,
    },
    computed: {
        ...mapState('shop', [
            'text_cancel',
            'text_status',
            'widthCount',
            'blocks',
        ]),
        ...mapGetters('shop', [
            'getToggleStates',
            'getItemValue',
        ]),

        widthDescription() {
            return `ширина ${this.widthCount}%`
        },

        name: {
            get () { return this.getItemValue('name') },
            set (v) { this.updateItemValue({k: 'name', v}) }
        },
        height: {
            get () { return this.getItemValue('height') },
            set (v) { this.updateItemValue({k: 'height', v}) }
        },
        status: {
            get () { return this.getItemValue('status') },
            set (v) { this.updateItemValue({k: 'status', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateItemValue',
            'removeBlock',
        ]),

        showBlockTypes() {
            this.$modal.show('pick-block-type-modal', {});
        },
        getBlockFields(type) {
            console.log(type);
            const blocksFields = {
                'type-1': TypeOne,
                'type-2': TypeTwo,
                'type-3': TypeThree,
            }
            return blocksFields[type]
        },
    },
}
</script>

<style lang="scss">
.melleb-pb {
    margin-bottom: 20px;
}
</style>
