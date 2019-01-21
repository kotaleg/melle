<template>
    <section class="mail-us">
        <h2 class="mail-us__title">Написать нам</h2>
        <div class="hideForm"></div>
        <div class="block-zak-form">
            <form class="mail-us-modal__form hideForm form-vertical" id="mailUsForm" method="post">
                <div class="mail-us__form-group">
                    <label class="mail-us__form-label required" for="AbstractForm_field_2">Представьтесь <span class="required">*</span></label><input class="mail-us__form-input" placeholder="Укажите ФИО" id="AbstractForm_field_2" type="text" v-model.trim="name">
                    <div class="help-block error" id="AbstractForm_field_2_em_"></div>
                </div>
                <div class="mail-us__form-group">
                    <label class="mail-us__form-label required" for="AbstractForm_field_3">E-mail <span class="required">*</span></label><input class="mail-us__form-input" placeholder="example@example.com" id="AbstractForm_field_3" type="text" name="Email" v-model.trim="email">
                    <div class="help-block error" id="AbstractForm_field_3_em_"></div>
                </div>
                <div class="mail-us__form-group">
                    <label class="mail-us__form-label required" for="AbstractForm_field_7">Телефон <span class="required">*</span></label><input class="mail-us__form-input" placeholder="+7 (_ _ _) _ _ _-_ _-_ _" id="AbstractForm_field_7" type="text" v-model.trim="phone">
                    <div class="help-block error" id="AbstractForm_field_7_em_"></div>
                </div>
                <div class="mail-us__form-group">
                    <label class="mail-us__form-label required" for="AbstractForm_field_5">Текст сообщения <span class="required">*</span></label>
                    <textarea class="mail-us__form-textarea" placeholder="Введите текст сообщения" id="AbstractForm_field_5" v-model.trim="message"></textarea>
                    <div class="help-block error" id="AbstractForm_field_5_em_"></div>
                </div>
                <div class="mail-us__form-group js-mail-us__form-group--captcha">

                    <div id="wb4565c_yw2" class="QapTcha inpbl kod ItexQapTcha"></div>
                    <div class="help-block error" id="AbstractForm_captcha_em_" style="display:none"></div>
                </div>
                <div class="field--checkbox">
                    <label for="AbstractForm_politic">
                        <input id="ytAbstractForm_politic" type="hidden" value="0" name="AbstractForm[politic]">
                        <input name="AbstractForm[politic]" id="AbstractForm_politic" value="1" type="checkbox">
                        <span></span><span class="label-content">Я принимаю условия<a href="konfidentsialnost" target="_blank"> политики конфиденциальности </a>и политики обработки персональных данных</span></label>
                    <div class="help-block error" id="AbstractForm_politic_em_" style="display:none"></div>
                </div>
                <div class="mail-us__form-group"><input type="submit" value="Отправить" class="mail-us__form-send"></div>
            </form>

        </div>
    </section>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex'

export default {
    components: {

    },
    computed: {
        ...mapGetters('mail_us', [
            'getFormValue',
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
        message: {
            get() { return this.getFormValue('message') },
            set(v) { this.updateFormValue({ k: 'message', v }) },
        },
    },
    methods: {
        ...mapActions('mail_us', [
            'updateFormValue',
        ])
    },
    created() {

    },
}
</script>

<style lang="scss">

</style>