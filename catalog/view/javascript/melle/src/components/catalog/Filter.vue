<template>
      <div class="search-modal__filter filter">
         <div class="filter__title">Фильтр <span style="font-size: 10px;">(Найдено: {{ product_total }})</span></div>

         <form v-on:submit.prevent="openSidebar(false)">
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
                <li v-for="(item, i) in getFilterValue('manufacturers')"
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

            <div class="select-section">
                <div class="select-section-item text-right">
                    <span class="filter__select-name">размер:</span>
                    <v-select
                        style="display: inline-block;"
                        v-model="size"
                        :options="getFilterValue('all_sizes')"
                        placeholder="Все"
                        :searchable="false"
                        :closeOnSelect="true"
                        maxHeight="200px">
                        <span slot="no-options"></span>
                    </v-select>
                </div>

                <div class="select-section-item text-right">
                    <span class="filter__select-name">цвет:</span>
                    <v-select
                        style="display: inline-block;"
                        v-model="color"
                        :options="getFilterValue('all_colors')"
                        placeholder="Все"
                        :searchable="false"
                        :closeOnSelect="true"
                        maxHeight="200px">
                        <span slot="no-options"></span>
                    </v-select>
                </div>

                <div class="select-section-item text-right">
                    <span class="filter__select-name">материал:</span>
                    <v-select
                        style="display: inline-block;"
                        v-model="material"
                        :options="getFilterValue('all_materials')"
                        placeholder="Все"
                        :searchable="false"
                        :closeOnSelect="true"
                        maxHeight="200px">
                        <span slot="no-options"></span>
                    </v-select>
                </div>
            </div>

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

            <div class="filter__buttons">
                <input type="submit" value="Показать" class="show-shitty-results" />
            </div>

         </form>
      </div>
</template>

<script>
import { mapState, mapActions, mapGetters, clone } from 'vuex'
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
        ...mapState('catalog', [
            'product_total',
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
        size: {
            get () { return this.getFilterValue('size') },
            set (v) { this.updateFilterValue({k: 'size', v}) }
        },
        color: {
            get () { return this.getFilterValue('color') },
            set (v) { this.updateFilterValue({k: 'color', v}) }
        },
        material: {
            get () { return this.getFilterValue('material') },
            set (v) { this.updateFilterValue({k: 'material', v}) }
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
            'updateSelectValue',
        ]),
        ...mapActions('header', [
            'openSidebar',
        ]),
    },
}
</script>

<style lang="scss">

</style>