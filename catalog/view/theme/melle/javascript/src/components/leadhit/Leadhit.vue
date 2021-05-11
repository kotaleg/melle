<template>
  <div>
    <carousel-block :items="items" />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import CarouselBlock from './CarouselBlock.vue'

export default {
  props: {
    id: {
      type: [String],
      required: true,
    },
    sourceType: {
      type: [String],
      required: true,
    },
  },
  components: {
    CarouselBlock,
  },
  computed: {
    ...mapState('leadhit', ['productsContainer']),
    items() {
      if (typeof this.productsContainer[this.id] != 'undefined') {
        return this.productsContainer[this.id]
      }
      return []
    },
  },
  created() {
    this.$store.dispatch('leadhit/initData')
  },
  mounted() {
    this.$store.dispatch('leadhit/getProductsSliceFor', {sourceType: this.sourceType, id: this.id})
  },
}
</script>

<style lang="scss"></style>
