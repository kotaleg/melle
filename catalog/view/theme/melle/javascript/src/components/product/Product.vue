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
                    optionId: o.option_id,
                    productOptionId: o.product_option_id,
                    optionValueId: ov.option_value_id,
                    productOptionValueId: ov.product_option_value_id,
                    selected: !ov.selected,
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
                  :title="ov.name"
                />
              </span>
            </div>
          </label>

          <a
            v-if="sizeList && o.class == 'size'"
            id="size-list"
            data-fancybox=""
            class="d-none d-sm-block"
            :href="sizeList"
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
          v-if="inStock"
          v-show="quantity >= maxQuantity"
          class="product-max-count"
        >
          доступно:
          <span>{{ maxQuantity }}</span>
        </span>
      </div>
    </div>

    <div
      v-if="inStock"
      class="align-items-center d-flex justify-content-start my-3"
    >
      <div>
        <span v-if="isSpecial" class="price-old"
          >{{ price }} <span class="ruble-sign">Р</span></span
        >
        <span v-if="isSpecial" class="ml-3 price"
          >{{ special }}
          <span v-if="star" class="ruble-container">
            <span class="ruble-sign">Р</span
            ><span class="ruble-zvezdochka">*</span>
          </span>
          <span v-else class="ruble-sign">Р</span>
        </span>

        <span v-if="star && specialText" class="ml-3 special-text-info">{{
          specialText
        }}</span>

        <span v-if="!isSpecial" class="price"
          >{{ price }} <span class="ruble-sign">Р</span></span
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

    <div v-if="inStock" class="d-flex flex-row justify-content-between">
      <a
        id="add_trigger_button"
        v-if="countInCart <= 0"
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
            {{ countInCart }}
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

    <div v-else class="d-flex flex-row justify-content-between mt-3">
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
import { isEqual } from 'lodash'
import { mapActions } from 'vuex'
import { get, sync } from 'vuex-pathify'

import OneClickModal from '@/components/modal/OneClickModal.vue'

export default {
  components: {
    'one-click-modal': OneClickModal,
  },
  computed: {
    ...get('product', [
      'productId',
      'name',
      'images',
      'quantity',
      'options',
      'reviewCount',
      'ratingValue',
      'ratingArray',
      'sizeList',
    ]),

    inStock: get('product/stock@inStock'),
    getSpecial: get('product/stock@special'),
    isSpecial: get('product/stock@isSpecial'),
    specialText: get('product/stock@specialText'),
    star: get('product/stock@star'),
    price: sync('product/stock@price'),
    special: sync('product/stock@special'),
    maxQuantity: get('product/stock@maxQuantity'),
    imageHash: get('product/stock@imageHash'),
    optionsForCart: get('product/stock@optionsForCart'),
    checkoutLink: get('cart/checkout_link'),
    cartProducts: get('cart/products'),

    countInCart() {
      for (const i in this.cartProducts) {
        const cartProduct = this.cartProducts[i]
        const optionsForCompare = cartProduct.optionsForCompare
        if (isEqual(this.optionsForCart, optionsForCompare)) {
          return cartProduct.quantity
        }
      }
      return 0
    },

    currentImageHash() {
      const isDesktop = window.matchMedia('(min-width: 992px)').matches

      if (isDesktop) {
        const newImagePreview = document.getElementById(`product-preview-${this.imageHash}`)
        if (newImagePreview) {
          newImagePreview.click()
        }
      } else {
        const newImage = document.getElementById(`product-image-${this.imageHash}`)
        if (newImage && !newImage.classList.contains('d-block')) {
          const allImages = document.querySelectorAll('li.prod-card__item-big-photo')
          for (const i of allImages) {
            if (i.classList.contains('d-block')) {
              i.classList.remove('d-block')
            }
          }
          newImage.classList.add('d-block')
          const imgTags = newImage.querySelectorAll(':scope img')
          for (const imgTag of imgTags) {
            if (!imgTag.src) {
              imgTag.src = imgTag.dataset.lazy
            }
          }
        }
      }

      return this.imageHash
    },
  },
  methods: {
    ...mapActions('product', [
      'quantityHandler',
      'radioHandler',
      'addToCartRequest',
      'getProductStockRequest',
    ]),
    ...mapActions('header', ['enableElement']),

    radioHandler(data) {
      if (!this.inStock) {
        return
      }
      this.getProductStockRequest({
        productId: this.productId,
        options: this.options,
        ...data
      })
    },
    addToCart() {
      this.addToCartRequest({
        product_id: this.productId,
        quantity: this.quantity,
        options: this.optionsForCart,
      })
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
    this.$store.dispatch('product/INIT_DATA')
  },
  mounted() {
    if (this.productId) {
      this.getProductStockRequest({
        productId: this.productId,
      })
    }

    // REMOVE PRERENDERED CONTENT
    let prerender = document.getElementById('rendered-product-content')
    // console.log(prerender)
    if (prerender) {
      prerender.remove()
    }
  },
}
</script>
