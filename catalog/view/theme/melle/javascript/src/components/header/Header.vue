<template>
  <header class="d-flex flex-row align-items-stretch justify-content-between">
    <notifications
      :group="this.$codename + '_header'"
      position="bottom right"
    />
    <loading :active.sync="is_loading" :is-full-page="true" />
    <product-preview-modal dir="ltr" :width="750" :scrollable="false" />

    <div
      class="d-flex flex-column justify-content-around w-100 header-container"
    >
      <div
        class="
          d-flex
          align-items-center
          justify-content-between
          py-2
          container-fluid
          text-white
          header-top
        "
      >
       <div class="logo-melle">
          <a :href="base" class="logo">
          <img src="https://melle.online/image/catalog/logo-header.svg" alt="MELLE" class="img-fluid" />
        </a>
        </div>

        <a id="menu-trigger" href="#mobile-menu" class="d-lg-none">
          <img
            src="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAiCAYAAAAd6YoqAAAAlElEQVRYhe2ZsQ2EMBAEB4SIESHx5yTfAA1QCwVRiIsgoIwP6cAv12CC9Wmngp1g5dO6yznvwEzbpCJyA2vjIlsvEOIVLKJGGJFS9gUYBbLU8CsiCkGqcUfUsIgaFlFjAE7g07jHUUS+Aa7fyR1RI1TZL+ARyFLD46NRDYuoYRE1Qr0jYcYHT6ZKWESNUJNp+x89kP521SBAVqopqwAAAABJRU5ErkJggg=="
            alt="Меню"
            class="img-f"
          />
        </a>

        <div class="search d-none d-lg-block">

          <div class="ais-InstantSearch w-100">
    <div class="melle-header-autocomplete">
        <div class="ais-Autocomplete">
            <div>
                <div class="d-flex align-items-stretch justify-content-start search-bar-wrapper">
                    <form action="https://melle.online/index.php?route=product/search" class="d-flex align-items-stretch justify-content-start w-100 h-100 search-bar-form">
                        <div class="d-flex h-100 searchbooster search-input-wrapper">

            <div class="test-class"></div>

                            <input type="hidden" name="route" value="product/search">
                            <input type="text" name="query" autocapitalize="off" autocomplete="off" autocorrect="off" spellcheck="false" maxlength="255" placeholder="Поиск">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


        </div>


        <a
          href="https://melle.online/dostavka-i-oplata"
          class="d-none d-lg-block"
          >Доставка</a
        >
        <a
          href="https://melle.online/opt/"
          target="_blank"
          class="d-none d-lg-block"
          >Оптовый сайт</a
        >
        <a :href="phoneLink" class="d-none d-lg-block">{{ phone }}</a>

        <div class="login d-none d-lg-block">
          <div v-if="!is_logged">
            <a @click="enableElement('login')" href="javascript:void(0)"
              >Вход</a
            >
            \
            <a @click="enableElement('register')" href="javascript:void(0)"
              >Регистрация</a
            >
          </div>
          <div v-else>
            <a :href="account_link">Кабинет</a>
            \
            <a :href="logout_link">Выход</a>
          </div>
        </div>
      </div>

      <div
        class="d-flex flex-row justify-content-between text-white header-bottom"
      >
        <div
          class="
            d-flex
            flex-row
            justify-content-between
            align-items-center
            container-fluid
            py-2
            header-bottom-container
          "
        >
          <div class="search search-mobile d-lg-none">
            <search />
          </div>

          <a :href="phoneLink" class="d-none d-sm-block d-lg-none">{{
            phone
          }}</a>

          <div class="login mobile-login d-flex d-lg-none ml-2">
            <img
              width="25"
              class="img-fluid mr-1"
              src="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAACIAAAAhCAYAAAC803lsAAAD9klEQVRYha2YW4hWVRTH/6Nfo2GTNmZWRJGXirIyh65UM12eJghHqF5q6EmDEH0qSqQLBPbSlSgpbMAgu4FSlBVokNDlpVDDLjaIZaljt1HL5uIvtvwP7c6cs8/5xhYs9vnO+q//Xmdf1l77awE0TjlV0ixJJ0kKJAcl9Uv6dTx0jSawkyTdKmmRpBskzSzB7ZG0WdLrkjZKGq5DXmdEJktaJmm5pNOj9z9L+l7SL/49Q9IcSadFmB8kPS5ptaSRZC8hkIReC3zHv/IpcA9wdsJnDrAM2Br5bQPmp/pKBRHIRkz0BdCVs08ErnNgi4ErgAk5TDfwtTmOAL3NBvKonUeBh91pbL8J2MlYCV9+dQ47CXgqQi6tG8i9dhgCbi+w9zjAINuBJ4Cno8D+Lhi9oEsivzuqArncARSCgXbgd9tX5qaiATxp24/AiQX+S20/BJxfFkgg2m7gqpIpW277hhJ72IVbjLmzBPOS7R8bPyaQsOiC7ABaS0heM8mixCJfYsxzJfY2YE+eZ4J3cWjv8/MDkoZKdnuWI3YnMsI+t1NK7CEDr/Tzg9nLLJBOSedK+kbShkQnLW7TyalaXnHAHZIujgPpcbvW50aZHPL79gRmco1gw4iv8/PCOJAut+9XfMsOtzcmMJ1ud1ZwbfxP305WQ9ZGRcrv8CI7CMwrsF9jnpCRZ1VwtZtrX7ZrzvKLbyscM33R+D+A+4FLgAXAI8Cftj1Wk2vQ+Nbw44LoPKnjHLZ2X0F6z+SZgiOhTHfZZ1ojqhdaK+Y0k7DQ7pb0sqReSRdJOirpS0l9kj6vyaNoYR+rR0IdsV/SXklnNEHyf8iwN0wjjMiApEEXPadI+q1GByc478x24sJbO+yUXZJGa3DMdYXYf8zfc/eR56o7MZ8nu+54DzicWCNhEb8N3AVMSfD1Gv9GfNas8MvVJQGs8omZyahLgPVeuH3uPBRBRyPcAZ/SRSfxW8YsjgOZ65eDua+4BfgpIn4XuA2YmvjS6R6NTZFfP9AZYWYCw9YZ+dP3Qzut8O+HIqJAelnNLRnrVcBn5hhx+amoYltXVAZcb2OYgjV+HnYx0zKOIDKd6OnJpuxZ847G2Tnv9Go0CiFL3nwcAeS1J6r+cOJTWSAh/+82cP1xjkTRyGw291f5HVXk0BGdAWsS1VozGjp905wDwHl53zKyLueDIJ/kC90mdX502Rrwh47hSJFeGt3yjniln9lEEOcAL0SXtG2+BRbiq8jaXARn95FA+o7vPguc7DLsNOBKV/ofRLtkyAmxKKnVDiTTC4G1wF/Rqs8kzqSxhDTwPDC7Th/N/j8yVVK3S8V5PvjaXAZkh95WSZtcCh6uxSrpH4b65ELPcnlyAAAAAElFTkSuQmCC"
              alt="Аккаунт"
            />
            <a
              v-if="!is_logged"
              @click="enableElement('login')"
              href="javascript:void(0)"
              >Вход</a
            >
            <a v-else :href="account_link">Кабинет</a>
          </div>

          <div
            v-for="(m, i) in menu"
            :key="`menu-item-${i}`"
            @mouseover="menuHandler({ i, status: true })"
            @mouseleave="menuHandler({ i, status: false })"
            :class="['d-none d-lg-block menu-item', { active: m.active }]"
          >
            <a :href="m.url">
              {{ m.title }}
            </a>
            <div v-if="m.children.length > 0" class="menu-children p-4 row">
              <div
                v-for="(c, childIndex) in m.children"
                :key="`menu-item-${i}-child-${childIndex}`"
                class="col-md-6 pb-2"
              >
                <a :href="c.url">{{ c.title }}</a>
              </div>
            </div>
          </div>
        </div>

        <div
          @click="enableElement('cart')"
          class="
            d-flex d-lg-none
            align-items-center
            text-center
            px-3
            cart-container
          "
        >
          <div>Корзина</div>
        </div>
      </div>
    </div>

    <div
      @click="enableElement('cart')"
      class="d-none d-lg-block text-center cart-container"
    >
      <img
        src="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAuCAYAAABEbmvDAAADWElEQVRYhc1YTWsUQRB9GVdNx24VDxo/0EVF9BAUUS9KrupBDyIKovkFiuBRELwJgpKDoDe9iF5EAjFHQVE0iEZBFAQFBT9iUJN0ZztmN0Z60iOVcpKdWXZ35sHAdm9X16OrurqqWlAjpFArAJwAsBfAdgDL/E5DAF4A6ANw01j9oxYNqYlJoVoBnAdwGkBrleVjAC4CuGCsLjeMmBRqDYBeAFvTyAHoB3DAWD1Ud2JSqHYAjwBsYH/98Yo/AAgAbASwI2bv1wA6jdW/kupMQqpFCnVfCjVFvkkpVLf3Nb5+tRTqGlvvvp66kfKKupgCK4Xal0DusBSqzGQP1pPYW7Z5VwrZU0z2SRK5qj4mhXKO/pJMPTZW70lBLPDyHWS6aKz+OJdckGBvTuJ6UlIOxmp3OW6w6c5qckmIrWfjRKZgeMrGxXoQW8LG32sgNsjGS6sJFDDtB2vJk8KxnI23SKF0SmKr2LhdCrVtlrU/jdWfXHy6BOBMSkWNxmVHrBydXI5QCXJIyqHgSBnnZn7iPYArbNExADvJ+CwAm1KR899zZPwQwF225iR5h7UjNk6IfTFWd9PV3kkpsavG6uE0rKRQRUZsIEbPEUKsFPicKcLiNArrjDaynXXE6NVva6jquUHj5UjA/EVkSIzqDk1ZIhMqA0IRqLVCYqNkYlE2nELQQzGBv5URClKoec1mJIXiB6K5KRFjzt9sPFmDbi7DKyZ+6f4zJUhMi9BHfvcbq9M+4A6fAbzxv6cA3GP/8xOzhZgoPqNWNFb3SKF2+byMb5gILlmUQu0GsN8RNFa/YnI8fg7HEeMn5jZ+BsB9NcO/FrdmkedhKgywI9WINQGxPjbOJquV/Y0Az5I1j/x5OTETZ8oson9uTcmtVIozZRbPErdSmF3wpC8PprR5MSWPY7E+loUpeQE8GmfKLNJrfmJjeTEl9bGK69cGxmpOLIu8n164sDiKmio0yGZhShrHwjQsIkZjWRYnNiPfByFG20Qdvm3eFEihXJG7iej6BtK3eEB69479cylUL6s5GwGXVbhmMa0zXPtgugcrhVrnU98sC1740LXZWD0YmtI3ao/GFCbNhHP6Q46U0/nvCCcqE+8WzF94x9/KlU18Ab4CuA3guLF6IJwB8Bc0RPKFRPDOzAAAAABJRU5ErkJggg=="
        class="img-fluid mt-3 mb-2"
        alt="Корзина"
      />
      <b>Корзина</b>
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
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import Loading from 'vue-loading-overlay'
import Search from '@/components/header/Search.vue'
import Sidebar from '@/components/header/Sidebar.vue'
import Cart from '@/components/header/Cart.vue'
import Login from '@/components/header/Login.vue'
import Register from '@/components/header/Register.vue'
import Forgotten from '@/components/header/Forgotten.vue'
import Filter from '@/components/header/Filter.vue'
import ProductPreviewModal from '@/components/modal/ProductPreviewModal.vue'

export default {
  components: {
    Loading,
    Search,
    ProductPreviewModal,
    'header-sidebar': Sidebar,
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
  },
  methods: {
    ...mapActions('header', ['menuHandler', 'enableElement']),
    ...mapActions('cart', ['updateCartDataRequest', 'clearCartRequest']),

    accountAction() {
      if (!this.is_logged) {
        this.enableElement('login')
      } else {
        window.location = this.account_link
      }
    },
  },
  created() {
    this.$store.dispatch('header/initData')
    this.$store.dispatch('cart/initData')
  },
  mounted() {
    if (typeof jQuery == 'function') {
      jQuery('.melle_reload_cart').on('click', () => {
        this.updateCartDataRequest()
      })
      jQuery('.melle_clear_cart').on('click', () => {
        this.clearCartRequest()
      })
    }

    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-header-content')
    if (prerender) {
      prerender.remove()
    }
  },
}
// searchbooster script

!function(e,t,n,c,o){e[o]=e[o]||function(){(e[o].a=e[o].a||[]).push(arguments)},e[o].h=c,e[o].n=o,e[o].i=1*new Date,s=t.createElement(n),a=t.getElementsByTagName(n)[0],s.async=1,s.src=c,a.parentNode.insertBefore(s,a)}(window,document,"script","https://cdn2.searchbooster.net/scripts/v2/init.js","searchbooster"),searchbooster({"apiKey":"","apiUrl":"https://api4.searchbooster.io","scriptUrl":"https://cdn2.searchbooster.net/scripts/v2/init.js","offer":{"cart":true},"initialized":(sb)=>{sb.mount({"selector":".searchbooster","widget":"search-popup","options":{"offer":{"cart":true}}});}})
</script>
