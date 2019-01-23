<template>
    <section class="reg">
        <sidebar-buttons />

        <h2 class="reg__title hideRegForm">Зарегистрируйтесь на Mademoiselle</h2>
        <div class="reg__text-info hideRegForm">
            <p>* Обязательные для заполнения поля</p>
        </div>
        <form class="reg__form hideRegForm form-vertical" id="registerForm" method="post" v-on:submit.prevent="register()">
            <div class="reg__form-group">
                <label class="reg__form-label">Представьтесь *</label>
                <div v-show="fieldHasError('name')" class="help-block error" id="CabinetRegisterForm_name_em_">{{ getFieldError('name') }}</div>
                <input v-model.trim="name" placeholder="Укажите ФИО" class="reg__form-input" id="CabinetRegisterForm_name" type="text">
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Ваш e-mail *</label>
                <div v-show="fieldHasError('email')" class="help-block error" id="CabinetRegisterForm_email_em_">{{ getFieldError('email') }}</div>
                <input v-model.trim="email" placeholder="Example@example.com" class="reg__form-input" id="CabinetRegisterForm_email" type="text">
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Телефон *</label>
                <div v-show="fieldHasError('phone')" class="help-block error" id="CabinetRegisterForm_phone_em_">{{ getFieldError('phone') }}</div>
                <the-mask mask="+7 (###) ###-##-##"
                        v-model.trim="phone"
                        type="tel" :masked="false"
                        id="CabinetRegisterForm_phone"
                        placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
                        class="reg__form-input" />
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Пароль *</label>
                <div v-show="fieldHasError('password')" class="help-block error" id="CabinetRegisterForm_password_em_">{{ getFieldError('password') }}</div>
                <input v-model.trim="password" placeholder="" class="reg__form-input" id="CabinetRegisterForm_password" type="password" maxlength="64">
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Повторите пароль *</label>
                <div v-show="fieldHasError('confirm')" class="help-block error" id="CabinetRegisterForm_repeatPassword_em_">{{ getFieldError('confirm') }}</div>
                <input v-model.trim="confirm" placeholder="" class="reg__form-input" id="CabinetRegisterForm_repeatPassword" type="password">
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Дата рождения</label>
                <div v-show="fieldHasError('birth')" class="help-block error" id="CabinetRegisterForm_birth_em_">{{ getFieldError('birth') }}</div>
                <the-mask mask="##.##.####"
                    v-model.trim="birth"
                    type="text" :masked="true"
                    id="CabinetRegisterForm_birth"
                    placeholder="дд.мм.гггг"
                    class="reg__form-input" />
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label">Скидочная карта (при наличии)</label>
                <div v-show="fieldHasError('discount_card')" class="help-block error" id="CabinetRegisterForm_personal_em_">{{ getFieldError('discount_card') }}</div>
                <input v-model.trim="discount_card" placeholder="" class="reg__form-input" id="CabinetRegisterForm_personal" type="text">
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label--checkbox" for="CabinetRegisterForm_accept">
                    <p-check name="newsletter" v-model="newsletter">Даю согласие на получение рассылки</p-check>
                </label>
                <div v-show="fieldHasError('newsletter')" class="help-block error" id="CabinetRegisterForm_accept_em_">{{ getFieldError('newsletter') }}</div>
            </div>
            <div class="reg__form-group">
                <label class="reg__form-label--checkbox" for="CabinetRegisterForm_accept">
                    <p-check name="agree" v-model="agree"></p-check>
                    <span>Я принимаю условия <a :href="konfidentsialnost_link" target="_blank">политики конфиденциальности</a> и политики обработки персональных данных</span>
                </label>
                <div v-show="fieldHasError('agree')" class="help-block error" id="CabinetRegisterForm_politic_em_">{{ getFieldError('agree') }}</div>
            </div>
            <div class="reg__form-group">
                <vue-recaptcha
                    ref="register_recaptcha"
                    @verify="onCaptchaVerified"
                    @expired="onCaptchaExpired"
                    size="invisible"
                    :sitekey="captchaKey" />
            </div>
            <div class="reg__form-group">
                <div class="reg__form-group-left"><input class="mail-us__form-send" type="submit" name="yt0" value="Регистрация" id="yt0"></div>
                <div class="reg__form-group-right">
                    <div class="reg__consent">
                        <p>Нажимая на кнопку «Регистрация », я соглашаюсь с условиями <a :href="public_offer_link" class="reg__consent-link" target="_blank">Публичной оферты.</a></p>
                    </div>
                </div>
            </div>
        </form>
    </section>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'
import VueRecaptcha from 'vue-recaptcha';

import SidebarButtons from '../partial/SidebarButtons.vue'

export default {
    components: {
        VueRecaptcha,
        'sidebar-buttons': SidebarButtons,
    },
    computed: {
        ...mapGetters('header', [
            'isCaptcha',
            'captchaKey',
        ]),
        ...mapGetters('register', [
            'getFormValue',
            'fieldHasError',
            'getFieldError',
        ]),
        ...mapState('header', [
            'konfidentsialnost_link',
            'public_offer_link',
        ]),

        name: {
            get() { return this.getFormValue('name') },
            set(v) { this.updateFormValue({ k: 'name', v }) },
        },
        email: {
            get() { return this.getFormValue('email') },
            set(v) { this.updateFormValue({ k: 'email', v }) },
        },
        phone: {
            get() { return this.getFormValue('phone') },
            set(v) { this.updateFormValue({ k: 'phone', v }) },
        },
        password: {
            get() { return this.getFormValue('password') },
            set(v) { this.updateFormValue({ k: 'password', v }) },
        },
        confirm: {
            get() { return this.getFormValue('confirm') },
            set(v) { this.updateFormValue({ k: 'confirm', v }) },
        },
        birth: {
            get() { return this.getFormValue('birth') },
            set(v) { this.updateFormValue({ k: 'birth', v }) },
        },
        discount_card: {
            get() { return this.getFormValue('discount_card') },
            set(v) { this.updateFormValue({ k: 'discount_card', v }) },
        },
        newsletter: {
            get() { return this.getFormValue('newsletter') },
            set(v) { this.updateFormValue({ k: 'newsletter', v }) },
        },
        agree: {
            get() { return this.getFormValue('agree') },
            set(v) { this.updateFormValue({ k: 'agree', v }) },
        },
    },
    methods: {
        ...mapActions('header', [
            'captchaRequest'
        ]),
        ...mapActions('register', [
            'updateFormValue',
            'registerRequest',
        ]),

        register() {
            if (this.isCaptcha) {
                this.$refs.register_recaptcha.execute();
            } else {
                this.registerRequest()
            }
        },
        onCaptchaVerified(recaptchaToken) {
            this.$refs.register_recaptcha.reset();

            this.captchaRequest(recaptchaToken)
                .then(captcha_res => {
                    if (captcha_res === true) {
                        this.registerRequest()
                    }
                })
        },
        onCaptchaExpired() {
            this.$refs.register_recaptcha.reset();
        },
    },
}
</script>

<style lang="scss">

</style>