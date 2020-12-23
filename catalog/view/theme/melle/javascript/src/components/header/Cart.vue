<template>
  <section class="sidebar-inner">
    <sidebar-buttons />

    <h4 class="title">Ваша корзина</h4>
    <div id="cart-controller">
      <div v-if="hasProducts" class="d-flex flex-column cart-items">
        <div
          v-for="(p, i) in products"
          :key="`cart-product-${i}`"
          class="cart-item d-flex mb-5 mt-4"
        >
          <div class="d-none d-sm-block">
            <a :href="p.href">
              <img :src="p.thumb" class="img-fluid" />
            </a>
          </div>
          <div class="d-flex flex-column w-50">
            <div class="mb-3">
              <div class="title mb-2">
                <a :href="p.href">{{ p.name }}</a>
              </div>
              <div
                v-for="(o, option_i) in p.option"
                :key="`cart-option-${option_i}`"
                class="cart-option mb-1"
              >
                <span>{{ o.name }}: </span><span>{{ o.value }}</span>
              </div>
            </div>
            <div class="d-flex cart-quantity-container">
              <button role="button" @click="quantityHandler(i, '-')">
                <span>-</span>
              </button>
              <input type="text" :value="p.quantity" readonly />
              <button role="button" @click="quantityHandler(i, '+')">
                <span>+</span>
              </button>
              <span v-show="p.quantity >= p.max_quantity" class="cart-max-count"
                >доступно:&nbsp;<span>{{ p.max_quantity }}</span></span
              >
            </div>
          </div>
          <div
            class="align-items-end d-flex flex-column justify-content-between"
          >
            <button @click="removeCartItemRequest(p.cart_id)" class="btn">
              <svg
                viewBox="0 0 191.414 191.414"
                width="21"
                height="21"
                fill="#918d8a"
              >
                <path
                  d="M107.888,96.142l80.916-80.916c3.48-3.48,3.48-8.701,0-12.181s-8.701-3.48-12.181,0L95.707,83.961L14.791,3.045   c-3.48-3.48-8.701-3.48-12.181,0s-3.48,8.701,0,12.181l80.915,80.916L2.61,177.057c-3.48,3.48-3.48,8.701,0,12.181   c1.74,1.74,5.22,1.74,6.96,1.74s5.22,0,5.22-1.74l80.916-80.916l80.916,80.916c1.74,1.74,5.22,1.74,6.96,1.74   c1.74,0,5.22,0,5.22-1.74c3.48-3.48,3.48-8.701,0-12.181L107.888,96.142z"
                ></path>
              </svg>
            </button>
            <div class="cart-price">
              <span>{{ p.price }} <span class="ruble-sign">Р</span></span>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="hasProducts"
        class="align-items-center cart-totals d-flex flex-column p-4 mb-4"
      >
        <div
          v-for="(total, i) in totals"
          :key="`cart-total-${i}`"
          class="cart-total text-center"
        >
          <span v-html="total.title"></span>:
          <span>{{ total.text }} <span class="ruble-sign">Р</span></span>
        </div>
        <div class="cart-total text-center">
          <span>ИТОГО: </span
          ><span class="boldPrice"
            >{{ total }} <span class="ruble-sign">Р</span></span
          >
        </div>
        <div class="mt-2">
          <a :href="checkout_link" class="btn btn-primary">Оформить заказ</a>
        </div>
      </div>
      <div
        v-else
        class="align-items-center cart-totals d-flex flex-column p-4 mb-4"
      >
        <h4 class="cart-empty text-dark">
          корзина пуста
        </h4>
      </div>

      <div
        v-if="hasProducts"
        class="align-items-center d-flex flex-column flex-sm-row justify-content-between"
      >
        <a
          :href="catalog_link"
          class="align-items-baseline cart-button d-flex flex-row m-sm-0 mb-4"
        >
          <svg viewBox="0 0 489.2 489.2" width="20" height="20" class="mr-2">
            <path
              d="M481.044,382.5c0-6.8-5.5-12.3-12.3-12.3h-418.7l73.6-73.6c4.8-4.8,4.8-12.5,0-17.3c-4.8-4.8-12.5-4.8-17.3,0l-94.5,94.5c-4.8,4.8-4.8,12.5,0,17.3l94.5,94.5c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6c4.8-4.8,4.8-12.5,0-17.3l-73.6-73.6h418.8C475.544,394.7,481.044,389.3,481.044,382.5z"
            ></path>
          </svg>
          <span>Продолжить покупки</span>
        </a>
        <a
          href="javascript:void(0)"
          class="align-items-center cart-button d-flex flex-row"
          @click="clearCartRequest()"
        >
          <span>Очистить корзину</span>
          <svg
            viewBox="0 0 191.414 191.414"
            width="12"
            height="15"
            class="ml-2"
          >
            <path
              d="M107.888,96.142l80.916-80.916c3.48-3.48,3.48-8.701,0-12.181s-8.701-3.48-12.181,0L95.707,83.961L14.791,3.045   c-3.48-3.48-8.701-3.48-12.181,0s-3.48,8.701,0,12.181l80.915,80.916L2.61,177.057c-3.48,3.48-3.48,8.701,0,12.181   c1.74,1.74,5.22,1.74,6.96,1.74s5.22,0,5.22-1.74l80.916-80.916l80.916,80.916c1.74,1.74,5.22,1.74,6.96,1.74   c1.74,0,5.22,0,5.22-1.74c3.48-3.48,3.48-8.701,0-12.181L107.888,96.142z"
            ></path>
          </svg>
        </a>
      </div>

      <div v-else class="">
        <a
          :href="catalog_link"
          class="align-items-center cart-button d-flex flex-row"
          >Начать покупки</a
        >
      </div>
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
    ...mapState('cart', [
      'count',
      'products',
      'total',
      'totals',
      'catalog_link',
      'checkout_link',
    ]),
    ...mapState('header', ['phone']),
    ...mapGetters('cart', ['hasProducts']),
  },
  methods: {
    ...mapActions('header', ['enableElement']),
    ...mapActions('cart', [
      'clearCartRequest',
      'updateCartItemRequest',
      'removeCartItemRequest',
    ]),

    quantityHandler(key, type) {
      let cart_id = this.products[key].cart_id
      let quantity = this.products[key].quantity

      switch (type) {
        case '+':
          quantity += 1
          break
        case '-':
          quantity -= 1
          break
      }

      if (quantity <= this.products[key].max_quantity) {
        this.updateCartItemRequest({ cart_id, quantity })
      }
    },
  },
  mounted() {
    // GTM
    this.$store.dispatch('gtm/openCheckoutPage')
  },
}
</script>
