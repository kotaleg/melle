<template>
  <section
    :class="['sidebar-popup w-100 h-100', { 'sidebar-opened': sidebar_opened }]"
  >
    <div
      ref="sidebarPopupContent"
      :class="['content h-100 p-4', { nameClass }]"
    >
      <notifications
        :group="this.$codename + '_sidebar'"
        position="bottom right"
      />

      <loading :active.sync="is_sidebar_loading" :is-full-page="false" />

      <slot />
    </div>
  </section>
</template>

<script>
import { has } from 'lodash'
import { mapState, mapActions } from 'vuex'
import Loading from 'vue-loading-overlay'

export default {
  props: {
    nameClass: {
      type: String,
      required: false,
      default: '',
    },
  },
  components: {
    Loading,
  },
  computed: {
    ...mapState('header', ['sidebar_opened', 'is_sidebar_loading']),
  },
  methods: {
    ...mapActions('header', ['openSidebar']),

    onKeyUp(event) {
      if (event.which === 27) {
        this.openSidebar(false)
      }
    },

    handleInit(create = true) {
      if (create === true) {
        document.documentElement.classList.add('open-menu')
        document.body.style = 'overflow:hidden;'
      } else {
        document.documentElement.classList.remove('open-menu')
        document.body.style = ''
      }
    },
  },
  destroyed() {
    window.removeEventListener('keyup', this.onKeyUp)
    this.handleInit(false)
  },
  mounted() {
    window.addEventListener('keyup', this.onKeyUp)
    this.handleInit(true)
  },
}
</script>
