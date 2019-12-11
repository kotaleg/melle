<template>
    <input @change="updateValue"
        type="hidden"
        :name="getInputName"
        :value="getInputValue"
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
        name: {
            type: String,
            required: true,
        },
    },
    computed: {
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+']['+this.name+']'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, this.name)
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationValue',
        ]),

        updateValue(e) {
            this.updateCombinationValue({
                combination_id: this.combid,
                key: this.name,
                value: parseInt(e.srcElement.value),
            })
        },
    }
}
</script>
