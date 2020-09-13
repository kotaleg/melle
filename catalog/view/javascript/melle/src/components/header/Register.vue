<template>
  <section class="sidebar-inner">
    <sidebar-buttons />

    <h4 class="title">Зарегистрируйтесь на Mademoiselle</h4>
    <div class="sub-title">
      <p>* Обязательные для заполнения поля</p>
    </div>
    <form class="form-vertical" method="post" v-on:submit.prevent="register()">
      <div class="form-group">
        <label for="register_name">Представьтесь *</label>
        <input
          v-model.trim="name"
          placeholder="Укажите ФИО"
          :class="['form-control', { 'is-invalid': fieldHasError('name') }]"
          id="register_name"
          type="text"
        />
        <div v-show="fieldHasError('name')" class="invalid-feedback">
          {{ getFieldError('name') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_email">Ваш e-mail *</label>
        <input
          v-model.trim="email"
          placeholder="example@example.com"
          :class="['form-control', { 'is-invalid': fieldHasError('email') }]"
          id="register_email"
          type="text"
        />
        <div v-show="fieldHasError('email')" class="invalid-feedback">
          {{ getFieldError('email') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_phone">Телефон *</label>
        <the-mask
          mask="+7 (###) ###-##-##"
          v-model.trim="phone"
          type="tel"
          :masked="false"
          id="register_phone"
          placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
          :class="['form-control', { 'is-invalid': fieldHasError('phone') }]"
        />
        <div v-show="fieldHasError('phone')" class="invalid-feedback">
          {{ getFieldError('phone') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_password">Пароль *</label>
        <input
          v-model.trim="password"
          placeholder=""
          :class="['form-control', { 'is-invalid': fieldHasError('password') }]"
          id="register_password"
          type="password"
          maxlength="64"
        />
        <div v-show="fieldHasError('password')" class="invalid-feedback">
          {{ getFieldError('password') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_password_confirm">Повторите пароль *</label>
        <input
          v-model.trim="confirm"
          placeholder=""
          :class="['form-control', { 'is-invalid': fieldHasError('confirm') }]"
          id="register_password_confirm"
          type="password"
        />
        <div v-show="fieldHasError('confirm')" class="invalid-feedback">
          {{ getFieldError('confirm') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_birth">Дата рождения</label>
        <the-mask
          mask="##.##.####"
          v-model.trim="birth"
          type="text"
          :masked="true"
          id="register_birth"
          placeholder="__.__.___"
          :class="['form-control', { 'is-invalid': fieldHasError('birth') }]"
        />
        <div v-show="fieldHasError('birth')" class="invalid-feedback">
          {{ getFieldError('birth') }}
        </div>
      </div>
      <div class="form-group">
        <label for="register_personal">Скидочная карта (при наличии)</label>
        <input
          v-model.trim="discount_card"
          placeholder=""
          :class="[
            'form-control',
            { 'is-invalid': fieldHasError('discount_card') },
          ]"
          id="register_personal"
          type="text"
        />
        <div v-show="fieldHasError('discount_card')" class="invalid-feedback">
          {{ getFieldError('discount_card') }}
        </div>
      </div>
      <div class="form-group">
        <label
          class="align-items-start checkbox-label d-flex"
          for="register_news"
        >
          <p-check
            name="newsletter"
            v-model="newsletter"
            id="register_news"
          ></p-check>
          <span>
            Даю согласие на получение рассылки
          </span>
        </label>
        <div v-show="fieldHasError('newsletter')" class="invalid-feedback">
          {{ getFieldError('newsletter') }}
        </div>
      </div>
      <div class="form-group">
        <label
          class="align-items-start checkbox-label d-flex"
          for="register_accept"
        >
          <p-check name="agree" v-model="agree" id="register_accept"></p-check>
          <span>
            Я принимаю условия
            <a :href="konfidentsialnost_link" target="_blank"
              >политики конфиденциальности</a
            >
            и политики обработки персональных данных
          </span>
        </label>
        <div v-show="fieldHasError('agree')" class="invalid-feedback">
          {{ getFieldError('agree') }}
        </div>
      </div>
      <div v-if="isCaptch && captchaKey" class="form-group">
        <vue-recaptcha
          ref="register_recaptcha"
          @verify="onCaptchaVerified"
          @expired="onCaptchaExpired"
          size="invisible"
          :sitekey="captchaKey"
        />
      </div>
      <div class="form-group row">
        <div class="col-md-5">
          <button class="btn btn-dark" type="submit">Регистрация</button>
        </div>
        <div class="col-md-7 pt-3 pt-sm-0 public-offer-confirm">
          <p>
            Нажимая на кнопку «Регистрация», я соглашаюсь с условиями
            <a :href="public_offer_link" target="_blank">Публичной оферты.</a>
          </p>
        </div>
      </div>
    </form>
  </section>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import VueRecaptcha from 'vue-recaptcha'

import SidebarButtons from '../partial/SidebarButtons.vue'

export default {
  components: {
    VueRecaptcha,
    'sidebar-buttons': SidebarButtons,
  },
  computed: {
    ...mapGetters('header', ['isCaptcha', 'captchaKey']),
    ...mapGetters('register', [
      'getFormValue',
      'fieldHasError',
      'getFieldError',
    ]),
    ...mapState('header', ['konfidentsialnost_link', 'public_offer_link']),

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
    password: {
      get() {
        return this.getFormValue('password')
      },
      set(v) {
        this.updateFormValue({ k: 'password', v })
      },
    },
    confirm: {
      get() {
        return this.getFormValue('confirm')
      },
      set(v) {
        this.updateFormValue({ k: 'confirm', v })
      },
    },
    birth: {
      get() {
        return this.getFormValue('birth')
      },
      set(v) {
        this.updateFormValue({ k: 'birth', v })
      },
    },
    discount_card: {
      get() {
        return this.getFormValue('discount_card')
      },
      set(v) {
        this.updateFormValue({ k: 'discount_card', v })
      },
    },
    newsletter: {
      get() {
        return this.getFormValue('newsletter')
      },
      set(v) {
        this.updateFormValue({ k: 'newsletter', v })
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
    ...mapActions('register', ['updateFormValue', 'registerRequest']),

    register() {
      if (this.isCaptcha && this.captchaKey && this.$refs.register_recaptcha) {
        this.$refs.register_recaptcha.execute()
      } else {
        this.registerRequest()
      }
    },
    onCaptchaVerified(recaptchaToken) {
      this.$refs.register_recaptcha.reset()

      this.captchaRequest(recaptchaToken).then((captcha_res) => {
        if (captcha_res === true) {
          this.registerRequest()
        }
      })
    },
    onCaptchaExpired() {
      this.$refs.register_recaptcha.reset()
    },
  },
}
</script>
