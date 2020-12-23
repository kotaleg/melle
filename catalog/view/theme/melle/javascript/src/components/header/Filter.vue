<template>
  <section class="auth">
    <sidebar-buttons />

    <cat-filter />
  </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

import SidebarButtons from '../partial/SidebarButtons.vue'
import Filter from '../catalog/Filter.vue'

export default {
  components: {
    'sidebar-buttons': SidebarButtons,
    'cat-filter': Filter,
  },
}
</script>
