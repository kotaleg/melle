<template>
  <div v-if="images.length > 0" class="d-flex align-items-center product-images-container position-relative">
    <div class="product-images-nav">
      <VueSlickCarousel
        ref="nav"
        @beforeChange="onBeforeChange"
        :slidesToShow="3"
        :vertical="true"
        :infinite="true"
        :focusOnSelect="true">
        <div v-for="image in images" :key="image.imageHash">
          <img :src="image.thumb" class="img-fluid">
        </div>
      </VueSlickCarousel>
    </div>
    <div class="product-images p-2">
      <VueSlickCarousel
        ref="images"
        @beforeChange="onBeforeChange"
        :infinite="true"
        :vertical="false"
        :slidesToShow="1"
        :slidesToScroll="1"
        :focusOnSelect="true">
        <div v-for="image in images" :key="image.imageHash">
          <zoom-on-hover :img-normal="image.image" :img-zoom="image.zoom" />
        </div>
      </VueSlickCarousel>
    </div>
  </div>
</template>

<script>
import { get } from 'vuex-pathify'
import VueSlickCarousel from 'vue-slick-carousel'

export default {
  components: {
    VueSlickCarousel,
  },
  computed: {
    ...get('product', [
      'productId',
      'images',
    ]),
    imageHash: get('product/stock@imageHash'),
  },
  methods: {
    onBeforeChange(currentPosition, nextPosition) {
      this.$refs.nav.goTo(nextPosition)
      this.$refs.images.goTo(nextPosition)
    }
  },
  watch: {
    imageHash: function (imageHash) {
      for (const key in this.images) {
        if (imageHash == this.images[key].imageHash) {
          this.$refs.images.goTo(key)
        }
      }
    }
  }
}
</script>
