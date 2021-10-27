<template>
  <modal
    name="size-list-modal"
    height="auto"
    :classes="['v--modal', 'vue-dialog', this.params.class]"
    :width="width"
    :pivot-y="0.3"
    :adaptive="true"
    :clickToClose="clickToClose"
    :transition="transition"
    @before-open="beforeOpened"
    @before-close="beforeClosed"
    @opened="$emit('opened', $event)"
    @closed="$emit('closed', $event)"
  >
    <div class="dialog-content">
      <div class="close-button" @click="closeModal"></div>
      <img :src="imageUrl" alt="Таблица Размеров" class="img-fluid" />
    </div>
  </modal>
</template>

<script>
export default {
  name: 'SizelistModal',
  props: {
    width: {
      type: [Number, String],
      default: 400,
    },
    clickToClose: {
      type: Boolean,
      default: true,
    },
    transition: {
      type: String,
      default: 'fade',
    },
  },
  data() {
    return {
      params: {},
    }
  },
  computed: {
    imageUrl() {
      return this.params.url || ''
    },
  },
  methods: {
    beforeOpened(event) {
      this.params = event.params || {}
      this.$emit('before-opened', event)
    },

    beforeClosed(event) {
      this.params = {}
      this.$emit('before-closed', event)
    },

    closeModal() {
      this.$modal.hide('size-list-modal')
    },
  },
}
</script>
