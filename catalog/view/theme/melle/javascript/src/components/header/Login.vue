<template>
  <section class="sidebar-inner">
    <sidebar-buttons />

    <h4 class="title pb-3">Вход в личный кабинет</h4>
    <form
      class="form-vertical"
      method="post"
      v-on:submit.prevent="loginRequest()"
    >
      <div class="form-group">
        <label for="login_email">Ваш e-mail</label>
        <input
          placeholder="example@example.com"
          class="form-control"
          id="login_email"
          type="text"
          v-model="email"
        />
      </div>
      <div class="form-group">
        <label for="login_password">Пароль</label>
        <input
          placeholder="Пароль"
          class="form-control"
          id="login_password"
          type="password"
          v-model="password"
        />
      </div>
      <div class="align-items-end d-flex flex-column form-group">
        <a
          @click="enableElement('forgotten')"
          href="javascript:void(0)"
          class="mb-2"
          >забыли пароль?</a
        >
        <button type="submit" class="btn btn-dark px-5">Войти</button>
        <a
          @click="enableElement('register')"
          href="javascript:void(0)"
          class="mt-2"
          >зарегистрироваться</a
        >
      </div>
    </form>
  </section>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

import SidebarButtons from '../partial/SidebarButtons.vue'

export default {
  components: {
    'sidebar-buttons': SidebarButtons,
  },
  computed: {
    ...mapGetters('login', ['getFormValue']),

    email: {
      get() {
        return this.getFormValue('email')
      },
      set(v) {
        this.updateFormValue({ k: 'email', v })
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
  },
  methods: {
    ...mapActions('login', ['updateFormValue', 'loginRequest']),
    ...mapActions('header', ['enableElement']),
  },
}
</script>
