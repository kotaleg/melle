<template>
<div class="row">
    <div class="col-sm-6" style="min-height: 340px;">

        <div class="col-sm-12 store-categories">
            <treeselect
                v-if="storeCategories"
                :multiple="true"
                :searchable="false"
                :maxHeight="300"
                :zIndex="995"
                :alwaysOpen="true"
                openDirection="below"
                :options="storeCategories"
                placeholder="Категории в магазине"
                v-model="selectedStoreCategories"
            />
        </div>

        <div class="col-sm-12 google-categories">
            <treeselect
                v-if="categories"
                :multiple="false"
                :searchable="false"
                :maxHeight="300"
                :zIndex="995"
                :alwaysOpen="true"
                openDirection="below"
                :options="categories"
                placeholder="Категории Google"
                v-model="selectedCategory"
            />
        </div>

    </div>
    <div class="col-sm-6">

        <div :class="['alert', {'alert-info': categoriesCount > 0, 'alert-warning': categoriesCount <= 0}]">
            <div class="col-sm-8">В базе данных <b>{{ categoriesCount }}</b> категорий Google</div>
            <div class="col-sm-4 text-right"><button @click="updateCategoriesRequest" class="btn btn-info btn-sm">Обновить</button></div>
        </div>

        <div :class="['alert', {'alert-info': unlinkedCount <= 0, 'alert-warning': unlinkedCount > 0}]">
            <div class="col-sm-12">У вас в магазине <b>{{ unlinkedCount }}</b> неприсвоенных категорий</div>
        </div>

        <div class="alert alert-info">Выберите неприсвоенные категории из списка, выберите гугл категории в списке ниже и нажмите "Применить"</div>

        <div class="cat-fee-section text-center">
            <button @click="applyLinkRequest" class="btn btn-success">Применить</button>
            <button @click="clearSelectionRequest" class="btn btn-danger">Очистить все</button>
        </div>
    </div>
</div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

export default {
    components: {
        Treeselect,
    },
    computed: {
        ...mapState('shop', [
            'categoriesCount',
            'unlinkedCount',
            'categories',
            'storeCategories',
        ]),
        ...mapGetters('shop', [
            'getValue',
        ]),

        selectedCategory: {
            get () { return this.getValue('selectedCategory') },
            set (v) { this.updateValue({k: 'selectedCategory', v}) }
        },

        selectedStoreCategories: {
            get () { return this.getValue('selectedStoreCategories') },
            set (v) { this.updateValue({k: 'selectedStoreCategories', v}) }
        },

    },
    methods: {
        ...mapActions('shop', [
            'updateValue',
            'updateCategoriesRequest',
            'applyLinkRequest',
            'clearSelectionRequest',
        ]),

    },
    created() {

    },
}
</script>

<style lang="scss">
.store-categories,
.google-categories {
    height: 350px;
}
</style>