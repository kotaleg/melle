<template>
  <section :class="{ 'sidebar sticky-sidebar': isMobile }">
    <div class="filter">
      <h4 class="title">
        Фильтр
        <span class="found">(Найдено:&nbsp;{{ product_total }})</span>
      </h4>

      <form v-on:submit.prevent="openSidebar(false)" class="mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <label class="d-flex align-items-center">
            <input
              name="CatalogFilterForm[hits]"
              type="checkbox"
              v-model="hit"
            />
            <span
              class="d-flex justify-content-between align-items-center"
            ></span
            ><span>Хиты</span>
          </label>
          <label class="d-flex align-items-center">
            <input
              name="CatalogFilterForm[news]"
              type="checkbox"
              v-model="neww"
            />
            <span
              class="d-flex justify-content-between align-items-center"
            ></span
            ><span>Новинки</span>
          </label>
          <label class="d-flex align-items-center">
            <input
              name="CatalogFilterForm[actions]"
              type="checkbox"
              v-model="act"
            />
            <span
              class="d-flex justify-content-between align-items-center"
            ></span
            ><span>Акции</span>
          </label>
        </div>

        <div class="filter-price mb-4">
          <div class="sub-title mb-2"><span>Цена, руб</span></div>
          <div class="d-flex">
            <div class="d-flex align-items-center w-50">
              <span>от</span>
              <input
                name="CatalogFilterForm[price_from]"
                type="text"
                class="text-center"
                v-model.trim="min_price"
              />
            </div>
            <div class="d-flex align-items-center justify-content-end w-50">
              <span>до</span>
              <input
                name="CatalogFilterForm[price_to]"
                type="text"
                class="text-center"
                v-model.trim="max_price"
              />
            </div>
          </div>
        </div>

        <div
          class="
            filter-manufacturers
            d-flex
            flex-wrap
            align-content-center align-items-center
            mb-4
          "
        >
          <div
            v-for="(item, i) in getFilterValue('manufacturers')"
            :key="`filter-manufacturer-${i}`"
            class="manufacturer-item mr-2"
          >
            <label class="d-flex align-items-center">
              <input
                @click="updateManufacturerStatus(i)"
                :checked="item.checked"
                type="checkbox"
                name="CatalogFilterForm[producers][]"
              />
              <span
                class="d-flex justify-content-between align-items-center"
              ></span>
              <span>
                <label>{{ item.label }}</label>
              </span>
            </label>
          </div>
        </div>

        <div
          class="
            d-flex
            align-items-center
            justify-content-between
            mb-4
            text-right
          "
        >
          <span class="sub-title">размер:</span>
          <v-select
            class="d-inline-block"
            v-model="size"
            :options="getFilterValue('all_sizes')"
            placeholder="Все"
            :searchable="false"
            :closeOnSelect="true"
            maxHeight="200px"
          >
            <span slot="no-options"></span>
          </v-select>
        </div>

        <div
          class="
            d-flex
            align-items-center
            justify-content-between
            mb-4
            text-right
          "
        >
          <span class="sub-title">цвет:</span>
          <v-select
            class="d-inline-block"
            v-model="color"
            :options="getFilterValue('all_colors')"
            placeholder="Все"
            :searchable="false"
            :closeOnSelect="true"
            maxHeight="200px"
          >
            <span slot="no-options"></span>
          </v-select>
        </div>

        <div
          class="
            d-flex
            align-items-center
            justify-content-between
            mb-4
            text-right
          "
        >
          <span class="sub-title">материал:</span>
          <v-select
            class="d-inline-block"
            v-model="material"
            :options="getFilterValue('all_materials')"
            placeholder="Все"
            :searchable="false"
            :closeOnSelect="true"
            maxHeight="200px"
          >
            <span slot="no-options"></span>
          </v-select>
        </div>

        <div class="filter-price mb-4">
          <div class="sub-title mb-2"><span>Ден:</span></div>
          <div class="d-flex">
            <div class="d-flex align-items-center w-50">
              <span>от</span>
              <input
                name="CatalogFilterForm[den_from]"
                type="text"
                class="text-center"
                v-model.trim="min_den"
              />
            </div>
            <div class="d-flex align-items-center justify-content-end w-50">
              <span>до</span>
              <input
                name="CatalogFilterForm[den_to]"
                type="text"
                class="text-center"
                v-model.trim="max_den"
              />
            </div>
          </div>
        </div>

        <div class="filter-buttons">
          <button
            type="submit"
            class="btn btn-dark btn-block d-block d-sm-none"
          >
            Применить
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Stickyfill from 'stickyfilljs'

export default {
  components: {},
  computed: {
    ...mapGetters('filter', [
      'isFilterChanged',
      'getFilterValue',
      'getSliderOptions',
    ]),
    ...mapState('catalog', ['product_total', 'filter_data']),

    hit: {
      get() {
        return this.getFilterValue('hit')
      },
      set(v) {
        this.updateFilterValue({ k: 'hit', v })
      },
    },
    neww: {
      get() {
        return this.getFilterValue('neww')
      },
      set(v) {
        this.updateFilterValue({ k: 'neww', v })
      },
    },
    act: {
      get() {
        return this.getFilterValue('act')
      },
      set(v) {
        this.updateFilterValue({ k: 'act', v })
      },
    },
    min_price: {
      get() {
        return this.getFilterValue('min_price')
      },
      set(v) {
        this.updateFilterValue({ k: 'min_price', v })
      },
    },
    max_price: {
      get() {
        return this.getFilterValue('max_price')
      },
      set(v) {
        this.updateFilterValue({ k: 'max_price', v })
      },
    },
    min_den: {
      get() {
        return this.getFilterValue('min_den')
      },
      set(v) {
        this.updateFilterValue({ k: 'min_den', v })
      },
    },
    max_den: {
      get() {
        return this.getFilterValue('max_den')
      },
      set(v) {
        this.updateFilterValue({ k: 'max_den', v })
      },
    },
    size: {
      get() {
        return this.getFilterValue('size')
      },
      set(v) {
        this.updateFilterValue({ k: 'size', v })
      },
    },
    color: {
      get() {
        return this.getFilterValue('color')
      },
      set(v) {
        this.updateFilterValue({ k: 'color', v })
      },
    },
    material: {
      get() {
        return this.getFilterValue('material')
      },
      set(v) {
        this.updateFilterValue({ k: 'material', v })
      },
    },

    den: {
      get() {
        return [this.min_den, this.max_den]
      },
      set(v) {
        this.updateFromSlider({ type: 'den', v })
      },
    },
    price: {
      get() {
        return [this.min_price, this.max_price]
      },
      set(v) {
        this.updateFromSlider({ type: 'price', v })
      },
    },

    isMobile() {
      return this.windowWidth > 768
    },
  },
  methods: {
    ...mapActions('filter', [
      'updateFilterValue',
      'updateFromSlider',
      'updateManufacturerStatus',
      'updateSelectValue',
      'clearSelection',
    ]),
    ...mapActions('header', ['openSidebar']),
  },
  data() {
    return {
      windowWidth: 0,
    }
  },
  mounted() {
    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-filter-content')
    if (prerender) {
      prerender.remove()
    }

    this.windowWidth = window.innerWidth
    this.$nextTick(() => {
      window.addEventListener('resize', () => {
        this.windowWidth = window.innerWidth
      })
    })

    const filter = document.querySelectorAll('.sticky-sidebar')
    if (this.isMobile) {
      Stickyfill.add(filter)
    } else {
      Stickyfill.remove(filter)
    }
  },
}
</script>
