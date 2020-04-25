<template>
  <fixed-header>
    <header class="header-page">
      <notifications
        :group="this.$codename + '_header'"
        position="bottom right"
      />

      <loading :active.sync="is_loading" :is-full-page="true" />

      <div class="header-page__container">
        <a v-if="logo" class="header-page__logo" :href="base">
          <img :src="logo" alt="logo" />
        </a>
        <a id="button-menu-mobile" href="#menu">
          <svg viewBox="0 0 53 53" width="24" height="24">
            <path
              d="M2,13.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,13.5,2,13.5z"
            ></path>
            <path
              d="M2,28.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,28.5,2,28.5z"
            ></path>
            <path
              d="M2,43.5h49c1.104,0,2-0.896,2-2s-0.896-2-2-2H2c-1.104,0-2,0.896-2,2S0.896,43.5,2,43.5z"
            ></path>
          </svg>
        </a>
        <div class="header-page__top-line">
          <ul class="top-line">
            <li class="menu-tel">
              <a :href="phoneLink" class="header-page__phone">
                <svg
                  version="1.1"
                  id="Слой_1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:xlink="http://www.w3.org/1999/xlink"
                  x="0px"
                  y="0px"
                  viewBox="0 0 85 85"
                  style="
                    enable-background: new 0 0 85 85;
                    width: 26px;
                    top: 8px;
                  "
                  xml:space="preserve"
                >
                  <path
                    class="st0"
                    d="M20.5,4.6c3,1.1,5,3.3,7,5.5c2.6,2.9,4.9,6,6.9,9.4c0.7,1.2,1.2,2.4,1.7,3.7c0.8,1.9,0.4,3.7-1.1,5
                        c-1.7,1.4-3.6,2.6-5.4,3.9c-1.2,0.9-2.5,1.6-3.6,2.5c-0.9,0.7-0.8,0.8-0.4,1.8c1.8,3.9,4.3,7.4,7.2,10.6c3.4,3.8,7.2,7.1,11.6,9.7
                        c1.1,0.6,2.2,1.1,3.3,1.7c0.5,0.3,0.8,0.2,1.1-0.2c0.8-0.9,1.5-1.9,2.2-2.9c1.2-1.8,2.4-3.6,3.6-5.4c0.3-0.5,0.8-0.9,1.2-1.3
                        c1.4-1.2,2.9-1.5,4.7-0.9c2.7,1,5.1,2.5,7.4,4.2c3.3,2.3,6.5,4.8,9.1,7.9c0.2,0.3,0.5,0.6,0.7,0.8c1.8,2.1,1.8,4.4,0.6,6.8
                        c-2.4,4.6-5.7,8.4-10.5,10.8c-1.2,0.6-2.5,0.9-3.8,0.8c-6.3-0.1-12.3-1.7-17.9-4.3c-15-7.1-26.4-18.1-34.7-32.4
                        c-2.8-4.8-4.8-9.9-5.8-15.4C5.4,25,5.3,23,5.1,21.1c0-0.3-0.1-0.6-0.1-0.9c0-0.3,0-0.7,0-1c0.3-1.1,0.4-2.3,1-3.3
                        c2.9-5.5,7.2-9.4,13.3-11.2C19.7,4.6,20.1,4.6,20.5,4.6z M63.9,75.3c1,0.1,2.3-0.2,3.5-0.9c3.5-2.1,6.1-5.1,7.9-8.8
                        c0.5-1,0.5-1.9-0.3-2.8c-1.6-2-3.5-3.7-5.5-5.3c-2.9-2.3-5.8-4.4-9.2-6C59,51,58.8,51,58,52c-0.9,1.2-1.7,2.6-2.6,3.8
                        c-1.2,1.7-2.4,3.5-3.8,5.1c-1.4,1.6-3.1,1.8-5,0.9c-0.7-0.3-1.3-0.6-2-0.9c-5-2.6-9.3-6.1-13.2-10.2c-3.8-4-7.1-8.4-9.4-13.5
                        c-0.7-1.6-0.6-3,0.5-4.3c1-1.1,2.2-2.1,3.4-2.9c1.8-1.3,3.7-2.3,5.5-3.6c1.8-1.3,1.7-1.3,0.8-3.2c-1.5-3.1-3.5-5.8-5.6-8.5
                        C25,12.7,23.2,10.7,21,9c-0.9-0.7-1.7-0.7-2.6-0.3C15,10.4,12.2,12.8,10,16c-0.9,1.3-1.3,2.8-1.3,4.4c0.2,5.7,1.8,11,4.1,16.1
                        c3.3,7.2,7.9,13.5,13.4,19.2C33,62.7,40.5,68.3,49.4,72C53.9,73.9,58.6,75.1,63.9,75.3z"
                  />
                  <path
                    class="st0"
                    d="M63.9,75.3c-5.3-0.2-10-1.4-14.5-3.3c-8.9-3.7-16.4-9.3-23.1-16.2c-5.5-5.7-10.1-12-13.4-19.2
                        C10.5,31.5,9,26.1,8.8,20.4C8.7,18.8,9.1,17.3,10,16c2.1-3.2,4.9-5.6,8.4-7.3C19.4,8.2,20.2,8.3,21,9c2.1,1.7,4,3.7,5.6,5.8
                        c2.1,2.7,4.1,5.4,5.6,8.5c0.9,1.9,1,1.9-0.8,3.2c-1.8,1.3-3.7,2.4-5.5,3.6c-1.2,0.9-2.4,1.8-3.4,2.9c-1.1,1.2-1.2,2.7-0.5,4.3
                        c2.2,5.1,5.5,9.5,9.4,13.5c3.9,4,8.2,7.6,13.2,10.2c0.6,0.3,1.3,0.6,2,0.9c1.9,0.9,3.6,0.7,5-0.9c1.4-1.6,2.6-3.4,3.8-5.1
                        c0.9-1.3,1.7-2.6,2.6-3.8c0.8-1.1,1.1-1.1,2.3-0.5c3.3,1.6,6.3,3.7,9.2,6c2,1.6,3.9,3.3,5.5,5.3c0.7,0.9,0.8,1.8,0.3,2.8
                        c-1.8,3.7-4.4,6.6-7.9,8.8C66.2,75.1,64.9,75.5,63.9,75.3z"
                  />
                </svg>
                <b>{{ phone }}</b>
              </a>
            </li>
            <li class="menu-search">
              <ais-instant-search
                v-if="searchClient && searchIndex"
                :index-name="searchIndex"
                :search-client="searchClient"
              >
                <div class="melle-header-autocomplete">
                  <ais-autocomplete>
                    <div slot-scope="{ currentRefinement, indices, refine }">
                      <div class="search-bar-wrapper">
                        <form
                          class="search-bar-form"
                          v-on:submit.prevent="searchAction(currentRefinement)"
                        >
                          <div class="search-input-wrapper">
                            <input
                              type="text"
                              name="search"
                              autocapitalize="off"
                              autocomplete="off"
                              autocorrect="off"
                              spellcheck="false"
                              maxlength="255"
                              placeholder="Поиск"
                              :value="currentRefinement"
                              @input="refine($event.currentTarget.value)"
                            />
                          </div>
                        </form>

                        <div v-if="currentRefinement">
                          <div class="search-autocomplete-items-wrapper">
                            <div class="search-prefix"></div>
                            <section class="search-suggestions">
                              <div
                                v-for="index in indices"
                                :key="index.label"
                                class="melle-autocomplete-items"
                              >
                                <a
                                  v-for="hit in index.hits"
                                  :key="hit.objectID"
                                  :href="`${product_link_placeholder}${hit.productId}`"
                                  class="suggestions-item"
                                >
                                  <ais-highlight attribute="h1" :hit="hit" />
                                </a>
                              </div>
                              <ais-powered-by />
                            </section>
                          </div>
                        </div>
                      </div>
                    </div>
                  </ais-autocomplete>
                </div>
              </ais-instant-search>
            </li>
            <li class="menu-mail panel-buttons__mail">
              <a
                id="melle_mail_us"
                class="panel-buttons__mail-link cursorred"
                @click="enableElement('mail_us')"
                href="javascript:void(0)"
              >
                <svg viewBox="0 0 612.074 612.074" width="25" height="25">
                  <path
                    d="M612.074,132.141v-2.38c0-8.849-4.016-19.26-11.229-26.473l-0.818-0.818c0,0-0.818,0-0.818-0.818c-1.636-1.636-3.198-2.38-4.833-4.016c-0.818,0-0.818-0.818-1.636-0.818c-1.636-0.818-4.016-1.636-5.652-2.38c-0.818,0-0.818-0.818-1.636-0.818c-2.38-0.818-4.833-1.636-7.213-1.636c-0.818,0-0.818,0-1.636,0c-2.38,0-5.651-0.818-8.849-0.818H43.427c-3.198,0-6.395,0-9.667,0.818c-0.818,0-1.636,0-2.38,0.818c-2.38,0.818-4.834,0.818-6.395,1.636c-0.818,0-0.818,0.818-1.636,0.818c-1.636,0.818-4.016,1.636-5.652,2.38l-0.818,0.818c-1.636,0.818-3.198,2.38-4.834,3.198c-0.818,0.818-1.636,1.636-2.38,2.38C4.016,110.428,0.818,117.715,0,125.746c0,0.818,0,0.818,0,1.636v357.384c0,0.818,0,0.818,0,1.636c1.636,11.229,7.213,20.896,15.244,26.473c7.213,4.833,16.062,8.031,26.473,8.031H569.39c0,0,0,0,0.818,0l0,0c2.38,0,5.651,0,8.031-0.818c0.818,0,0.818,0,1.636,0c2.38-0.818,4.834-0.818,6.395-1.636h0.818c17.698-6.395,24.911-21.714,24.911-36.14v-2.38v-0.818v-0.818V134.521c0-0.818,0-0.818,0-1.636C612.074,132.959,612.074,132.959,612.074,132.141z M560.69,120.913l-252.98,246.51l-57.854-56.218l0,0L51.459,120.838H560.69V120.913z M29.819,475.099V140.991l187.095,179.882L29.819,475.099z M299.679,491.905H56.292l182.336-149.393l58.597,57.036c2.38,2.38,4.834,3.198,7.213,4.016h0.818c0.818,0,0.818,0,1.636,0l0,0c0.818,0,1.636,0,1.636,0h0.818c2.38-0.818,5.651-1.636,7.213-4.016l55.4-53.838l183.079,146.196H299.679z M582.329,475.843L394.417,324.07L582.329,140.99V475.843z"
                  ></path>
                </svg>
              </a>
            </li>
            <li class="menu-pay">
              <a href="https://melle.online/opt" class="cursorred"
                >оптовый<br />сайт</a
              >
            </li>
            <li class="menu-enter">
              <a
                v-if="!is_logged"
                id="panel-buttons__login-link"
                @click="enableElement('login')"
                href="javascript:void(0)"
                >Вход</a
              >
              <a
                v-if="!is_logged"
                id="panel-buttons__reg-link"
                @click="enableElement('register')"
                href="javascript:void(0)"
                >Регистрация</a
              >
              <a v-if="is_logged" :href="account_link">Кабинет</a>
              <a v-if="is_logged" :href="logout_link">Выход</a>

              <a
                @click="accountAction"
                id="panel-buttons__login-link"
                class="mob-icon-user"
              >
                <svg viewBox="0 0 16 20" width="20px" height="20px">
                  <path
                    d="M15.9894459,19.4710744 L15.9894459,16.6694215 C15.9894459,13.2024793 13.6675462,10.2561983 10.4717678,9.23966942 C12.0084433,8.39256198 13.0511873,6.78099174 13.0511873,4.9338843 C13.0511873,2.21487603 10.7883905,-1.7616762e-15 8.01055409,-1.7616762e-15 C5.23271768,-1.7616762e-15 2.96992084,2.21487603 2.96992084,4.9338843 C2.96992084,6.78099174 4.01266491,8.39256198 5.54934037,9.23966942 C2.34934037,10.2561983 0.0316622691,13.2024793 0.0316622691,16.6694215 L0.0316622691,19.4710744 C0.0316622691,19.7520661 0.263852243,19.9793388 0.550923483,19.9793388 L15.478628,19.9793388 C15.7572559,19.9752066 15.9894459,19.7520661 15.9894459,19.4710744 Z M4,4.9338843 C4,2.77272727 5.79841689,1.01239669 8.00633245,1.01239669 C10.214248,1.01239669 12.0126649,2.77272727 12.0126649,4.9338843 C12.0126649,7.09504132 10.214248,8.8553719 8.00633245,8.8553719 C5.79841689,8.8553719 4,7.09504132 4,4.9338843 Z M14.9551451,18.9628099 L1.05751979,18.9628099 L1.05751979,16.6652893 C1.05751979,12.9173554 4.17308707,9.86363636 8.00633245,9.86363636 C11.8395778,9.86363636 14.9551451,12.9132231 14.9551451,16.6652893 L14.9551451,18.9628099 Z"
                    id="Shape"
                  ></path>
                </svg>
              </a>
            </li>
            <li class="menu-bascet panel-buttons__basket shop-cart-container">
              <a
                @click="enableElement('cart')"
                class="header-page__basket-mobile panel-buttons__basket-link"
                href="javascript:void(0)"
              >
                <svg viewBox="0 0 1489.733 1698.268" width="62" height="62">
                  <path
                    d="M1489.668,1540.226l-50.734-1145.759c-0.896-84.585-70.35-153.199-155.591-153.199h-257.892   C1004.523,106.268,886.593,0,744.689,0C602.747,0,484.784,106.268,463.85,241.268H206.313   c-85.217,0-154.649,68.616-155.543,153.202L0.064,1540.188C0.022,1541.16,0,1542.146,0,1543.121   c0,85.543,69.797,155.146,155.592,155.146h1178.556c85.79,0,155.586-69.583,155.586-155.127C1489.733,1542.166,1489.712,1541.2,1489.668,1540.226z M744.689,132.141c68.746,0,126.941,46.126,145.617,109.126H598.998   C617.684,178.268,675.908,132.141,744.689,132.141z M1334.147,1566.268H155.592c-12.811,0-22.917-9.645-23.43-22.062   l50.674-1145.048c0.043-0.971,0.064-2.111,0.064-3.084c0-12.695,10.283-22.806,23.412-22.806H460v241.459   c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268h304v241.459c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268   h255.343c13.153,0,23.457,10.095,23.457,22.79c0,0.974,0.021,2.023,0.064,2.998l50.706,1145.117   C1357.057,1556.586,1346.953,1566.268,1334.147,1566.268z"
                  ></path>
                </svg>
                <i v-if="cartCount > 0" class="itemCartCountM">{{
                  cartCount
                }}</i>
              </a>
            </li>
          </ul>
          <ul class="header-page__bottom-line">
            <li
              v-for="(m, i) in menu"
              :key="`menu-item-${i}`"
              @mouseover="menuHandler({ i, status: true })"
              @mouseleave="menuHandler({ i, status: false })"
              :class="[{ active: m.active }]"
            >
              <a :href="m.url">{{ m.title }}</a>
              <ul v-if="m.children.length > 0">
                <li
                  v-for="(c, childIndex) in m.children"
                  :key="`menu-item-${i}-child-${childIndex}`"
                >
                  <a :href="c.url">{{ c.title }}</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        <a
          href="javascript:void(0)"
          class="header-page__basket panel-buttons__basket-link cursorred"
          @click="enableElement('cart')"
        >
          <svg viewBox="0 0 1489.733 1698.268" width="62" height="62">
            <path
              d="M1489.668,1540.226l-50.734-1145.759c-0.896-84.585-70.35-153.199-155.591-153.199h-257.892   C1004.523,106.268,886.593,0,744.689,0C602.747,0,484.784,106.268,463.85,241.268H206.313   c-85.217,0-154.649,68.616-155.543,153.202L0.064,1540.188C0.022,1541.16,0,1542.146,0,1543.121   c0,85.543,69.797,155.146,155.592,155.146h1178.556c85.79,0,155.586-69.583,155.586-155.127C1489.733,1542.166,1489.712,1541.2,1489.668,1540.226z M744.689,132.141c68.746,0,126.941,46.126,145.617,109.126H598.998   C617.684,178.268,675.908,132.141,744.689,132.141z M1334.147,1566.268H155.592c-12.811,0-22.917-9.645-23.43-22.062   l50.674-1145.048c0.043-0.971,0.064-2.111,0.064-3.084c0-12.695,10.283-22.806,23.412-22.806H460v241.459   c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268h304v241.459c0,36.49,29.51,66.07,66,66.07s66-29.58,66-66.07V373.268   h255.343c13.153,0,23.457,10.095,23.457,22.79c0,0.974,0.021,2.023,0.064,2.998l50.706,1145.117   C1357.057,1556.586,1346.953,1566.268,1334.147,1566.268z"
            ></path>
          </svg>
          <i v-if="cartCount" style="color: white;" class="itemCartCountD">{{
            cartCount
          }}</i>
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

      <input
        type="hidden"
        id="melle_reload_cart"
        @click="updateCartDataRequest()"
      />
      <input type="hidden" id="melle_clear_cart" @click="clearCartRequest()" />
    </header>
  </fixed-header>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import FixedHeader from 'vue-fixed-header'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.min.css'
import algoliasearch from 'algoliasearch/lite'
import {
  AisInstantSearch,
  AisAutocomplete,
  AisHighlight,
  AisPoweredBy,
} from 'vue-instantsearch'

import Sidebar from './Sidebar.vue'
import Cart from './Cart.vue'
import Login from './Login.vue'
import Register from './Register.vue'
import MailUs from './MailUs.vue'
import Forgotten from './Forgotten.vue'
import Filter from './Filter.vue'

export default {
  components: {
    AisInstantSearch,
    AisAutocomplete,
    AisHighlight,
    AisPoweredBy,
    FixedHeader,
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
      'product_link_placeholder',
      'pro_algolia',
    ]),
    ...mapState('cart', {
      cartCount: 'count',
    }),
    ...mapGetters('header', ['isElementActive', 'phoneLink', 'accountLink']),

    searchIndex() {
      if (!this.pro_algolia) {
        return
      }
      return this.pro_algolia.indexName
    },
    searchClient() {
      if (!this.pro_algolia) {
        return
      }

      return algoliasearch(
        this.pro_algolia.appId,
        this.pro_algolia.searchApiKey
      )
    },
  },
  methods: {
    ...mapActions('header', ['menuHandler', 'enableElement']),
    ...mapActions('cart', ['updateCartDataRequest', 'clearCartRequest']),

    searchAction(searchQuery) {
      let url = this.base + 'index.php?route=product/search'
      url += '&query=' + encodeURIComponent(searchQuery)
      location = url
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

    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-header-content')
    // console.log(prerender)
    if (prerender) {
      prerender.remove()
    }
  },
}
</script>

<style lang="scss" scoped="true">
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
.st0 {
  fill: #ffffff;
}
</style>
