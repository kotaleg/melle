<template>
  <modal
    name="product-preview-modal"
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
    @closed="$emit('closed', $event)"
  >
    <div id="product-page" class="dialog-content">
      <div class="close-button" @click="closeModal"></div>
      <div class="row">
        <div class="col-sm-6">
          <div class="align-items-center d-flex h-100">
            <a v-if="image" :href="productLink">
              <img :src="image" :alt="name" class="img-fluid">
            </a>
          </div>
        </div>
        <div class="col-sm-6">
          <div>
            <h1><a :href="productLink">{{name}}</a></h1>

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
                </div>
              </div>
            </div>

            <div class="align-items-center d-flex justify-content-start">
              <span class="option-selector">кол-во:</span>
              <div class="align-items-center d-flex quantity-container">
                <button
                  @click="quantityHandler('-')"
                  type="button"
                  class="p-0 px-2 text-center">
                  -
                </button>
                <input
                  class="p-0 text-center"
                  v-model.number="quantity"/>
                <button
                  @click="quantityHandler('+')"
                  type="button"
                  class="p-0 px-2 text-center">
                  +
                </button>

                <span
                  v-if="inStock"
                  v-show="quantity >= maxQuantity"
                  class="product-max-count">
                  <span>доступно:</span>
                  <span>{{ maxQuantity }}</span>
                </span>
              </div>
            </div>

            <div v-if="inStock" class="align-items-center d-flex justify-content-start my-3">
              <div>
                <span v-if="isSpecial" class="price-old">{{ price }} <span class="ruble-sign">Р</span></span>
                <span v-if="isSpecial" class="ml-3 price">
                  <span>{{ special }}</span>
                  <span v-if="star" class="ruble-container">
                    <span class="ruble-sign">Р</span
                    ><span class="ruble-zvezdochka">*</span>
                  </span>
                  <span v-else class="ruble-sign">Р</span>
                </span>
                <span v-if="!isSpecial" class="price">{{ price }} <span class="ruble-sign">Р</span></span>
                <div v-if="star && specialText" class="special-text-info">{{specialText}}</div>
              </div>
            </div>

            <div v-if="inStock" class="d-flex flex-row justify-content-between">
              <a
                v-if="countInCart <= 0"
                @click="addToCart()"
                href="javascript:void(0)"
                class="add-button p-2 p-sm-3 w-100 text-center">
                <span>Добавить<br />В корзину</span>
              </a>
              <div v-else class="d-flex add-button w-100">
                <div
                  @click="goToCheckout()"
                  class="count p-2 p-sm-3 d-flex text-center">
                  <span class="m-auto">в корзине
                    {{ countInCart }}
                    шт.<br />перейти</span>
                </div>
                <div @click="addToCart()" class="plus p-2 p-sm-3 d-flex text-center">
                  <span class="m-auto">+1 шт.</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </modal>
</template>

<script>
import {isEqual} from 'lodash'
import { mapState, mapActions } from 'vuex'
import { get, sync } from 'vuex-pathify'

export default {
  name: 'ProductPreviewModal',
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
    productId: get('product/productPreview@productId'),
    name: get('product/productPreview@name'),
    image: get('product/productPreview@image'),
    productLink: get('product/productPreview@productLink'),
    quantity: sync('product/productPreview@quantity'),
    options: get('product/productPreview@options'),

    inStock: get('product/productPreview@stock.inStock'),
    getSpecial: get('product/productPreview@stock.special'),
    isSpecial: get('product/productPreview@stock.isSpecial'),
    specialText: get('product/productPreview@stock.specialText'),
    star: get('product/productPreview@stock.star'),
    price: sync('product/productPreview@stock.price'),
    special: sync('product/productPreview@stock.special'),
    maxQuantity: get('product/productPreview@stock.maxQuantity'),
    optionsForCart: get('product/productPreview@stock.optionsForCart'),
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
  },
  methods: {
    ...mapActions('product', ['getProductPreviewDataRequest', 'getProductPreviewStockRequest', 'addToCartRequest', 'CLEAR_PREVIEW']),

    radioHandler(data) {
      this.getProductPreviewStockRequest({
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
    goToCheckout() {
      window.location = this.checkoutLink
    },

    quantityHandler(operation) {
      let q = parseInt(this.quantity)
      if (operation == '+') {
        q += 1
      } else if (operation == '-') {
        q -= 1
      }
      if (this.maxQuantity < q) {
        q = this.maxQuantity
      }
      if (q < 1) {
        q = 1
      }
      if (q != this.quantity) {
        this.quantity = q
      }
    },

    beforeOpened(event) {
      window.addEventListener('keyup', this.onKeyUp)
      this.params = event.params || {}
      this.$emit('before-opened', event)
      this.getProductPreviewDataRequest(this.params.productId)
    },

    beforeClosed(event) {
      window.removeEventListener('keyup', this.onKeyUp)
      this.params = {}
      this.$emit('before-closed', event)
      this.CLEAR_PREVIEW()
    },

    closeModal() {
      this.$modal.hide('product-preview-modal')
    },
  },
}
</script>
