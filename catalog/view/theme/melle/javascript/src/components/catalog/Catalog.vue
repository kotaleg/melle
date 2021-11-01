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
        <router-link :to="p.router_link" @click.native="productClick(p)">
          <img
            :src="p.image"
            loading="lazy"
            width="365"
            height="468"
            :alt="p.name"
            class="img-fluid"
          />
        </router-link>

        <div v-if="p.special_text" class="p-2 special-text">
          <span>{{ p.special_text }}</span>
        </div>

        <div class="row">
          <div class="col-sm-12">
            <div class="my-4">
              <router-link
                :to="p.router_link"
                @click.native="productClick(p)"
                class="title"
                >{{ p.name }}</router-link
              >
            </div>
          </div>
          <div
            v-if="p.in_stock"
            class="d-flex align-items-center justify-content-around col-sm-12"
          >
            <div v-if="isSpecial(i)" class="position-relative">
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
              @click="openProductPreview(p)"
              href="javascript:void(0)"
              class="btn btn-primary px-4"
              >Купить</a
            >
          </div>

          <router-link
            v-else
            :to="p.router_link"
            @click.native="productClick(p)"
            class="btn btn-primary btn-block w-75 m-auto"
            >Скоро в продаже</router-link
          >
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
import gtag from '@/plugins/gtag'

export default {
  components: {
    ProductPreviewModal,
  },
  computed: {
    ...mapGetters('catalog', [
      'canLoadMore',
      'getRating',
      'getPrice',
      'isSpecial',
      'getSpecial',
    ]),
    ...mapState('catalog', [
      'current_category',
      'products',
      'product_total',
      'heading_title',
    ]),
  },
  methods: {
    ...mapActions('catalog', ['loadMoreRequest']),

    loadMore() {
      this.loadMoreRequest()
    },

    productClick(product) {
      gtag.productClick({
        product,
        list_name: this.heading_title,
        category_name: this.current_category,
      })
    },

    openProductPreview(product) {
      gtag.productClick({
        product,
        list_name: this.heading_title,
        category_name: this.current_category,
      })

      if (window.matchMedia('(min-width: 992px)').matches) {
        this.$modal.show('product-preview-modal', {
          productId: product.product_id,
        })
      } else {
        this.$router.push(product.router_link)
      }
    },
  },
  mounted() {
    gtag.productImpressions({
      products: this.products,
      list_name: this.heading_title,
      category_name: this.current_category,
    })
  },
}
</script>
