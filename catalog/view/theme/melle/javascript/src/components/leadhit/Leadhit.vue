<template>
  <div>
    <carousel-block :items="items" />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import { nanoid } from 'nanoid'
import CarouselBlock from './CarouselBlock.vue'

export default {
  props: {
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
    randomId() {
      return nanoid()
    },
    items() {
      if (typeof this.productsContainer[this.randomId] != 'undefined') {
        return this.productsContainer[this.randomId]
      }
      return []
    },
  },
  created() {
    this.$store.dispatch('leadhit/initData')
  },
  mounted() {
    this.$store.dispatch('leadhit/getProductsSliceFor', {
      serviceName: this.sourceType,
      id: this.randomId,
    })
  },
}
</script>

<style lang="scss"></style>
