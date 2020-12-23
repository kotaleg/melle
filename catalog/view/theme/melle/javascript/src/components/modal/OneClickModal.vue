<template>
  <modal
    name="one-click-modal"
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
      <div class="dialog-c-title">Оформить заказ</div>
      <div class="dialog-c-text">
        <form
          class="fast-order form-vertical"
          enctype="multipart/form-data"
          method="post"
          v-on:submit.prevent="oneClick()"
        >
          <div class="sub-title">
            <p>* Обязательные для заполнения поля</p>
          </div>

          <div class="form-group">
            <label for="one-click-name" class="required"
              >Представьтесь <span class="required">*</span></label
            >
            <input
              placeholder="Укажите ФИО"
              id="one-click-name"
              type="text"
              class="form-control"
              v-model="name"
            />
          </div>

          <div class="form-group">
            <label for="one-click-phone" class="required"
              >Телефон <span class="required">*</span></label
            >
            <the-mask
              mask="+7 (###) ###-##-##"
              v-model.trim="phone"
              type="tel"
              :masked="false"
              id="one-click-phone"
              placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
              class="form-control"
            />
          </div>

          <div class="form-group">
            <label for="one-click-agree">
              <p-check
                name="agree"
                id="one-click-agree"
                v-model="agree"
              ></p-check>
              <span class="label-content"
                >Я принимаю условия<a
                  :href="konfidentsialnost_link"
                  target="_blank"
                >
                  политики конфиденциальности </a
                >и политики обработки персональных данных</span
              >
            </label>
          </div>

          <div class="button-row text-right">
            <button type="submit" class="btn btn-dark">Отправить</button>
          </div>
        </form>
      </div>
    </div>
  </modal>
</template>

<script>
import { mapState, mapActions } from 'vuex'

export default {
  name: 'OneClickModal',
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

      name: '',
      phone: '',
      agree: true,
    }
  },
  computed: {
    ...mapState('header', ['konfidentsialnost_link']),

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
    ...mapActions('product', ['oneClickRequest']),

    oneClick() {
      this.oneClickRequest({
        name: this.name,
        phone: this.phone,
        agree: this.agree,
        source: this.params.source || '',
      })
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
        this.$modal.hide('one-click-modal')
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
