<template>
    <ais-instant-search
      v-if="searchClient && searchIndex"
      :index-name="searchIndex"
      :search-client="searchClient"
      :routing="routing"
      :class-names="{
        'ais-InstantSearch': 'search__container container',
      }"
    >

      <div class="search__sidebar">
        <section :class="{'sidebar sticky-sidebar': isMobile}">
          <div class="search-modal__filter filter">
            <div class="filter__title">
              <ais-stats>
                <p slot-scope="{ nbHits }">
                  Фильтр <span style="font-size: 10px;">(Найдено: {{ nbHits }})</span>
                </p>
              </ais-stats>
            </div>
            <form v-on:submit.prevent="() => {}">
              
              <div class="filter__price">
                <div class="filter__price-title"><span>Цена, руб</span></div>

                <ais-range-input attribute="price">
                  <form
                    slot-scope="{
                      currentRefinement,
                      range,
                      canRefine,
                      refine,
                    }"
                  >
                  <div class="super-flex">
                  <div class="filter__price-start">
                    <span>от</span>
                    <input
                      type="number"
                      :min="range.min"
                      :max="range.max"
                      :placeholder="range.min"
                      :disabled="!canRefine"
                      :value="formatMinValue(currentRefinement.min, range.min)"
                      @input="refine({
                        min:$event.currentTarget.value,
                        max: formatMaxValue(currentRefinement.max, range.max),
                      })"
                    >
                  </div>

                  <div class="filter__price-end">
                    <span>до</span>
                    <input
                      type="number"
                      :min="range.min"
                      :max="range.max"
                      :placeholder="range.max"
                      :disabled="!canRefine"
                      :value="formatMaxValue(currentRefinement.max,range.max)"
                      @input="refine({
                        min:formatMinValue(currentRefinement.min, range.min),
                        max: $event.currentTarget.value,
                      })"
                    >
                  </div>
                  </div>
                  </form>
                </ais-range-input>
              </div>
              
              <ais-refinement-list 
                attribute="manufacturer"
                :class-names="{
                  'ais-RefinementList-list': 'filter__list',
                  'ais-RefinementList-item': 'filter__item',
                }"
              >
                <li
                  slot="item"
                  slot-scope="{ item, refine }"
                  :style="{ fontWeight: item.isRefined ? 'bold' : '' }"
                  @click.prevent="refine(item.value)"
                >
                  <label>
                    <input type="checkbox" :checked="item.isRefined"> 
                    <span style="flex-shrink: 0;"></span> 
                    <span><label><ais-highlight attribute="item" :hit="item"/></label></span>
                    </label>
                </li>
              </ais-refinement-list>

              <div class="filter__price">
                <div class="filter__price-title"><span>Ден:</span></div>
              <ais-range-input attribute="den">
                  <form
                    slot-scope="{
                      currentRefinement,
                      range,
                      canRefine,
                      refine,
                    }"
                  >
                  <div class="super-flex">
                  <div class="filter__price-start">
                    <span>от</span>
                    <input
                      type="number"
                      :min="range.min"
                      :max="range.max"
                      :placeholder="range.min"
                      :disabled="!canRefine"
                      :value="formatMinValue(currentRefinement.min, range.min)"
                      @input="refine({
                        min:$event.currentTarget.value,
                        max: formatMaxValue(currentRefinement.max, range.max),
                      })"
                    >
                  </div>

                  <div class="filter__price-end">
                    <span>до</span>
                    <input
                      type="number"
                      :min="range.min"
                      :max="range.max"
                      :placeholder="range.max"
                      :disabled="!canRefine"
                      :value="formatMaxValue(currentRefinement.max,range.max)"
                      @input="refine({
                        min:formatMinValue(currentRefinement.min, range.min),
                        max: $event.currentTarget.value,
                      })"
                    >
                  </div>
                  </div>
                  </form>
                </ais-range-input>
              </div>

              <div class="filter__buttons">
                <ais-clear-refinements>
                  <div slot-scope="{ canRefine, refine, createURL }">
                    <a
                      :href="createURL()"
                      @click.prevent="refine"
                      v-if="canRefine"
                    >
                      Сбросить
                    </a>
                  </div>
                </ais-clear-refinements>
              </div>

            </form>
          </div>
        </section>
      </div>

      <div class="search__content">
        <div class="search-form">
          <div class="search-box-wrapper">
            <ais-search-box
              placeholder="Введите название товара"
              submit-title="Поиск"
              reset-title="Очистить"
              :autofocus="true"
              :show-loading-indicator="true"
            />
          </div>

          <div class="search-form__footer">
            <div class="search-form__info-result">
              <ais-stats>
                <span slot-scope="{ nbHits }">
                  <span>Найдено:
                    <span class="search-form__info-result-number">{{ nbHits }} </span>
                    <span>результатов </span>
                  </span>
                </span>
              </ais-stats>
            </div>
          </div>
        </div>

        <!-- <ais-sort-by
          :items="[
            { value: 'instant_search', label: 'Наименованию' },
            { value: 'instant_search_price_asc', label: 'Цене (сначала дешевые)' },
            { value: 'instant_search_price_desc', label: 'Цене (сначала дорогие)' },
          ]"
        >
          <ul slot-scope="{ items, currentRefinement, refine }">
            <li v-for="item in items" :key="item.value" :value="item.value">
              <a
                href="#"
                :style="{ fontWeight: item.value === currentRefinement ? 'bold' : '' }"
                @click.prevent="refine(item.value)"
              >
                {{ item.label }}
              </a>
            </li>
          </ul>
        </ais-sort-by> -->


        <div class="catalog_list_view search-hits-wrapper">
          <ais-hits :escapeHTML="false">
            <ul slot-scope="{ items }" class="catalog__list">
              <li v-for="item in items" :key="item.objectID" class="catalog__item">
                <a :href="formatProductHref(item.productId)" class="catalog__item-link">
                  <img :src="item.image" :alt="item.h1">
                </a>
                <div class="catalog__item-ivaninfo">
                    <div class="row">
                      <div class="col-xs-12">
                          <h3 class="ivanitemtitle"><a :href="formatProductHref(item.productId)">{{item.h1}}</a></h3>
                      </div>
                      <div class="col-xs-7">
                        <span class="catalog__item-price-default">
                        399 <span class="ruble-sign">Р</span></span>
                      </div>
                      <div class="col-xs-5">
                          <div><a :href="formatProductHref(item.productId)" class="ivanbuybutton">Купить</a></div>
                      </div>
                    </div>
                </div>
              </li>
            </ul>
          </ais-hits>

          <ais-pagination
            :padding="5"
          />
        </div>
      </div>

    </ais-instant-search>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import algoliasearch from 'algoliasearch/lite'
import { history } from 'instantsearch.js/es/lib/routers'
import { simple } from 'instantsearch.js/es/lib/stateMappings'
import { 
  AisInstantSearch, 
  AisSearchBox, 
  AisAutocomplete, 
  AisHits, 
  AisRefinementList, 
  AisHighlight, 
  AisClearRefinements, 
  AisStats,
  AisRangeInput,
  AisPagination,
} from 'vue-instantsearch'


export default {
  components: {
    AisInstantSearch,
    AisSearchBox,
    AisAutocomplete,
    AisHits,
    AisRefinementList,
    AisHighlight,
    AisClearRefinements,
    AisStats,
    AisRangeInput,
    AisPagination,
  },
  computed: {
    ...mapState('search', [
      'searchQuery',
      'productLinkPlaceholder',
    ]),
    ...mapState('header', [
      'pro_algolia',
    ]),

    searchIndex() {
      if (!this.pro_algolia) {
        return
      }
      return this.pro_algolia.indexName
    },
    searchClient() {
      if (!this.pro_algolia) {
        return
      }

      return algoliasearch(
        this.pro_algolia.appId,
        this.pro_algolia.searchApiKey
      )
    },
    isMobile: function isMobile() {
      return this.windowWidth > 768;
    }
  },
  methods: {
    formatMinValue(minValue, minRange) {
      return minValue !== null && minValue !== minRange ? minValue : '';
    },
    formatMaxValue(maxValue, maxRange) {
      return maxValue !== null && maxValue !== maxRange ? maxValue : '';
    },
    formatProductHref(productId) {
      return `${this.productLinkPlaceholder}${productId}`
    },
  },
  data() {
    return {
      routing: {
        router: history(),
        stateMapping: simple(),
      },
    };
  },
  created() {
    this.$store.dispatch('search/initData')
  },
}
</script>

<style lang="scss">

</style>