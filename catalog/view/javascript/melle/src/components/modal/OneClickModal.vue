<template>
    <modal
        name="one-click-modal"
        height="auto"
        :classes="['v--modal', 'vue-dialog', this.params.class]"
        :width="width"
        :pivot-y="0.3"
        :adaptive="true"
        :clickToClose="clickToClose"
        :transition="transition"
        @before-open="beforeOpened"
        @before-close="beforeClosed"
        @opened="$emit('opened', $event)"
        @closed="$emit('closed', $event)">

        <div class="dialog-content">
            <div class="dialog-c-title">Оформить заказ</div>
            <div class="dialog-c-text">
                <form class="fast-order form-vertical" enctype="multipart/form-data" id="yw0" method="post" v-on:submit.prevent="oneClick()">

                   <div class="fast-order__text-info">
                      <p>* Обязательные для заполнения поля</p>
                   </div>

                   <div class="fast-order__form-group ">
                      <label for="AbstractForm_field_9" class="required">Представьтесь <span class="required">*</span></label>
                      <input placeholder="Укажите ФИО" id="AbstractForm_field_9" type="text" v-model="name">
                   </div>

                   <div class="fast-order__form-group ">
                      <label for="AbstractForm_field_10" class="required">Телефон <span class="required">*</span></label>
                      <the-mask mask="+7 (###) ###-##-##"
                        v-model.trim="phone"
                        type="tel" :masked="false"
                        id="AbstractForm_field_7"
                        placeholder="+7 (_ _ _) _ _ _-_ _-_ _"
                        class="mail-us__form-input" />
                   </div>

                   <div class="field--checkbox">
                      <label for="AbstractForm_politic">
                        <p-check name="agree" v-model="agree"></p-check>
                        <span class="label-content">Я принимаю условия<a :href="konfidentsialnost_link" target="_blank"> политики конфиденциальности </a>и политики обработки персональных данных</span>
                      </label>
                   </div>

                   <div class="button-row">
                      <button type="submit" class="btn no-btn">Отправить</button>
                   </div>

                </form>
            </div>
        </div>
    </modal>
</template>

<script>
import { isEmpty, isString, trim } from 'lodash'
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    name: 'OneClickModal',
    components: {},
    props: {
        width: {
            type: [Number, String],
            default: 400,
        },
        clickToClose: {
            type: Boolean,
            default: true,
        },
        transition: {
            type: String,
            default: 'fade',
        },
    },
    data() {
        return {
            params: {},
            defaultButtons: [{ title: 'CLOSE' }],

            name: '',
            phone: '',
            agree: false,
        }
    },
    computed: {
        ...mapState('header', [
            'konfidentsialnost_link',
        ]),

        buttons() {
            return this.params.buttons || this.defaultButtons
        },

        buttonStyle() {
            return {
                flex: `1 1 ${100 / 1}%`,
            }
        },
    },
    methods: {
        ...mapActions('product', [
            'oneClickRequest',
        ]),

        oneClick() {
            this.oneClickRequest({
                name: this.name,
                phone: this.phone,
                agree: this.agree,
            })
        },

        beforeOpened(event) {
            window.addEventListener('keyup', this.onKeyUp)
            this.params = event.params || {}
            this.$emit('before-opened', event)
        },

        beforeClosed(event) {
            window.removeEventListener('keyup', this.onKeyUp)
            this.params = {}
            this.$emit('before-closed', event)
        },

        click(i, event, source = 'click') {
            const button = this.buttons[i]
            if (button && typeof button.handler === 'function') {
                button.handler(i, event)
            } else {
                this.$modal.hide('dialog')
            }
        },

        onKeyUp(event) {
            if (event.which === 13 && this.buttons.length > 0) {
                const buttonIndex =
                    this.buttons.length === 1
                        ? 0
                        : this.buttons.findIndex(button => button.default)
                if (buttonIndex !== -1) {
                    this.click(buttonIndex, event, 'keypress')
                }
            }
        },
    },
}
</script>
<style lang="scss">

</style>
