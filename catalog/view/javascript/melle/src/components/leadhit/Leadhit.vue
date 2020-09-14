<template>
  <div class="">
    <carousel-block :items="items" />
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
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
    ...mapState('leadhit', ['hits', 'recommend']),
    items() {
      if (this.sourceType === 'hits') {
        return this.hits
      } else if (this.sourceType === 'recommend') {
        return this.recommend
      }
      return []
    },
  },
  methods: {},
  created() {
    this.$store.dispatch('leadhit/initData')
  },
  mounted() {
    if (this.sourceType === 'hits') {
      this.$store.dispatch('leadhit/getHits')
    } else if (this.sourceType === 'recommend') {
      this.$store.dispatch('leadhit/getRecommend')
    }
  },
}
</script>

<style lang="scss"></style>
