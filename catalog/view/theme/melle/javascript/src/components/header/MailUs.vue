<template>
  <section class="mail-us">
    <h2 class="mail-us__title">Написать нам</h2>
    <div class="hideForm"></div>
    <div class="block-zak-form">
      <div class="pas-rec__text-info">
        <p v-show="sent" class="resetPasswordSuccess">
          Спасибо за обращение! Мы свяжемся с вами!
        </p>
      </div>

      <form
        v-show="!sent"
        class="mail-us-modal__form hideForm form-vertical"
        id="mailUsForm"
        method="post"
        v-on:submit.prevent="mailUs()"
      >
        <div class="mail-us__form-group">
          <label class="mail-us__form-label required" for="AbstractForm_field_2"
            >Представьтесь <span class="required">*</span></label
          ><input
            class="mail-us__form-input"
            placeholder="Укажите ФИО"
            id="AbstractForm_field_2"
            type="text"
            v-model.trim="name"
          />
          <div
            v-show="fieldHasError('name')"
            class="help-block error"
            id="AbstractForm_field_2_em_"
          >
            {{ getFieldError('name') }}
          </div>
        </div>

        <div class="mail-us__form-group">
          <label class="mail-us__form-label required" for="AbstractForm_field_3"
            >E-mail <span class="required">*</span></label
          ><input
            class="mail-us__form-input"
            placeholder="example@example.com"
            id="AbstractForm_field_3"
            type="text"
            name="Email"
            v-model.trim="email"
          />
          <div
            v-show="fieldHasError('email')"
            class="help-block error"
            id="AbstractForm_field_3_em_"
          >
            {{ getFieldError('email') }}
          </div>
        </div>

        <div class="mail-us__form-group">
          <label class="mail-us__form-label required" for="AbstractForm_field_7"
            >Телефон <span class="required">*</span></label
          >
          <the-mask
            mask="+7 (###) ###-##-##"
            v-model.trim="phone"
            type="tel"
            :masked="false"
            id="AbstractForm_field_7"
            placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
            class="mail-us__form-input"
          />

          <div
            v-show="fieldHasError('phone')"
            class="help-block error"
            id="AbstractForm_field_7_em_"
          >
            {{ getFieldError('phone') }}
          </div>
        </div>

        <div class="mail-us__form-group">
          <label class="mail-us__form-label required" for="AbstractForm_field_5"
            >Текст сообщения <span class="required">*</span></label
          >
          <textarea
            class="mail-us__form-textarea"
            placeholder="Введите текст сообщения"
            id="AbstractForm_field_5"
            v-model.trim="message"
          ></textarea>
          <div
            v-show="fieldHasError('message')"
            class="help-block error"
            id="AbstractForm_field_5_em_"
          >
            {{ getFieldError('message') }}
          </div>
        </div>

        <div
          v-if="isCaptcha && captchaKey"
          class="mail-us__form-group js-mail-us__form-group--captcha"
        >
          <vue-recaptcha
            ref="mailus_recaptcha"
            @verify="onCaptchaVerified"
            @expired="onCaptchaExpired"
            size="invisible"
            :sitekey="captchaKey"
          />
        </div>

        <div style="margin-bottom: 25px;">
          <label for="AbstractForm_politic">
            <p-check name="agree" v-model="agree"></p-check>
            <span class="label-content"
              >Я принимаю условия<a
                :href="konfidentsialnost_link"
                target="_blank"
              >
                политики конфиденциальности </a
              >и политики обработки персональных данных</span
            >
          </label>
          <div
            v-show="fieldHasError('agree')"
            class="help-block error"
            id="AbstractForm_politic_em_"
          >
            {{ getFieldError('agree') }}
          </div>
        </div>

        <div class="mail-us__form-group">
          <input type="submit" value="Отправить" class="mail-us__form-send" />
        </div>
      </form>
    </div>
  </section>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import VueRecaptcha from 'vue-recaptcha'

export default {
  components: {
    VueRecaptcha,
  },
  computed: {
    ...mapGetters('header', ['isCaptcha', 'captchaKey']),
    ...mapGetters('mail_us', [
      'getFormValue',
      'fieldHasError',
      'getFieldError',
    ]),
    ...mapState('header', ['konfidentsialnost_link']),

    name: {
      get() {
        return this.getFormValue('name')
      },
      set(v) {
        this.updateFormValue({ k: 'name', v })
      },
    },
    email: {
      get() {
        return this.getFormValue('email')
      },
      set(v) {
        this.updateFormValue({ k: 'email', v })
      },
    },
    phone: {
      get() {
        return this.getFormValue('phone')
      },
      set(v) {
        this.updateFormValue({ k: 'phone', v })
      },
    },
    message: {
      get() {
        return this.getFormValue('message')
      },
      set(v) {
        this.updateFormValue({ k: 'message', v })
      },
    },
    agree: {
      get() {
        return this.getFormValue('agree')
      },
      set(v) {
        this.updateFormValue({ k: 'agree', v })
      },
    },
  },
  methods: {
    ...mapActions('header', ['captchaRequest']),
    ...mapActions('mail_us', ['updateFormValue', 'mailUsRequest']),

    mailUs() {
      if (this.isCaptcha && this.captchaKey && this.$refs.mailus_recaptcha) {
        this.$refs.mailus_recaptcha.execute()
      } else {
        this.mailUsRequest().then((res) => {
          if (res === true) {
            this.sent = true
          }
        })
      }
    },
    onCaptchaVerified(recaptchaToken) {
      this.$refs.mailus_recaptcha.reset()

      this.captchaRequest(recaptchaToken).then((captcha_res) => {
        if (captcha_res === true) {
          this.mailUsRequest().then((res) => {
            if (res === true) {
              this.sent = true
            }
          })
        }
      })
    },
    onCaptchaExpired() {
      this.$refs.mailus_recaptcha.reset()
    },
  },
  data() {
    return {
      sent: false,
    }
  },
}
</script>

<style lang="scss"></style>
