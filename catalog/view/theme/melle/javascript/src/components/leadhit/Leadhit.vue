<template>
  <div>
    <carousel-block :items="items" />
  </div>
</template>

<script>
import { mapState } from 'vuex'
import { v4 as uuidv4 } from 'uuid'
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
      return uuidv4()
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
