<template>
   <section class="filter-cat">
      <form>

         <div class="filter-cat__name desktop">
            <label>
               сортировать по:

               <v-select
                    class="filter sortProducts"
                    style="display: inline-block;"
                    v-model="sort"
                    :options="getFilterValue('all_sorts')"
                    placeholder="Сортировать по"
                    :searchable="false"
                    :closeOnSelect="true"
                    maxHeight="200px">
                    <span slot="no-options"></span>
                </v-select>

            </label>
         </div>

         <div @click="flipSortOrder()" class="filter-cat__button desktop">
            <label>
               <svg viewBox="0 0 489.2 489.2" width="20" height="20">
                  <path d="M481.044,382.5c0-6.8-5.5-12.3-12.3-12.3h-418.7l73.6-73.6c4.8-4.8,4.8-12.5,0-17.3c-4.8-4.8-12.5-4.8-17.3,0l-94.5,94.5c-4.8,4.8-4.8,12.5,0,17.3l94.5,94.5c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6c4.8-4.8,4.8-12.5,0-17.3l-73.6-73.6h418.8C475.544,394.7,481.044,389.3,481.044,382.5z"></path>
                  <path d="M477.444,98l-94.5-94.4c-4.8-4.8-12.5-4.8-17.3,0s-4.8,12.5,0,17.3l73.6,73.6h-418.8c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h418.8l-73.6,73.4c-4.8,4.8-4.8,12.5,0,17.3c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6l94.5-94.5C482.244,110.6,482.244,102.8,477.444,98z"></path>
               </svg>
               <button type="button"></button>
            </label>
         </div>

         <details class="filter-cat-mobile">
            <summary @click="enableElement('filter')">Фильтр</summary>
         </details>

         <details class="filter-cat__mobile">
            <summary>Сортировка</summary>
            <div class="filter-cat__name">
               <label>
                  сортировать по:

                    <v-select
                        class="filter sortProducts"
                        style="display: inline-block;"
                        v-model="sort"
                        :options="getFilterValue('all_sorts')"
                        placeholder="Сортировать по"
                        :searchable="false"
                        :closeOnSelect="true"
                        maxHeight="200px">
                        <span slot="no-options"></span>
                    </v-select>

               </label>
            </div>
            <div @click="flipSortOrder()" class="filter-cat__button">
               <label>
                  <svg viewBox="0 0 489.2 489.2" width="20" height="20">
                     <path d="M481.044,382.5c0-6.8-5.5-12.3-12.3-12.3h-418.7l73.6-73.6c4.8-4.8,4.8-12.5,0-17.3c-4.8-4.8-12.5-4.8-17.3,0l-94.5,94.5c-4.8,4.8-4.8,12.5,0,17.3l94.5,94.5c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6c4.8-4.8,4.8-12.5,0-17.3l-73.6-73.6h418.8C475.544,394.7,481.044,389.3,481.044,382.5z"></path>
                     <path d="M477.444,98l-94.5-94.4c-4.8-4.8-12.5-4.8-17.3,0s-4.8,12.5,0,17.3l73.6,73.6h-418.8c-6.8,0-12.3,5.5-12.3,12.3s5.5,12.3,12.3,12.3h418.8l-73.6,73.4c-4.8,4.8-4.8,12.5,0,17.3c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6l94.5-94.5C482.244,110.6,482.244,102.8,477.444,98z"></path>
                  </svg>
                  <button type="button"></button>
               </label>
            </div>
         </details>

      </form>
   </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

import vueSlider from 'vue-slider-component'

export default {
    components: {
        vueSlider,
    },
    computed: {
        ...mapGetters('filter', [
            'getFilterValue',
        ]),

        sort: {
            get () { return this.getFilterValue('sort') },
            set (v) { this.updateFilterValue({k: 'sort', v}) }
        },
    },
    methods: {
        ...mapActions('header', [
            'enableElement',
        ]),
        ...mapActions('filter', [
            'flipSortOrder',
            'updateFilterValue',
        ]),
    },
}
</script>

<style lang="scss">

</style>