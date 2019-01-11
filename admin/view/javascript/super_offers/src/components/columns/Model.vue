<template>
    <input @change="updateValue"
        type="text"
        :name="getInputName"
        :value="getInputValue"
        :placeholder="text_model"
        class="form-control" />
</template>

<script type="text/javascript">
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    props: {
        combid: {
            type: Number,
            required: true,
        },
    },
    computed: {
        ...mapState('shop', [
            'text_model',
        ]),
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][model]'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, 'model')
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationValue',
        ]),

        updateValue(e) {
            this.updateCombinationValue({
                combination_id: this.combid,
                key: 'model',
                value: parseInt(e.srcElement.value),
            })
        },
    }
}
</script>
