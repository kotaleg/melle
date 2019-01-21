<template>
    <section class="auth">
        <sidebar-buttons />

        <h2 class="auth__title">Вход в личный кабинет</h2>
        <div class="auth__text-info">
            <p><span>Произвольный текст перед формой авторизации.</span></p>
        </div>
        <div id="authForm">
            <form class="auth__form form-vertical" id="yw1" method="post" v-on:submit.prevent="loginRequest()">
                <div class="auth__form-group">
                    <label class="auth__form-label">Ваш e-mail</label>
                    <div v-if="fieldHasError('email')" class="help-block error" id="CabinetLoginForm_email_em_">{{ getFieldError('email') }}</div>
                    <input placeholder="Example@example.com" class="auth__form-input" id="CabinetLoginForm_email" type="text" v-model="email">
                </div>
                <div class="auth__form-group">
                    <label class="auth__form-label">Пароль</label>
                    <div v-if="fieldHasError('password')" class="help-block error" id="CabinetLoginForm_password_em_">{{ getFieldError('password') }}</div>
                    <input placeholder="Пароль" class="auth__form-input" id="CabinetLoginForm_password" type="password" v-model="password">
                </div>
                <div class="auth__form-group">
                    <a @click="enableElement('forgotten')" href="javascript:void(0)" class="auth__form-link auth__form-link--pas">забыли пароль?</a>
                    <input type="submit" value="Войти" class="auth__form-send">
                    <a @click="enableElement('register')" href="javascript:void(0)" class="auth__form-link auth__form-link--reg">зарегистрироваться</a>
                </div>
            </form>
        </div>
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
        ...mapState('header', [
            'phone',
        ]),
        ...mapGetters('header', [
            'phoneLink',
        ]),
        ...mapGetters('login', [
            'getFormValue',
            'fieldHasError',
            'getFieldError',
        ]),

        email: {
            get() { return this.getFormValue('email') },
            set(v) { this.updateFormValue({ k: 'email', v }) },
        },
        password: {
            get() { return this.getFormValue('password') },
            set(v) { this.updateFormValue({ k: 'password', v }) },
        },
    },
    methods: {
        ...mapActions('header', [
            'enableElement',
        ]),
        ...mapActions('login', [
            'updateFormValue',
            'loginRequest',
        ]),
    },
    created() {

    },
}
</script>

<style lang="scss">

</style>