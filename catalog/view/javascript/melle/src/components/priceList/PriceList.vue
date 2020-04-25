<template>
  <div class="price-list-container">
    <p v-for="pl in priceLists">
      <a @click="downloadHandler(pl.downloadLink)" class="price-list-link">{{
        pl.title
      }}</a>
    </p>

    <price-list-modal dir="ltr" :width="500" :scrollable="false" />
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import PriceListModal from './../modal/PriceListModal.vue'

export default {
  components: {
    'price-list-modal': PriceListModal,
  },
  computed: {
    ...mapState('header', ['is_logged']),
    ...mapState('priceList', ['priceLists']),
  },
  methods: {
    ...mapActions('header', ['enableElement']),

    downloadHandler(downloadLink) {
      if (this.is_logged) {
        window.location.href = downloadLink
      } else {
        this.$modal.show('price-list-modal', {})
      }
    },
  },
  created() {
    this.$store.dispatch('priceList/initData')
  },
}
</script>

<style lang="scss"></style>
