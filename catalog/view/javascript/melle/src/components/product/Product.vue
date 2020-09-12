<template>
  <form class="form-vertical" method="post">
    <div v-for="(o, o_key) in options" :key="`option-${o_key}`" class="mb-3">
      <div class="align-items-center d-flex justify-content-start">
        <span class="option-selector">{{ o.name }}:</span>
        <div class="d-flex flex-row flex-wrap justify-content-start">
          <label
            v-for="(ov, ov_key) in o.product_option_value"
            :key="`option-value-${ov_key}`"
            :class="[
              'radio-inline text-center my-1 mr-2',
              `${o.class}-radio`,
              { check: ov.selected, disabled: ov.disabled_by_selection },
            ]"
          >
            <div
              :class="[
                'd-flex flex-column justify-content-center',
                { krestik: ov.disabled_by_selection },
              ]"
            >
              <input
                type="radio"
                @click="
                  radioHandler({
                    o_key: o_key,
                    ov_key: ov_key,
                    status: !ov.selected,
                  })
                "
                :name="[`ShopCartItem[${o.class}]`]"
                :placeholder="ov.name"
                :value="ov.option_value_id"
                :class="[
                  { check: ov.selected, disabled: ov.disabled_by_selection },
                ]"
              />

              <span v-if="!ov.image">{{ ov.name }}</span>
              <span v-if="ov.image">
                <img
                  :src="ov.image"
                  :class="[`${o.class}-image`]"
                  v-tooltip="{ content: ov.name }"
                />
              </span>
            </div>
          </label>

          <a
            v-if="size_list && o.class == 'size'"
            id="size-list"
            data-fancybox=""
            class="d-none d-sm-block"
            :href="size_list"
            >таблица<br />размеров</a
          >
        </div>
      </div>
    </div>

    <div class="align-items-center d-flex justify-content-start">
      <span class="option-selector">кол-во:</span>
      <div class="align-items-center d-flex quantity-container">
        <button
          @click="quantityHandler('-')"
          type="button"
          class="p-0 px-2 text-center"
        >
          -
        </button>
        <input
          id="productCounter"
          class="p-0 text-center"
          v-model.number="quantity"
        />
        <button
          @click="quantityHandler('+')"
          type="button"
          class="p-0 px-2 text-center"
        >
          +
        </button>

        <span
          v-if="in_stock"
          v-show="quantity >= getActiveMaxQuantity"
          class="product-max-count"
        >
          доступно:
          <span>{{ getActiveMaxQuantity }}</span>
        </span>
      </div>
    </div>

    <div
      v-if="in_stock"
      class="align-items-center d-flex justify-content-start my-3"
    >
      <div>
        <span v-if="isSpecial" class="price-old"
          >{{ getActivePrice }} <span class="ruble-sign">Р</span></span
        >
        <span v-if="isSpecial" class="ml-3 price"
          >{{ getSpecial }}
          <span v-if="zvezdochka" class="ruble-container">
            <span class="ruble-sign">Р</span
            ><span class="ruble-zvezdochka">*</span>
          </span>
          <span v-else class="ruble-sign">Р</span>
        </span>

        <span v-if="zvezdochka" class="ml-3 special-text-info">{{
          special_text
        }}</span>

        <span v-if="!isSpecial" class="price"
          >{{ getActivePrice }} <span class="ruble-sign">Р</span></span
        >
      </div>
      <div
        class="rating"
        itemprop="aggregateRating"
        itemtype="http://schema.org/AggregateRating"
        itemscope
      >
        <meta itemprop="reviewCount" :content="reviewCount" />
        <meta itemprop="ratingValue" :content="ratingValue" />
      </div>
    </div>

    <div v-if="in_stock" class="d-flex flex-row justify-content-between">
      <a
        id="add_trigger_button"
        v-if="getProductCountForCurrentSelectedOptions <= 0"
        @click="addToCart()"
        href="javascript:void(0);"
        class="add-button p-2 p-sm-3 w-100 text-center"
        ><span
          >Добавить <br />
          В корзину</span
        ></a
      >

      <div v-else class="d-flex add-button w-100">
        <div
          @click="goToCheckout()"
          class="count p-2 p-sm-3 d-flex text-center"
        >
          <span class="m-auto"
            >в корзине
            {{ getProductCountForCurrentSelectedOptions }}
            шт.<br />перейти</span
          >
        </div>
        <div @click="addToCart()" class="plus p-2 p-sm-3 d-flex text-center">
          <span class="m-auto">+1 шт.</span>
        </div>
      </div>

      <a
        @click="buyOneClick()"
        href="javascript:void(0);"
        class="d-flex ml-3 ml-sm-5 one-click-button p-2 p-sm-3 w-100"
        ><span class="m-auto"
          >Купить <br />
          в 1 клик</span
        ></a
      >
    </div>

    <div v-else class="d-flex flex-row justify-content-between">
      <a
        @click="notifyInStock()"
        href="javascript:void(0);"
        class="d-flex one-click-button p-2 p-sm-3 text-center w-50 not-in-stock"
        ><span class="m-auto">Сообщить о поступлении</span></a
      >
    </div>

    <input type="hidden" id="active-image-hash" :value="currentImageHash" />

    <one-click-modal dir="ltr" :width="500" :scrollable="false" />
  </form>
</template>

<script>
import { forEach, isEqual } from 'lodash'
import { mapState, mapActions, mapGetters } from 'vuex'

import OneClickModal from './../modal/OneClickModal.vue'

export default {
  components: {
    'one-click-modal': OneClickModal,
  },
  computed: {
    ...mapGetters('product', [
      'getRating',
      'isSpecial',
      'getSpecial',
      'getActiveMaxQuantity',
      'getFormValue',
      'getStateValue',
      'getActivePrice',
      'getActiveImageHash',
      'getOptionsForCart',
    ]),
    ...mapState('cart', {
      checkoutLink: 'checkout_link',
      cartProducts: 'products',
    }),
    ...mapState('product', [
      'product_id',
      'options',
      'size_list',
      'zvezdochka',
      'special_text',
      'reviewCount',
      'ratingValue',
      'in_stock',
    ]),

    getProductCountForCurrentSelectedOptions() {
      const optionsForCart = this.getOptionsForCart

      for (const i in this.cartProducts) {
        const cartProduct = this.cartProducts[i]
        const optionsForCompare = cartProduct.optionsForCompare

        if (isEqual(optionsForCart, optionsForCompare)) {
          return cartProduct.quantity
        }
      }

      return 0
    },

    currentImageHash() {
      const currentImages = document.querySelectorAll(
        "li[data-hash='" + this.getActiveImageHash + "']"
      )

      let clickCount = 0
      if (currentImages.length === 0) {
        const defaultImages = document.querySelectorAll(
          "li[data-hash='default']"
        )
        forEach(defaultImages, (value) => {
          if (clickCount === 0) {
            value.click()
          }
        })
      } else {
        forEach(currentImages, (value) => {
          if (clickCount === 0) {
            value.click()
          }
        })
      }

      return this.getActiveImageHash
    },

    quantity: {
      get() {
        return this.getStateValue('quantity')
      },
      set(v) {
        this.updateQuantity(v)
      },
    },
  },
  methods: {
    ...mapActions('product', [
      'quantityHandler',
      'updateQuantity',
      'radioHandler',
      'addToCartRequest',
    ]),
    ...mapActions('header', ['enableElement']),

    addToCart() {
      this.addToCartRequest()
    },
    buyOneClick() {
      this.$modal.show('one-click-modal', { source: 'buy-one-click' })
    },
    notifyInStock() {
      this.$modal.show('one-click-modal', { source: 'notify-in-stock' })
    },
    goToCheckout() {
      window.location = this.checkoutLink
    },
  },
  created() {
    this.$store.dispatch('product/initData')
  },
  mounted() {
    this.$store.dispatch('product/selectFirstCombination')

    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-product-content')
    // console.log(prerender)
    if (prerender) {
      prerender.remove()
    }
  },
}
</script>
