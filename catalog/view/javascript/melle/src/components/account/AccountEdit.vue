<template>
    <section class="lk-data">
       <div class="lk-data__info-text">
          <p>Вы можете отредактировать свои личные данные.</p>
       </div>
       <p class="new-log"></p>
       <form class="lk-data__form form-vertical" enctype="multipart/form-data" id="yw3" method="post" v-on:submit.prevent="edit()">
        <div class="lk-data__form-group"><label>Представьтесь *</label>
            <div v-show="fieldHasError('name')" class="help-block error" id="CabinetRegisterForm_name_em_">{{ getFieldError('name') }}</div>
            <input v-model.trim="name" placeholder="Укажите ФИО" class="reg__form-input" id="CabinetRegisterForm_name" type="text">
           </div>
        <div class="lk-data__form-group"><label>Ваш e-mail *</label>
            <div v-show="fieldHasError('email')" class="help-block error" id="CabinetRegisterForm_email_em_">{{ getFieldError('email') }}</div>
            <input v-model.trim="email" placeholder="Example@example.com" class="reg__form-input" id="CabinetRegisterForm_email" type="text">
        </div>
        <div class="lk-data__form-group">
            <label>Телефон *</label>
            <div v-show="fieldHasError('phone')" class="help-block error" id="CabinetRegisterForm_phone_em_">{{ getFieldError('phone') }}</div>
            <the-mask mask="+7 (###) ###-##-##"
                v-model.trim="phone"
                type="tel" :masked="false"
                id="CabinetRegisterForm_phone"
                placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
                class="reg__form-input" />
        </div>
        <div class="lk-data__form-group lk-data__form-group--delivery">
            <label>Новый пароль</label>
            <div v-show="fieldHasError('password')" class="help-block error" id="CabinetRegisterForm_password_em_">{{ getFieldError('password') }}</div>
            <input v-model.trim="password" placeholder="" class="reg__form-input" id="CabinetRegisterForm_password" type="password" maxlength="64">
        </div>
        <div class="lk-data__form-group lk-data__form-group--delivery">
            <label>Повторите новый пароль</label>
            <div v-show="fieldHasError('confirm')" class="help-block error" id="CabinetRegisterForm_repeatPassword_em_">{{ getFieldError('confirm') }}</div>
            <input v-model.trim="confirm" placeholder="" class="reg__form-input" id="CabinetRegisterForm_repeatPassword" type="password">
        </div>
        <div class="lk-data__form-group">
            <label>Дата рождения</label>
            <div v-show="fieldHasError('birth')" class="help-block error" id="CabinetRegisterForm_birth_em_">{{ getFieldError('birth') }}</div>
            <the-mask mask="##.##.####"
                v-model.trim="birth"
                type="text" :masked="true"
                id="CabinetRegisterForm_birth"
                placeholder="дд.мм.гггг"
                class="reg__form-input" />
        </div>
        <div class="lk-data__form-group lk-data__form-group--discount">
            <label>Скидочная карта <span>(при наличии)</span></label>
            <div v-show="fieldHasError('discount_card')" class="help-block error" id="CabinetRegisterForm_personal_em_">{{ getFieldError('discount_card') }}</div>
            <input v-model.trim="discount_card" placeholder="" class="reg__form-input" id="CabinetRegisterForm_personal" type="text">
        </div>
        <div class="lk-data__form-group lk-data__form-group">
            <label class="reg__form-label--checkbox" for="CabinetRegisterForm_accept">
                <p-check name="newsletter" v-model="newsletter">Даю согласие на получение рассылки</p-check>
            </label>
            <div v-show="fieldHasError('newsletter')" class="help-block error" id="CabinetRegisterForm_accept_em_">{{ getFieldError('newsletter') }}</div>
        </div>
        <div class="lk-data__form-group lk-data__form-group--send">
            <input type="submit" value="Сохранить">
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
    },
    methods: {
        ...mapActions('account', [
            'updateFormValue',
            'editRequest',
        ]),

        edit() {
            this.editRequest()
        },
    },
    created() {
        this.$store.dispatch('account/initData')
    },
}
</script>

<style lang="scss">

</style>