<template>
    <modal
        name="pick-block-type-modal"
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

        <div class="dialog-content pick-block-type-modal-container">
            <div class="dialog-c-text pick-block-type-modal-body text-center">
                <h3>Выберите тип блока:</h3>

                <div v-for="bt in blockTypes" class="melleb-type">
                    <a @click="pickHandler(bt.type)" class="btn btn-block">{{ bt.typeDescription }}</a>
                </div>
            </div>
        </div>
    </modal>
</template>

<script>
import { mapActions, mapState } from 'vuex'

export default {
    name: 'PickBlockTypeModal',
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
            defaultButtons: [{ title: 'Закрыть' }],
        }
    },
    computed: {
        ...mapState('shop', [
            'blockTypes',
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
        ...mapActions('shop', [
            'addNewBlock',
        ]),

        pickHandler(type) {
            this.addNewBlock(type)
            this.$modal.hide('pick-block-type-modal')
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
                this.$modal.hide('pick-block-type-modal')
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
.melleb-type {
    color: #000;
    font-size: 16px;

    a {
        color: inherit;
        font-size: inherit;
    }
}
</style>
