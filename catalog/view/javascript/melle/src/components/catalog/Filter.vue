<template>
   <section class="sidebar">
      <div class="search-modal__filter filter">
         <div class="filter__title">Фильтр</div>
         <form>
            <div class="filter__relevant-category">
               <label>
                  <input name="CatalogFilterForm[hits]" type="checkbox">
                  <span></span><span>Хиты</span>
               </label>
               <label>
                  <input name="CatalogFilterForm[news]" type="checkbox">
                  <span></span><span>Новинки</span>
               </label>
               <label>
                  <input name="CatalogFilterForm[actions]" type="checkbox">
                  <span></span><span>Акции</span>
               </label>
            </div>
         </form>
      </div>
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
            'isFilterChanged',
        ]),
        ...mapState('filter', [
            'filter_data',
            'last_filter',
        ]),
    },
    methods: {

    },
}
</script>

<style lang="scss">

</style>