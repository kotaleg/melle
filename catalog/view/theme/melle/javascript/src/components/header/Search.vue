<template>
  <ais-instant-search
    v-if="searchClient && searchIndex"
    :index-name="searchIndex"
    :search-client="searchClient"
  >
    <div class="melle-header-autocomplete">
      <ais-autocomplete>
        <div slot-scope="{ currentRefinement, indices, refine }">
          <div class="search-bar-wrapper">
            <form
              class="search-bar-form"
              v-on:submit.prevent="searchAction(currentRefinement)"
            >
              <div class="search-input-wrapper">
                <input
                  type="text"
                  name="search"
                  autocapitalize="off"
                  autocomplete="off"
                  autocorrect="off"
                  spellcheck="false"
                  maxlength="255"
                  placeholder="Поиск"
                  :value="currentRefinement"
                  @input="refine($event.currentTarget.value)"
                />
              </div>
            </form>

            <div v-if="currentRefinement">
              <div class="search-autocomplete-items-wrapper">
                <div class="search-prefix"></div>
                <section class="search-suggestions">
                  <div
                    v-for="index in indices"
                    :key="index.label"
                    class="melle-autocomplete-items"
                  >
                    <a
                      v-for="hit in index.hits"
                      :key="hit.objectID"
                      :href="`${product_link_placeholder}${hit.productId}`"
                      class="suggestions-item"
                    >
                      <img :src="hit.image" :alt="hit.name" class="mr-2 ml-2" />

                      <ais-highlight attribute="h1" :hit="hit" />

                      <div class="price-wrapper ml-2">
                        <span v-if="hit.special > 0">{{ hit.special }}</span>
                        <span v-else>{{ hit.price }}</span>
                        <span class="ruble-sign">Р</span>
                      </div>
                    </a>
                  </div>
                  <ais-powered-by />
                </section>
              </div>
            </div>
          </div>
        </div>
      </ais-autocomplete>
    </div>
  </ais-instant-search>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import algoliasearch from 'algoliasearch/lite'
import {
  AisInstantSearch,
  AisAutocomplete,
  AisHighlight,
  AisPoweredBy,
} from 'vue-instantsearch'

export default {
  components: {
    AisInstantSearch,
    AisAutocomplete,
    AisHighlight,
    AisPoweredBy,
  },
  computed: {
    ...mapState('header', ['product_link_placeholder', 'pro_algolia', 'base']),
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
  },
  methods: {
    searchAction(searchQuery) {
      const url = `${this.base}index.php?route=product/search&${this.searchIndex}[query]=${encodeURIComponent(searchQuery)}`
      location = url
    },
  },
}
</script>
