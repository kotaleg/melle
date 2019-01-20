<template>
    <section class="sidebar-popup" :style="sidebarStyle">
        <div ref="sidebarPopupContent"
            :class="['sidebar-popup__content', {nameClass}]" :style="sidebarContentStyle"
            v-click-outside="configCO">
            <notifications
                :group="this.$codename+'_sidebar'"
                position="bottom right"/>

            <loading
                :active.sync="is_sidebar_loading"
                :is-full-page="false" />

            <slot/>

            <button @click="openSidebar(false)"
                class="sidebar-popup__button"
                :style="sidebarButtonStyle" />
        </div>
    </section>
</template>

<script>
import { has } from 'lodash'
import { mapState, mapActions } from 'vuex'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'

export default {
    props: {
        nameClass: {
            type: String,
            required: false,
            default: '',
        },
        position: {
            type: String,
            required: false,
            default: 'right',
        },
    },
    components: {
        Loading,
    },
    computed: {
        ...mapState('header', [
            'sidebar_opened',
            'is_sidebar_loading',
        ]),
        sidebarStyle() {
            let styles = {
                display: 'block',
                backgroundColor: this.sidebar_opened ? 'rgba(0, 0, 0, .5)' : 'rgba(0, 0, 0, 0)',
            }
            return styles
        },
        sidebarContentStyle() {
            let styles = {}
            styles[this.position] = this.sidebar_opened ? '0px' : `-${this.sidebarPopupContentWidth+50}px`
            return styles
        },
        sidebarButtonStyle() {
            let styles = {
                left: '',
                right: '',
            }
            styles[this.position] = this.windowWidth > 767 ? "calc(100% - 52px)" : "calc(100% - 40px)"
            return styles
        },
    },
    methods: {
        ...mapActions('header', [
            'openSidebar',
        ]),

        onKeyUp(event) {
            if (event.which === 27) {
                this.openSidebar(false)
            }
        },

        handleResize() {
            this.windowWidth = window.innerWidth
            this.windowHeight = window.innerHeight

            if (has(this.$refs, 'sidebarPopupContent')) {
                this.sidebarPopupContentWidth = this.$refs.sidebarPopupContent.clientWidth
            }
        },

        handleInit(create = true) {
            if (create === true) {
                document.documentElement.classList.add('open-menu')
                document.body.style = 'overflow:hidden;'
            } else {
                document.documentElement.classList.remove('open-menu')
                document.body.style = ''
            }
        },
    },
    data() {
        return {
            windowHeight: 0,
            windowWidth: 0,
            sidebarPopupContentWidth: 0,
            configCO: {
                handler: (e, el) => { this.openSidebar(false) },
                middleware: (e, el) => { return true },
                events: ["dblclick", "click"],
            },
        }
    },
    created() {
        window.addEventListener('resize', this.handleResize)
    },
    destroyed() {
        window.removeEventListener('resize', this.handleResize)
        window.removeEventListener('keyup', this.onKeyUp)
        this.handleInit(false)
    },
    mounted() {
        window.addEventListener('keyup', this.onKeyUp)
        this.handleInit(true)
        this.handleResize()
    },
}
</script>

<style lang="scss">
// NOTIFICATION
.vue-notification {
    font-size: 14px;
}
</style>