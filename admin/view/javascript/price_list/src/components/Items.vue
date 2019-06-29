<template>
    <div class="table-responsive">
        <table v-if="!emptyItems" class="table">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Название</td>
                    <td>Путь</td>
                    <td>Кол-во скачиваний</td>
                    <td>Порядок</td>
                    <td>Статус</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="d in items">
                    <td>{{ d._id }}</td>
                    <td>{{ d.title }}</td>
                    <td>{{ d.filePath }}</td>
                    <td>{{ d.downloadCount }}</td>
                    <td>{{ d.sortOrder }}</td>
                    <td @click="flipItem(d._id)">
                        <i v-if="d.status" class="fa fa-check-circle" />
                        <i v-else class="fa fa-times-circle" />
                    </td>
                    <td class="edit-btns text-center">
                        <i @click="editItem(d.filePath)" class="fa fa-pencil" />
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="emptyItems" class="alert alert-info" role="alert">
            У вас пока нет ни одного прайс листа. Загрузите <b>.xls</b> файлы в папку <b>{{ workFolder }}</b> и перезагрузите страницу.
        </div>
    </div>
</template>

<script>
import { isEmpty } from 'lodash'
import { mapState, mapGetters, mapActions } from 'vuex'

export default {
    components: {

    },
    computed: {
        ...mapState('shop', [
            'items',
            'workFolder',
        ]),

        emptyItems() {
            return isEmpty(this.items)
        },
    },
    methods: {
        ...mapActions('shop', [
            'editItem',
            'flipItem',
        ]),
    },
}
</script>

<style lang="scss" scoped>
tbody {
    font-size: 15px;
}
.edit-btns {
    font-size: 18px;
}
i {
    cursor: pointer;
}

.fa-trash {
    margin-left: 20px;
    color: #b12a1a;
}
.fa-check-circle {
    color: #327a32;
}
.fa-times-circle {
    color: #b12a1a;
}
</style>
