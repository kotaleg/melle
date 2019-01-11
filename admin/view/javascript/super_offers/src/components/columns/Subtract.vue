<template>
    <select @change="updateValue"
        :name="getInputName"
        class="form-control">
            <option value="1" :selected="getInputValue">{{ text_yes }}</option>
            <option value="0" :selected="!getInputValue">{{ text_no }}</option>
    </select>
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
            'text_yes',
            'text_no',
        ]),
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][subtract]'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, 'subtract')
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationValue',
        ]),

        updateValue(e) {
            let value = e.srcElement.options[e.srcElement.selectedIndex].value;

            this.updateCombinationValue({
                combination_id: this.combid,
                key: 'subtract',
                value: (value == 1) ? true : false,
            })
        },
    }
}
</script>
