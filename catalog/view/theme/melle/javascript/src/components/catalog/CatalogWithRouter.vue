<template>
  <router-view></router-view>
</template>

<script>
export default {
  components: {},
  computed: {},
  methods: {},
  created() {
    this.$store.dispatch('catalog/initData')
    this.$store.dispatch('filter/initData')
  },
  mounted() {
    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-catalog-content')
    // console.log(prerender)
    if (prerender) {
      prerender.remove()
    }
  },
}
</script>
