<template>
  <carousel
    :perPageCustom="[
      [0, 1.4],
      [576, 1.5],
      [768, 2.5],
      [992, 3.5],
      [1200, 4.5],
      [1700, 6],
    ]"
    :navigationEnabled="false"
    :paginationEnabled="false"
    :scrollPerPage="false"
    :mouseDrag="true"
    class="other-products"
  >
    <slide v-for="(p, i) in items" :key="i">
      <div class="text-center product-item slider-item mr-3 mr-sm-0">
        <a :href="p.url" class="link">
          <img :src="p.picture" :alt="p.name" />
        </a>
        <div class="row">
          <div class="col-sm-12">
            <div class="mt-3 mb-3 d-inline-block title-container">
              <a :href="p.url" class="title">{{ p.name }}</a>
            </div>
          </div>
          <div class="d-flex align-items-center justify-content-around col-sm-12">
            <span class="price">{{ p.price }}<span class="ruble-sign">Р</span></span>
            <a :href="p.url" class="btn btn-primary">Купить</a>
          </div>
        </div>
      </div>
    </slide>
  </carousel>
</template>

<script>
import { Carousel, Slide } from 'vue-carousel'

export default {
  props: {
    items: {
      type: Array,
      default: [],
    },
  },
  components: {
    Carousel,
    Slide,
  },
  computed: {},
  methods: {
    getStars(rating) {
      return [true, true, true, true, true]
    },
  },
}
</script>
