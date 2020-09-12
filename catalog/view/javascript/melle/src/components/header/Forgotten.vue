<template>
  <section class="sidebar-inner">
    <sidebar-buttons />

    <h4 class="title">Восстановление пароля</h4>

    <div class="sub-title">
      <p v-if="!sent">
        Для восстановления пароля, пожалуйста, укажите Ваш e-mail, указанный при
        регистрации.
      </p>
      <p v-if="sent" class="text-success">
        На Ваш почтовый ящик выслано письмо с инструкциями по восстановлению
        пароля
      </p>
    </div>

    <form
      v-show="!sent"
      method="post"
      class="form-vertical"
      v-on:submit.prevent="send()"
    >
      <div class="form-group">
        <label for="forgotten_email">Ваш e-mail</label>
        <input
          placeholder="example@example.com"
          class="form-control"
          id="forgotten_email"
          type="email"
          v-model="email"
        />
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-dark px-5">Отправить</button>
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
