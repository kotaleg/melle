<template>
   <section class="sidebar">
      <div class="search-modal__filter filter">
         <div class="filter__title">Фильтр</div>
         <form>
            <div class="filter__relevant-category">
               <label>
                  <input name="CatalogFilterForm[hits]" type="checkbox" v-model="hit">
                  <span></span><span>Хиты</span>
               </label>
               <label>
                  <input name="CatalogFilterForm[news]" type="checkbox" v-model="neww">
                  <span></span><span>Новинки</span>
               </label>
               <label>
                  <input name="CatalogFilterForm[actions]" type="checkbox" v-model="act">
                  <span></span><span>Акции</span>
               </label>
            </div>

            <div class="filter__price">
                <div class="filter__price-title"><span>Цена, руб</span></div>
                <div class="super-flex">
                    <div class="filter__price-start"><span>от</span>
                        <input name="CatalogFilterForm[price_from]" type="text" v-model.trim="min_price">
                    </div>
                    <div class="filter__price-end"><span>до</span>
                        <input name="CatalogFilterForm[price_to]" type="text" v-model.trim="max_price">
                    </div>
                </div>

                <vue-slider
                    ref="price_slider"
                    class="mt-14 vsc-class"
                    v-bind="getSliderOptions('price')"
                    v-model="price">
                </vue-slider>
            </div>

            <ul class="filter__list">
                <li v-for="(item, i) in filter_data.manufacturers"
                    class="filter__item">
                    <label>
                        <input
                            @click="updateManufacturerStatus(i)"
                            :checked="item.checked"
                            type="checkbox"
                            name="CatalogFilterForm[producers][]">
                        <span style="flex-shrink: 0"></span>
                        <span>
                            <label>{{ item.label }}</label>
                        </span>
                    </label>
                </li>
            </ul>

            <div class="filter__price">
                <div class="filter__price-title"><span>Ден:</span></div>
                <div class="super-flex">
                    <div class="filter__price-start"><span>от</span>
                        <input name="CatalogFilterForm[den_from]" type="text" v-model.trim="min_den">
                    </div>
                    <div class="filter__price-end"><span>до</span>
                        <input name="CatalogFilterForm[den_to]" type="text" v-model.trim="max_den">
                    </div>
                </div>

                <vue-slider
                    ref="den_slider"
                    class="mt-14 vsc-class"
                    v-bind="getSliderOptions('den')"
                    v-model="den">
                </vue-slider>
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
            'getFilterValue',
            'getSliderOptions',
        ]),
        ...mapState('filter', [
            'filter_data',
        ]),

        hit: {
            get () { return this.getFilterValue('hit') },
            set (v) { this.updateFilterValue({k: 'hit', v}) }
        },
        neww: {
            get () { return this.getFilterValue('neww') },
            set (v) { this.updateFilterValue({k: 'neww', v}) }
        },
        act: {
            get () { return this.getFilterValue('act') },
            set (v) { this.updateFilterValue({k: 'act', v}) }
        },

        min_price: {
            get () { return this.getFilterValue('min_price') },
            set (v) { this.updateFilterValue({k: 'min_price', v}) }
        },
        max_price: {
            get () { return this.getFilterValue('max_price') },
            set (v) { this.updateFilterValue({k: 'max_price', v}) }
        },

        min_den: {
            get () { return this.getFilterValue('min_den') },
            set (v) { this.updateFilterValue({k: 'min_den', v}) }
        },
        max_den: {
            get () { return this.getFilterValue('max_den') },
            set (v) { this.updateFilterValue({k: 'max_den', v}) }
        },

        den: {
            get () { return [this.min_den, this.max_den] },
            set (v) { this.updateFromSlider({type: 'den', v}) }
        },
        price: {
            get () { return [this.min_price, this.max_price] },
            set (v) { this.updateFromSlider({type: 'price', v}) }
        },

    },
    methods: {
        ...mapActions('filter', [
            'updateFilterValue',
            'updateFromSlider',
            'updateManufacturerStatus',
        ]),
    },
}
</script>

<style lang="scss">

</style>