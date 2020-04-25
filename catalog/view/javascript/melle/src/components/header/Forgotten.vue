<template>
  <section class="pas-rec">
    <sidebar-buttons />

    <h2 class="pas-rec__title">Восстановление пароля</h2>

    <div class="pas-rec__text-info">
      <p v-if="!sent" class="resetPasswordSuccess">
        Для восстановления пароля, пожалуйста, укажите Ваш e-mail, указанный при
        регистрации.
      </p>
      <p v-if="sent" class="resetPasswordSuccess">
        На Ваш почтовый ящик выслано письмо с инструкциями по восстановлению
        пароля
      </p>
    </div>

    <form
      v-show="!sent"
      method="post"
      id="resetPasswordForm"
      class="form-vertical"
      v-on:submit.prevent="send()"
    >
      <div class="pas-rec__form-group">
        <label class="pas-rec__form-label">Ваш e-mail</label>
        <input
          placeholder="example@example.com"
          class="reg__form-input"
          id="CabinetResetPasswordForm_email"
          type="email"
          v-model="email"
        />
      </div>
      <div class="pas-rec__form-group">
        <input type="submit" value="Отправить" class="pas-rec__form-send" />
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
    ...mapGetters('forgotten', ['getFormValue']),

    email: {
      get() {
        return this.getFormValue('email')
      },
      set(v) {
        this.updateFormValue({ k: 'email', v })
      },
    },
  },
  methods: {
    ...mapActions('forgotten', ['updateFormValue', 'sendRequest']),

    send() {
      this.sendRequest().then((res) => {
        console.log(res)
        if (res === true) {
          this.sent = true
        }
      })
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
