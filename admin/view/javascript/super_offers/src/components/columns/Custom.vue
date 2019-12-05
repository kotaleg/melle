<template>
    <select @change="changeSomeShit"
        :name="getSelectName"
        class="form-control"
        readonly>

        <option v-for="(o, i) in weirdStuff[acOptionId]"
            :value="o.id"
            :data-codename="o.codename"
            :selected="o.active">{{o.name}}</option>
    </select>
</template>

<script type="text/javascript">
import {forEach} from 'lodash'
import { mapState, mapActions } from 'vuex'

export default {
    props: {
        combid: {
            type: Number,
            required: true,
        },
        colid: {
            type: Number,
            required: true,
        },
    },
    computed: {
        ...mapState('shop', [
            'options',
            'active_columns',
            'option_values',
            'combinations',
        ]),
        getSelectName() {
            return 'so_combination['+this.combid+']['+this.acOptionId+'__'+this.oOptionId+']'
        },
        acOptionId() {
            return this.active_columns[this.colid].option_id
        },
        oOptionId() {
            return this.options[this.acOptionId].option_id
        },
        weirdStuff() {
            let shit = {};

            forEach(this.options, (o_element, o_index) => {
                let povs = [];

                forEach(o_element.product_option_value, (v_element, v_index) => {
                    let pov = {
                        'id': v_element.option_value_id,
                        'codename': v_index,
                        'active': false,
                        'name': false,
                    }

                    forEach(this.option_values, (ov_element, ov_index) => {
                        ov_element.forEach((ove_element, ove_index) => {
                            if (ove_element.option_value_id == pov.id) {
                                pov.name = ove_element.name
                            }
                        })
                    })

                    forEach(this.combinations[this.combid], (c_element, c_index) => {
                        if ((c_index == o_index) && (c_element == v_index)) {
                            pov.active = true
                        }
                    })

                    povs.push(pov)
                })

                shit[o_index] = povs
            })

            return shit;
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateCombinationActiveOptionCodename',
        ]),

        changeSomeShit(e) {
            let codename = e.srcElement.options[e.srcElement.selectedIndex].dataset.codename
            this.updateCombinationActiveOptionCodename({
                combination_id: this.combid,
                active_option_id: this.acOptionId,
                codename: codename,
            })
        },
    }
}
</script>
