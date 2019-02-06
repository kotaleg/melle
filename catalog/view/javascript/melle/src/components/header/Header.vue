<template>
    <header class="header-page">

      <notifications
        :group="this.$codename+'_header'"
        position="bottom right"/>

      <loading
        :active.sync="is_loading"
        :is-full-page="true" />

        <div class="header-page__container">
            <a v-if="logo" class="header-page__logo" :href="base">
                <img :src="logo" alt="logo">
            </a>
            <a id="button-menu-mobile" href="#menu">
                <svg viewBox="0 0 53 53" width="24" height="24">
                   <path d="M2,13.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,13.5,2,13.5z"></path>
                   <path d="M2,28.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,28.5,2,28.5z"></path>
                   <path d="M2,43.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,43.5,2,43.5z"></path>
                </svg>
            </a>
            <div class="header-page__top-line">
                <ul class="top-line">
                   <li class="menu-tel">
                      <a :href="phoneLink" class="header-page__phone">
                         <svg viewBox="0 0 32 32" width="20" height="20">
                            <path d="M24,0L8,0C6.342,0,5,1.343,5,3v26c0,1.658,1.343,3,3,3h16c1.656,0,3-1.344,3-3V3C27,1.342,25.656,0,24,0z    M25,29c0,0.551-0.449,1-1,1H8c-0.552,0-1-0.447-1-1v-2.004h18V29z M25,25.996H7V6L25,6V25.996z M25,5L7,5V3c0-0.552,0.448-1,1-1   L24,2c0.551,0,1,0.448,1,1V5z"></path>
                            <path d="M18,3.5C18,3.776,17.775,4,17.5,4h-3C14.223,4,14,3.776,14,3.5l0,0C14,3.223,14.223,3,14.5,3h3   C17.775,3,18,3.223,18,3.5L18,3.5z"></path>
                            <path d="M17,28.496c0,0.275-0.225,0.5-0.5,0.5h-1c-0.276,0-0.5-0.225-0.5-0.5l0,0c0-0.277,0.224-0.5,0.5-0.5h1   C16.775,27.996,17,28.219,17,28.496L17,28.496z"></path>
                         </svg>
                         <b>{{ phone }}</b>
                      </a>
                   </li>
                   <li class="menu-search">
                      <div class="search-block">
                         <div class="search-modal__form">
                            <input name="q" type="search" v-model="search" @keyup.enter="searchAction">
                            <input type="submit" value="" @click="searchAction" class="cursorred">
                        </div>
                      </div>
                   </li>
                   <li class="menu-mail panel-buttons__mail">
                      <a id="melle_mail_us" class="panel-buttons__mail-link cursorred" @click="enableElement('mail_us')" href="javascript:void(0)">
                         <svg viewBox="0 0 612.074 612.074" width="25" height="25">
                            <path d="M612.074,132.141v-2.38c0-8.849-4.016-19.26-11.229-26.473l-0.818-0.818c0,0-0.818,0-0.818-0.818c-1.636-1.636-3.198-2.38-4.833-4.016c-0.818,0-0.818-0.818-1.636-0.818c-1.636-0.818-4.016-1.636-5.652-2.38c-0.818,0-0.818-0.818-1.636-0.818c-2.38-0.818-4.833-1.636-7.213-1.636c-0.818,0-0.818,0-1.636,0c-2.38,0-5.651-0.818-8.849-0.818H43.427c-3.198,0-6.395,0-9.667,0.818c-0.818,0-1.636,0-2.38,0.818c-2.38,0.818-4.834,0.818-6.395,1.636c-0.818,0-0.818,0.818-1.636,0.818c-1.636,0.818-4.016,1.636-5.652,2.38l-0.818,0.818c-1.636,0.818-3.198,2.38-4.834,3.198c-0.818,0.818-1.636,1.636-2.38,2.38C4.016,110.428,0.818,117.715,0,125.746c0,0.818,0,0.818,0,1.636v357.384c0,0.818,0,0.818,0,1.636c1.636,11.229,7.213,20.896,15.244,26.473c7.213,4.833,16.062,8.031,26.473,8.031H569.39c0,0,0,0,0.818,0l0,0c2.38,0,5.651,0,8.031-0.818c0.818,0,0.818,0,1.636,0c2.38-0.818,4.834-0.818,6.395-1.636h0.818c17.698-6.395,24.911-21.714,24.911-36.14v-2.38v-0.818v-0.818V134.521c0-0.818,0-0.818,0-1.636C612.074,132.959,612.074,132.959,612.074,132.141z M560.69,120.913l-252.98,246.51l-57.854-56.218l0,0L51.459,120.838H560.69V120.913z M29.819,475.099V140.991l187.095,179.882L29.819,475.099z M299.679,491.905H56.292l182.336-149.393l58.597,57.036c2.38,2.38,4.834,3.198,7.213,4.016h0.818c0.818,0,0.818,0,1.636,0l0,0c0.818,0,1.636,0,1.636,0h0.818c2.38-0.818,5.651-1.636,7.213-4.016l55.4-53.838l183.079,146.196H299.679z M582.329,475.843L394.417,324.07L582.329,140.99V475.843z"></path>
                         </svg>
                      </a>
                   </li>
                   <li class="menu-pay"><a :href="delivery_link" class="cursorred">оплата и <br>доставка</a></li>
                   <li class="menu-enter">
                      <a v-if="!is_logged" id="panel-buttons__login-link" @click="enableElement('login')" href="javascript:void(0)">Вход</a>
                      <a v-if="!is_logged" id="panel-buttons__reg-link" @click="enableElement('register')" href="javascript:void(0)">Регистрация</a>
                      <a v-if="is_logged" :href="account_link">Кабинет</a>
                      <a v-if="is_logged" :href="logout_link">Выход</a>

                      <a @click="accountAction" id="panel-buttons__login-link" class="mob-icon-user">
                         <svg viewBox="0 0 16 20" width="20px" height="20px">
                            <path d="M15.9894459,19.4710744 L15.9894459,16.6694215 C15.9894459,13.2024793 13.6675462,10.2561983 10.4717678,9.23966942 C12.0084433,8.39256198 13.0511873,6.78099174 13.0511873,4.9338843 C13.0511873,2.21487603 10.7883905,-1.7616762e-15 8.01055409,-1.7616762e-15 C5.23271768,-1.7616762e-15 2.96992084,2.21487603 2.96992084,4.9338843 C2.96992084,6.78099174 4.01266491,8.39256198 5.54934037,9.23966942 C2.34934037,10.2561983 0.0316622691,13.2024793 0.0316622691,16.6694215 L0.0316622691,19.4710744 C0.0316622691,19.7520661 0.263852243,19.9793388 0.550923483,19.9793388 L15.478628,19.9793388 C15.7572559,19.9752066 15.9894459,19.7520661 15.9894459,19.4710744 Z M4,4.9338843 C4,2.77272727 5.79841689,1.01239669 8.00633245,1.01239669 C10.214248,1.01239669 12.0126649,2.77272727 12.0126649,4.9338843 C12.0126649,7.09504132 10.214248,8.8553719 8.00633245,8.8553719 C5.79841689,8.8553719 4,7.09504132 4,4.9338843 Z M14.9551451,18.9628099 L1.05751979,18.9628099 L1.05751979,16.6652893 C1.05751979,12.9173554 4.17308707,9.86363636 8.00633245,9.86363636 C11.8395778,9.86363636 14.9551451,12.9132231 14.9551451,16.6652893 L14.9551451,18.9628099 Z" id="Shape"></path>
                         </svg>
                      </a>
                   </li>
                   <li class="menu-bascet panel-buttons__basket shop-cart-container">
                      <a @click="enableElement('cart')" class="header-page__basket-mobile panel-buttons__basket-link" href="javascript:void(0)">
                         <svg viewBox="0 0 1489.733 1698.268" width="62" height="62">
                            <path d="M1489.668,1540.226l-50.734-1145.759c-0.896-84.585-70.35-153.199-155.591-153.199h-257.892   C1004.523,106.268,886.593,0,744.689,0C602.747,0,484.784,106.268,463.85,241.268H206.313   c-85.217,0-154.649,68.616-155.543,153.202L0.064,1540.188C0.022,1541.16,0,1542.146,0,1543.121   c0,85.543,69.797,155.146,155.592,155.146h1178.556c85.79,0,155.586-69.583,155.586-155.127C1489.733,1542.166,1489.712,1541.2,1489.668,1540.226z M744.689,132.141c68.746,0,126.941,46.126,145.617,109.126H598.998   C617.684,178.268,675.908,132.141,744.689,132.141z M1334.147,1566.268H155.592c-12.811,0-22.917-9.645-23.43-22.062   l50.674-1145.048c0.043-0.971,0.064-2.111,0.064-3.084c0-12.695,10.283-22.806,23.412-22.806H460v241.459   c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268h304v241.459c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268   h255.343c13.153,0,23.457,10.095,23.457,22.79c0,0.974,0.021,2.023,0.064,2.998l50.706,1145.117   C1357.057,1556.586,1346.953,1566.268,1334.147,1566.268z"></path>
                         </svg>
                         <i v-if="cartCount > 0" class="itemCartCountM">{{ cartCount }}</i>
                      </a>
                   </li>
                </ul>
                <ul class="header-page__bottom-line">
                   <li v-for="(m, i) in menu"
                        @mouseover="menuHandler({i, status:true})"
                        @mouseleave="menuHandler({i, status:false})"
                        :class="[{'active': m.active}]">

                      <a :href="m.url">{{ m.title }}</a>
                      <ul v-if="m.children.length > 0">
                         <li v-for="c in m.children">
                            <a :href="c.url">{{ c.title }}</a>
                         </li>
                      </ul>
                   </li>
                </ul>
             </div>
             <a href="javascript:void(0)" class="header-page__basket panel-buttons__basket-link cursorred" @click="enableElement('cart')">
                <svg viewBox="0 0 1489.733 1698.268" width="62" height="62">
                    <path d="M1489.668,1540.226l-50.734-1145.759c-0.896-84.585-70.35-153.199-155.591-153.199h-257.892   C1004.523,106.268,886.593,0,744.689,0C602.747,0,484.784,106.268,463.85,241.268H206.313   c-85.217,0-154.649,68.616-155.543,153.202L0.064,1540.188C0.022,1541.16,0,1542.146,0,1543.121   c0,85.543,69.797,155.146,155.592,155.146h1178.556c85.79,0,155.586-69.583,155.586-155.127C1489.733,1542.166,1489.712,1541.2,1489.668,1540.226z M744.689,132.141c68.746,0,126.941,46.126,145.617,109.126H598.998   C617.684,178.268,675.908,132.141,744.689,132.141z M1334.147,1566.268H155.592c-12.811,0-22.917-9.645-23.43-22.062   l50.674-1145.048c0.043-0.971,0.064-2.111,0.064-3.084c0-12.695,10.283-22.806,23.412-22.806H460v241.459   c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268h304v241.459c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268   h255.343c13.153,0,23.457,10.095,23.457,22.79c0,0.974,0.021,2.023,0.064,2.998l50.706,1145.117   C1357.057,1556.586,1346.953,1566.268,1334.147,1566.268z"></path>
                </svg>
                <i v-if="cartCount" style="color: white" class="itemCartCountD">{{ cartCount }}</i>
            </a>
        </div>

        <transition name="fade" appear>
            <header-sidebar v-if="sidebar_opened">
                <h-cart v-if="isElementActive('cart')" />
                <h-login v-if="isElementActive('login')" />
                <h-register v-if="isElementActive('register')" />
                <h-mail-us v-if="isElementActive('mail_us')" />
                <h-forgotten v-if="isElementActive('forgotten')" />
                <h-filter v-if="isElementActive('filter')" />
            </header-sidebar>
        </transition>

        <input type="hidden" id="melle_reload_cart" @click="updateCartDataRequest()">
        <input type="hidden" id="melle_clear_cart" @click="clearCartRequest()">
    </header>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'

import Sidebar from './Sidebar.vue'
import Cart from './Cart.vue'
import Login from './Login.vue'
import Register from './Register.vue'
import MailUs from './MailUs.vue'
import Forgotten from './Forgotten.vue'
import Filter from './Filter.vue'


export default {
    components: {
        Loading,
        'header-sidebar': Sidebar,
        'h-mail-us': MailUs,
        'h-login': Login,
        'h-register': Register,
        'h-cart': Cart,
        'h-forgotten': Forgotten,
        'h-filter': Filter,
    },
    computed: {
        ...mapState('header', [
            'base',
            'logo',
            'phone',
            'menu',
            'sidebar_opened',
            'is_logged',
            'is_loading',
            'logout_link',
            'account_link',
            'delivery_link',
        ]),
        ...mapState('cart', {
            cartCount: 'count',
        }),
        ...mapGetters('header', [
            'isElementActive',
            'phoneLink',
            'accountLink',
        ]),
    },
    methods: {
        ...mapActions('header', [
            'menuHandler',
            'enableElement',
        ]),
        ...mapActions('cart', [
            'updateCartDataRequest',
            'clearCartRequest',
        ]),

        searchAction() {
            let url = this.base + 'index.php?route=product/search'
            url += '&search=' + encodeURIComponent(this.search);
            location = url;
        },

        accountAction() {
            if (!this.is_logged) {
                this.enableElement('login')
            } else {
                window.location = this.account_link
            }
        },
    },
    data() {
        return {
            search: '',
        }
    },
    created() {
        this.$store.dispatch('header/initData')
        this.$store.dispatch('cart/initData')
        this.$store.dispatch('gtm/initData')
    },
    mounted() {
        // GTM
        this.$store.dispatch('gtm/ecommShittyPush')

        $('.melle_reload_cart').on('click', () => {
            this.updateCartDataRequest()
        })
        $('.melle_clear_cart').on('click', () => {
            this.clearCartRequest()
        })
    },
}
</script>

<style lang="scss" scoped="true">
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>