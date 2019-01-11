<template>
    <input @change="updateValue"
        type="text"
        :name="getInputName"
        :value="getInputValue"
        :placeholder="text_quantity"
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
            'text_quantity',
        ]),
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][quantity]'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, 'quantity')
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
