<template>
   <div class="search-form">
      <form method="get" class="search-form__form ng-pristine ng-valid" v-on:submit.prevent="searchIt()">
         <input name="q" type="text" placeholder="Введите название товара" class="q search-form__input ui-autocomplete-input" autocomplete="off" v-model="search"><span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
         <button type="submit" class="search-form__send">
            <svg viewBox="0 0 512 512" width="17" height="17">
               <path d="m495,466.1l-110.1-110.1c31.1-37.7 48-84.6 48-134 0-56.4-21.9-109.3-61.8-149.2-39.8-39.9-92.8-61.8-149.1-61.8-56.3,0-109.3,21.9-149.2,61.8-39.9,39.8-61.8,92.8-61.8,149.2 0,56.3 21.9,109.3 61.8,149.2 39.8,39.8 92.8,61.8 149.2,61.8 49.5,0 96.4-16.9 134-48l110.1,110c8,8 20.9,8 28.9,0 8-8 8-20.9 0-28.9zm-393.3-123.9c-32.2-32.1-49.9-74.8-49.9-120.2 0-45.4 17.7-88.2 49.8-120.3 32.1-32.1 74.8-49.8 120.3-49.8 45.4,0 88.2,17.7 120.3,49.8 32.1,32.1 49.8,74.8 49.8,120.3 0,45.4-17.7,88.2-49.8,120.3-32.1,32.1-74.9,49.8-120.3,49.8-45.4,0-88.1-17.7-120.2-49.9z"></path>
            </svg>
         </button>
      </form>
      <div class="search-form__footer">
         <div class="search-form__info-result" id="ivan_search_count_replace">
            <span>Найдено:
            <span class="search-form__info-result-number">{{ product_total }} </span>
            <span>результатов </span>
            </span>
         </div>
      </div>

      <sort-section />
   </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

import Sort from './Sort.vue'

export default {
    components: {
      'sort-section': Sort
    },
    computed: {
        ...mapGetters('filter', [
            'getFilterValue',
        ]),
        ...mapState('catalog', [
            'product_total',
        ]),

        search: {
            get () { return this.getFilterValue('search') },
            set (v) { this.updateFilterValueWithDelay({k: 'search', v}) }
        },
    },
    methods: {
        ...mapActions('filter', [
            'updateFilterValueWithDelay',
        ]),
        ...mapActions('catalog', [
            'loadMoreRequest',
        ]),

        searchIt() {
            this.loadMoreRequest(true)
        },
    },
}
</script>

<style lang="scss">

</style>