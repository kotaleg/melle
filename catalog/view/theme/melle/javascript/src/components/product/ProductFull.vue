<template>
  <main
    id="product-page"
    class="main-page container-fluid"
    itemtype="http://schema.org/Product"
    itemscope
  >
    <div class="row">
      <div class="col-lg-12">
        <section class="d-flex justify-content-between align-items-center">
          <div class="breadcrumbs">
            <div class="breadcrumbs">
              <span
                v-for="(breadcrumb, index) in breadcrumbs"
                :key="breadcrumb.href"
              >
                <a
                  v-if="index != breadcrumbs.length - 1"
                  :href="breadcrumb.href"
                  >{{ breadcrumb.text }}</a
                >
                <span v-else>{{ breadcrumb.text }}</span>
                <span v-if="index != breadcrumbs.length - 1" class="divider">
                  /
                </span>
              </span>
            </div>
          </div>
        </section>
      </div>

      <div class="col-md-12">
        <div class="row">
          <div class="col-lg-5">
            <ProductImages />
          </div>

          <div class="col-lg-7">
            <div v-if="znachek" class="prod-card__category">
              <span>{{ znachek }}</span>
            </div>

            <h1 class="title mb-4" v-focus>{{ name }}</h1>
            <meta itemprop="name" :content="name" />

            <melle-product />

            <div class="description mt-4 mb-2">{{ description }}</div>

            <div
              v-if="manufacturer"
              class="manufacturer mb-2"
              itemprop="brand"
              itemtype="http://schema.org/Brand"
              itemscope
            >
              <meta itemprop="name" :content="manufacturer" />
              <span>производитель:&nbsp;</span>
              <a :href="manufacturers">
                {{ manufacturer }}
              </a>
            </div>

            <div v-if="den" class="manufacturer mb-2">
              <span>количество ден:&nbsp;</span>{{ den }}
            </div>

            <div v-if="sostav" class="manufacturer mb-2">
              <span>состав:&nbsp;</span>{{ sostav }}
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 my-4">
        <div class="row">
          <div v-if="extra_description" class="col-md-8 description-extra">
            <span v-html="extra_description"></span>
            <details v-if="extra_description_hidden" details>
              <summary>Подробнее</summary>
              <span v-html="extra_description_hidden"></span>
            </details>
          </div>
          <!-- PRODUCT REWIEW START -->
          <div class="col-md-4">
            <melle-product-review></melle-product-review>
          </div>
          <!-- PRODUCT REWIEW END -->
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { get } from 'vuex-pathify'
import ProductImages from '@/components/product/ProductImages'

export default {
  components: {
    ProductImages,
  },
  computed: {
    ...get('product', [
      'productId',
      'breadcrumbs',
      'images',
      'znachek',
      'name',
      'description',
      'manufacturer',
      'manufacturers',
      'den',
      'sostav',
      'extra_description',
      'extra_description_hidden',
    ]),
  },
  created() {
    if (this.$route.query.product_id) {
      let data = { productId: this.$route.query.product_id }
      if (this.$route.query.categoryPath) {
        data.categoryPath = this.$route.query.categoryPath
      }
      this.$store.dispatch('product/FETCH_DATA', data)
    } else {
      this.$store.dispatch('product/FETCH_DATA', {
        productId: this.$route.params.productId,
      })
    }
  },
}
</script>
