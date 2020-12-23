<template>
  <modal
    name="price-list-modal"
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
    <div class="dialog-content price-list-modal-container">
      <div class="dialog-c-text price-list-modal-body text-center">
        <p>Для скачивания прайс листа</p>
        <p>вам необходимо выполнить</p>
        <p>
          <a @click="lickHandler('login')" href="javascript:void(0)"
            >авторизацию</a
          >
          или
          <a @click="lickHandler('register')" href="javascript:void(0)"
            >регистрацию</a
          >
        </p>
      </div>
    </div>
  </modal>
</template>

<script>
import { mapActions } from 'vuex'

export default {
  name: 'PriceListModal',
  components: {},
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
      defaultButtons: [{ title: 'Закрыть' }],
    }
  },
  computed: {
    buttons() {
      return this.params.buttons || this.defaultButtons
    },

    buttonStyle() {
      return {
        flex: `1 1 ${100 / 1}%`,
      }
    },
  },
  methods: {
    ...mapActions('header', ['enableElement']),

    lickHandler(type) {
      this.enableElement(type)
      this.$modal.hide('price-list-modal')
    },

    beforeOpened(event) {
      window.addEventListener('keyup', this.onKeyUp)
      this.params = event.params || {}
      this.$emit('before-opened', event)
    },

    beforeClosed(event) {
      window.removeEventListener('keyup', this.onKeyUp)
      this.params = {}
      this.$emit('before-closed', event)
    },

    click(i, event, source = 'click') {
      const button = this.buttons[i]
      if (button && typeof button.handler === 'function') {
        button.handler(i, event)
      } else {
        this.$modal.hide('price-list-modal')
      }
    },

    onKeyUp(event) {
      if (event.which === 13 && this.buttons.length > 0) {
        const buttonIndex =
          this.buttons.length === 1
            ? 0
            : this.buttons.findIndex((button) => button.default)
        if (buttonIndex !== -1) {
          this.click(buttonIndex, event, 'keypress')
        }
      }
    },
  },
}
</script>

<style lang="scss"></style>
