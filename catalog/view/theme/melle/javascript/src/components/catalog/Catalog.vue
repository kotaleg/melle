<template>
  <div class="mt-4 mt-md-0">
    <h1 class="title">{{ current_category }}</h1>

    <div v-if="product_total > 0" class="row mt-4 mb-2">
      <div
        v-for="(p, i) in products"
        :key="`product-${p.product_id}`"
        :class="[
          'col-md-6 col-xl-4 product-item mb-5 text-center',
          p.znachek_class,
        ]"
      >
        <router-link :to="p.router_link" @click="gtmProductClick(i)">
          <img :src="p.image" loading="lazy" width="365" height="468" :alt="p.h1" class="img-fluid" />
        </router-link>

        <div v-if="p.special_text" class="p-2 special-text">
          <span>{{ p.special_text }}</span>
        </div>

        <div class="row">
          <div class="col-sm-12">
            <div @click="gtmProductClick(i)" class="my-4">
              <router-link :to="p.router_link" class="title">{{ p.h1 }}</router-link>
            </div>
          </div>
          <div
            v-if="p.in_stock"
            class="d-flex align-items-center justify-content-around col-sm-12"
          >
            <div v-if="isSpecial(i)">
              <span class="price price-old mr-2">
                {{ getPrice(i) }} <span class="ruble-sign">Р</span>
              </span>
              <span class="price">
                {{ getSpecial(i) }}
                <span v-if="p.zvezdochka" class="ruble-container"
                  ><span class="ruble-sign">Р</span
                  ><span class="ruble-zvezdochka">*</span></span
                >
                <span v-else class="ruble-sign">Р</span>
              </span>
            </div>
            <span v-else class="price">
              {{ getPrice(i) }} <span class="ruble-sign">Р</span></span
            >
            <a
              @click="openProductPreview(p.product_id)" href="javascript:void(0)"
              class="btn btn-primary px-4"
              >Купить</a
            >
          </div>

          <router-link v-else :to="p.router_link" class="btn btn-primary btn-block w-75 m-auto">Скоро в продаже</router-link>
        </div>
      </div>
    </div>

    <div v-show="canLoadMore" class="text-center">
      <button
        @click="loadMore()"
        id="view-more-button"
        class="btn btn-secondary bg-dark px-5 py-3"
      >
        <b>ПОКАЗАТЬ ЕЩЁ</b>
      </button>
    </div>

    <product-preview-modal dir="ltr" :width="750" :scrollable="false" />
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import ProductPreviewModal from '@/components/modal/ProductPreviewModal.vue'

export default {
  components: {
    ProductPreviewModal,
  },
  computed: {
    ...mapGetters('catalog', [
      'canLoadMore',
      'getRating',
      'getPrice',
      'getProductForGTM',
      'isSpecial',
      'getSpecial',
    ]),
    ...mapState('catalog', ['current_category', 'products', 'product_total']),
  },
  methods: {
    ...mapActions('catalog', ['loadMoreRequest']),
    ...mapActions('gtm', ['productClick']),

    loadMore() {
      this.loadMoreRequest()
    },

    gtmProductClick(i) {
      let product = this.getProductForGTM(i)
      this.productClick({ page_type: false, product })
    },

    openProductPreview(productId) {
      if (window.matchMedia('(min-width: 992px)').matches) {
        this.$modal.show('product-preview-modal', { productId })
      }
    },
  },
  mounted() {
    // GTM
    this.$store.dispatch('gtm/loadCatalog')
  },
}
</script>
