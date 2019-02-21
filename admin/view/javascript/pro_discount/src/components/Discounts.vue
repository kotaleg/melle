<template>
    <div class="table-responsive">
        <table v-if="!emptyDiscounts" class="table">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Название</td>
                    <td>Тип</td>
                    <td>Сортировка</td>
                    <td>Статус</td>
                    <td></td>
                </tr>
            </thead>
            <tbody style="font-size: 15px;">
                <tr v-for="d in discounts">
                    <td>{{ d.discount_id }}</td>
                    <td>{{ d.name }}</td>
                    <td>{{ d.type_name }}</td>
                    <td>{{ d.sort_order }}</td>
                    <td style="font-size: 18px;">
                        <i @click="flipDiscount(d.discount_id)" v-if="d.status" class="fa fa-check-circle" aria-hidden="true" style="cursor: pointer;color: #327a32;"></i>
                        <i @click="flipDiscount(d.discount_id)" v-else class="fa fa-times-circle" aria-hidden="true" style="cursor: pointer;color: #b12a1a;"></i>
                    </td>
                    <td style="font-size: 18px;">
                        <i @click="editDiscount(d.discount_id)" class="fa fa-pencil" aria-hidden="true" style="cursor: pointer;"></i>
                        <i @click="removeDiscount(d.discount_id)" class="fa fa-trash" aria-hidden="true" style="cursor: pointer; margin-left: 20px;color: #b12a1a;"></i>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="emptyDiscounts" class="alert alert-info" role="alert">
            У вас пока нет скидок
        </div>
    </div>
</template>

<script>
import { isEmpty  } from 'lodash'
import { mapState, mapGetters, mapActions } from 'vuex'

export default {
    components: {

    },
    computed: {
        ...mapState('shop', [
            'discounts',
        ]),

        emptyDiscounts() {
            return isEmpty(this.discounts)
        },
    },
    methods: {
        ...mapActions('shop', [
            'editDiscount',
            'flipDiscount',
            'removeDiscount',
        ]),
    },
}
</script>

<style lang="scss">

</style>
