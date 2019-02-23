<template>
    <div class="table-responsive">
        <table v-if="!emptyItems" class="table">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Название</td>
                    <td></td>
                </tr>
            </thead>
            <tbody style="font-size: 15px;">
                <tr v-for="d in items">
                    <td>{{ d.image_id }}</td>
                    <td>{{ d.name }}</td>
                    <td style="font-size: 18px;">
                        <i @click="editItem(d.image_id)" class="fa fa-pencil" aria-hidden="true" style="cursor: pointer;"></i>
                        <i @click="removeItem(d.image_id)" class="fa fa-trash" aria-hidden="true" style="cursor: pointer; margin-left: 20px;color: #b12a1a;"></i>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="emptyItems" class="alert alert-info" role="alert">
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
            'items',
        ]),

        emptyItems() {
            return isEmpty(this.items)
        },
    },
    methods: {
        ...mapActions('shop', [
            'editItem',
            'flipItem',
            'removeItem',
        ]),
    },
}
</script>

<style lang="scss">

</style>
