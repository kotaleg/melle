<template>
  <ais-instant-search
    v-if="searchClient && searchIndex"
    :index-name="searchIndex"
    :search-client="searchClient"
    :routing="routing"
    :class-names="{
      'ais-InstantSearch': 'row catalog mt-4',
    }"
  >
    <div class="d-none d-lg-block col-lg-4 col-xl-3 col-xxl-2">
      <section :class="{ 'sidebar sticky-sidebar': isMobile }">
        <div class="filter">
          <h1 class="title">
            <ais-stats>
              <p slot-scope="{ nbHits }">
                Фильтр
                <span style="font-size: 10px;"
                  >(Найдено:&nbsp;{{ nbHits }})</span
                >
              </p>
            </ais-stats>
          </h1>
          <form v-on:submit.prevent="() => {}" class="mt-3">
            <div class="filter-price mb-4">
              <div class="sub-title mb-2"><span>Цена, руб</span></div>

              <ais-range-input attribute="price">
                <form
                  slot-scope="{ currentRefinement, range, canRefine, refine }"
                >
                  <div class="d-flex">
                    <div class="d-flex align-items-center w-50">
                      <span>от</span>
                      <input
                        type="number"
                        class="text-center"
                        :min="range.min"
                        :max="range.max"
                        :placeholder="range.min"
                        :disabled="!canRefine"
                        :value="
                          formatMinValue(currentRefinement.min, range.min)
                        "
                        @input="
                          refine({
                            min: $event.currentTarget.value,
                            max: formatMaxValue(
                              currentRefinement.max,
                              range.max
                            ),
                          })
                        "
                      />
                    </div>

                    <div
                      class="d-flex align-items-center justify-content-end w-50"
                    >
                      <span>до</span>
                      <input
                        type="number"
                        class="text-center"
                        :min="range.min"
                        :max="range.max"
                        :placeholder="range.max"
                        :disabled="!canRefine"
                        :value="
                          formatMaxValue(currentRefinement.max, range.max)
                        "
                        @input="
                          refine({
                            min: formatMinValue(
                              currentRefinement.min,
                              range.min
                            ),
                            max: $event.currentTarget.value,
                          })
                        "
                      />
                    </div>
                  </div>
                </form>
              </ais-range-input>
            </div>

            <ais-refinement-list
              attribute="manufacturer"
              :class-names="{
                'ais-RefinementList-list':
                  'filter-manufacturers d-flex flex-wrap align-content-center align-items-center mb-4',
                'ais-RefinementList-item': 'manufacturer-item mr-2',
              }"
            >
              <div
                slot="item"
                slot-scope="{ item, refine }"
                :style="{ fontWeight: item.isRefined ? 'bold' : '' }"
                @click.prevent="refine(item.value)"
              >
                <label class="d-flex align-items-center">
                  <input type="checkbox" :checked="item.isRefined" />
                  <span
                    class="d-flex justify-content-between align-items-center"
                  ></span>
                  <span
                    ><label
                      ><ais-highlight attribute="item" :hit="item" /></label
                  ></span>
                </label>
              </div>
            </ais-refinement-list>

            <div class="filter-price mb-4">
              <div class="sub-title mb-2"><span>Ден:</span></div>
              <ais-range-input attribute="den">
                <form
                  slot-scope="{ currentRefinement, range, canRefine, refine }"
                >
                  <div class="d-flex">
                    <div class="d-flex align-items-center w-50">
                      <span>от</span>
                      <input
                        type="number"
                        class="text-center"
                        :min="range.min"
                        :max="range.max"
                        :placeholder="range.min"
                        :disabled="!canRefine"
                        :value="
                          formatMinValue(currentRefinement.min, range.min)
                        "
                        @input="
                          refine({
                            min: $event.currentTarget.value,
                            max: formatMaxValue(
                              currentRefinement.max,
                              range.max
                            ),
                          })
                        "
                      />
                    </div>

                    <div
                      class="d-flex align-items-center justify-content-end w-50"
                    >
                      <span>до</span>
                      <input
                        type="number"
                        class="text-center"
                        :min="range.min"
                        :max="range.max"
                        :placeholder="range.max"
                        :disabled="!canRefine"
                        :value="
                          formatMaxValue(currentRefinement.max, range.max)
                        "
                        @input="
                          refine({
                            min: formatMinValue(
                              currentRefinement.min,
                              range.min
                            ),
                            max: $event.currentTarget.value,
                          })
                        "
                      />
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
                    class="btn btn-dark"
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

    <div class="col-lg-8 col-xl-9 col-xxl-10">
      <div class="search-form">
        <div class="search-box-wrapper mb-2">
          <ais-search-box
            placeholder="Введите название товара"
            submit-title="Поиск"
            reset-title="Очистить"
            :autofocus="true"
            :show-loading-indicator="true"
          />
        </div>

        <div class="align-items-center d-flex justify-content-between">
          <ais-stats>
            <span slot-scope="{ nbHits }">
              <span
                >Найдено:
                <span class="result-number">{{ nbHits }} </span>
                <span>результатов </span>
              </span>
            </span>
          </ais-stats>
          <ais-powered-by />
        </div>
      </div>

      <div>
        <ais-hits :escapeHTML="false" :transform-items="transformItems">
          <div slot-scope="{ items }" class="row mt-4 mb-2 search-hits-wrapper">
            <div
              v-for="item in items"
              :key="item.objectID"
              class="col-md-6 col-xl-4 product-item mb-5 text-center"
            >
              <a :href="item.href">
                <img :src="item.image" :alt="item.h1" class="img-fluid" />
              </a>

              <div v-if="item.specialText" class="p-2 special-text">
                <span>{{ item.specialText }}</span>
              </div>

              <div class="row">
                <div class="col-xs-12">
                  <div class="my-4">
                    <a :href="item.href" class="title">{{ item.h1 }}</a>
                  </div>
                </div>
                <div
                  class="d-flex align-items-center justify-content-around col-sm-12"
                >
                  <div v-if="item.isSpecial" class="position-relative">
                    <span class="price price-old mr-2">
                      {{ item.price }}
                      <span class="ruble-sign">Р</span>
                    </span>
                    <span class="price">
                      {{ item.special }}
                      <span v-if="isZvezdochka" class="ruble-container"
                        ><span class="ruble-sign">Р</span
                        ><span class="ruble-zvezdochka">*</span></span
                      >
                      <span v-else class="ruble-sign">Р</span>
                    </span>
                  </div>
                  <span v-else class="price">
                    {{ item.price }}
                    <span class="ruble-sign">Р</span>
                  </span>
                  <a :href="item.href" class="btn btn-primary px-4">Купить</a>
                </div>
              </div>
            </div>
          </div>
        </ais-hits>

        <div class="text-center">
          <ais-pagination :padding="5" />
        </div>
      </div>
    </div>
  </ais-instant-search>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { isNumber, isString } from 'lodash'
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
  AisPoweredBy,
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
    AisPoweredBy,
  },
  computed: {
    ...mapState('search', ['searchQuery', 'productLinkPlaceholder']),
    ...mapState('header', ['pro_algolia']),

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
      return this.windowWidth > 768
    },
  },
  methods: {
    formatMinValue(minValue, minRange) {
      return minValue !== null && minValue !== minRange ? minValue : ''
    },
    formatMaxValue(maxValue, maxRange) {
      return maxValue !== null && maxValue !== maxRange ? maxValue : ''
    },
    formatProductHref(productId) {
      return `${this.productLinkPlaceholder}${productId}`
    },
    isSpecial(specialValue) {
      if (isNumber(specialValue) && specialValue > 0) {
        return true
      }
      return false
    },
    isZvezdochka(specialText) {
      if (isString(specialText) && specialText.includes('*')) {
        return true
      }
      return false
    },
    transformItems(items) {
      return items.map((item) => ({
        ...item,
        href: this.formatProductHref(item.productId),
        isSpecial: this.isSpecial(item.special),
        isZvezdochka: this.isZvezdochka(item.specialText),
      }))
    },
  },
  data() {
    return {
      routing: {
        router: history({
          createURL({ qsModule, location, routeState }) {
            const { origin, pathname, hash } = location
            const indexState = routeState || {}

            // TODO: implement updating value on page change
            routeState.route = 'product/search'

            const queryString = qsModule.stringify(routeState)

            return `${origin}${pathname}?${queryString}${hash}`
          },
        }),
        stateMapping: simple(),
      },
    }
  },
  created() {
    this.$store.dispatch('search/initData')
  },
}
</script>

<style lang="scss"></style>
