<template>
  <div></div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { Carousel, Slide } from 'vue-carousel'

export default {
  props: {
    moduleId: {
      type: [Number, String],
    },
  },
  components: {
    Carousel,
    Slide,
  },
  computed: {
    ...mapGetters('slideshow', ['getSlidesForModule']),

    getCarouselId() {
      return `melle-slideshow-${this.moduleId}`
    },
  },
  methods: {
    handleSlideClick(dataset) {
      console.log(`REDIRECTING TO ${dataset.href}`)
      window.location.href = dataset.href
    },
  },
  mounted() {
    this.$store.dispatch('slideshow/initData', this.moduleId)
  },
}
</script>
