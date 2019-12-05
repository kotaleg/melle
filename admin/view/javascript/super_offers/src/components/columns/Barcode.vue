<template>
    <input @change="updateValue"
        type="text"
        :name="getInputName"
        :value="getInputValue"
        :placeholder="text_barcode"
        class="form-control"
        readonly />
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
            'text_barcode',
        ]),
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][barcode]'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, 'barcode')
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationValue',
        ]),

        updateValue(e) {
            this.updateCombinationValue({
                combination_id: this.combid,
                key: 'barcode',
                value: parseInt(e.srcElement.value),
            })
        },
    }
}
</script>
