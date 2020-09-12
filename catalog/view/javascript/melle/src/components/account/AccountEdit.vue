<template>
  <section class="account-edit">
    <div class="sub-title">
      <p>Вы можете отредактировать свои личные данные.</p>
    </div>
    <div v-if="customerActivated" class="text-success">
      Пользователь успешно активирован
    </div>
    <form
      class="form-vertical"
      enctype="multipart/form-data"
      method="post"
      v-on:submit.prevent="edit()"
    >
      <div class="form-group">
        <label for="account-name">Представьтесь *</label>
        <input
          v-model.trim="name"
          placeholder="Укажите ФИО"
          :class="['form-control', { 'is-invalid': fieldHasError('name') }]"
          id="account-name"
          type="text"
        />
        <div v-show="fieldHasError('name')" class="invalid-feedback">
          {{ getFieldError('name') }}
        </div>
      </div>

      <div class="form-group">
        <label for="account-email">Ваш e-mail *</label>
        <input
          v-model.trim="email"
          placeholder="example@example.com"
          :class="['form-control', { 'is-invalid': fieldHasError('email') }]"
          id="account-email"
          type="text"
        />
        <div v-show="fieldHasError('email')" class="invalid-feedback">
          {{ getFieldError('email') }}
        </div>
      </div>

      <div class="form-group">
        <label for="account-phone">Телефон *</label>
        <the-mask
          mask="+7 (###) ###-##-##"
          v-model.trim="phone"
          type="tel"
          :masked="false"
          id="account-phone"
          placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
          :class="['form-control', { 'is-invalid': fieldHasError('phone') }]"
        />
        <div v-show="fieldHasError('phone')" class="invalid-feedback">
          {{ getFieldError('phone') }}
        </div>
      </div>

      <div class="form-group">
        <label for="account-password">Новый пароль</label>
        <input
          v-model.trim="password"
          placeholder=""
          :class="['form-control', { 'is-invalid': fieldHasError('password') }]"
          id="account-password"
          type="password"
          maxlength="64"
        />
        <div v-show="fieldHasError('password')" class="invalid-feedback">
          {{ getFieldError('password') }}
        </div>
      </div>

      <div class="form-group form-group--delivery">
        <label for="account-repeatPassword">Повторите новый пароль</label>
        <input
          v-model.trim="confirm"
          placeholder=""
          :class="['form-control', { 'is-invalid': fieldHasError('confirm') }]"
          id="account-repeatPassword"
          type="password"
        />
        <div v-show="fieldHasError('confirm')" class="invalid-feedback">
          {{ getFieldError('confirm') }}
        </div>
      </div>

      <div class="form-group">
        <label for="account-birth">Дата рождения</label>
        <the-mask
          mask="##.##.####"
          v-model.trim="birth"
          type="text"
          :masked="true"
          id="account-birth"
          placeholder="__.__.____"
          :class="['form-control', { 'is-invalid': fieldHasError('birth') }]"
        />
        <div v-show="fieldHasError('birth')" class="invalid-feedback">
          {{ getFieldError('birth') }}
        </div>
      </div>

      <div class="form-group form-group--discount">
        <label for="account-personal"
          >Скидочная карта <span>(при наличии)</span></label
        >
        <input
          v-model.trim="discount_card"
          placeholder=""
          :class="[
            'form-control',
            { 'is-invalid': fieldHasError('discount_card') },
          ]"
          id="account-personal"
          type="text"
        />
        <div v-show="fieldHasError('discount_card')" class="invalid-feedback">
          {{ getFieldError('discount_card') }}
        </div>
      </div>

      <div class="form-group form-group">
        <label for="account-accept">
          <p-check name="newsletter" v-model="newsletter"
            >Даю согласие на получение рассылки</p-check
          >
        </label>
        <div v-show="fieldHasError('newsletter')" class="invalid-feedback">
          {{ getFieldError('newsletter') }}
        </div>
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-dark">Сохранить</button>
      </div>
    </form>
  </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters('account', [
      'getFormValue',
      'fieldHasError',
      'getFieldError',
    ]),
    ...mapState('account', ['customerActivated']),

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
  },
  methods: {
    ...mapActions('account', ['updateFormValue', 'editRequest']),

    edit() {
      this.editRequest()
    },
  },
  created() {
    this.$store.dispatch('account/initData')
  },
}
</script>

<style lang="scss"></style>
