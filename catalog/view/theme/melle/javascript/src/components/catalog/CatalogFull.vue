<template>
  <main class="main-page container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <section class="row">
          <div class="col-md-4 col-lg-6">
            <div class="breadcrumbs">
              <span v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.href">
                <a :href="breadcrumb.href">{{ breadcrumb.text }}</a>
                <span v-if="index != breadcrumbs.length-1" class="divider">/</span>
              </span>
            </div>
          </div>
          <div class="col-md-8 col-lg-6">
            <melle-catalog-sort />
          </div>
        </section>
      </div>

      <div class="col-lg-12">
        <section class="row catalog">
          <div class="d-none d-lg-block col-lg-4 col-xl-3 col-xxl-2">
            <melle-catalog-filter />
          </div>
          <div class="col-lg-8 col-xl-9 col-xxl-10">
            <catalog />
            <details v-if="description" details>
              <summary>Показать полностью</summary>
              <br />
              <article style="column-count: 2;">{{ description }}</article>
            </details>
          </div>
        </section>
      </div>
    </div>
  </main>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Catalog from './Catalog'

export default {
  components: {
    Catalog,
  },
  computed: {
    ...mapState('catalog', ['breadcrumbs', 'description']),
  },
  methods: {
  },
  mounted() {
  },
}
</script>s
