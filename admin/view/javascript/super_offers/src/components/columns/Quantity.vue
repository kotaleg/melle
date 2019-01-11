<template>
    <input @change="updateValue"
        type="text"
        :name="getInputName"
        :value="getInputValue"
        :placeholder="text_quantity"
        class="form-control" />
</template>

<script type="text/javascript">
import { mapState, mapActions } from 'vuex'

export default {
    props: {
        combid: {
            type: Number,
            required: true,
        },
    },
    computed: {
        ...mapState('shop', [
            'text_quantity',
            'combinations_data',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][quantity]'
        },
        getInputValue() {
            return this.combinations_data[this.combid].quantity
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationValue',
        ]),

        updateValue(e) {
            this.updateCombinationValue({
                combination_id: this.combid,
                key: 'quantity',
                value: parseInt(e.srcElement.value),
            })
        },
    }
}
</script>
