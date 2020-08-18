<template>
  <form class="add-to-cart form-vertical" method="post">
    <div
      v-for="(o, o_key) in options"
      :key="`option-${o_key}`"
      :class="['prod-card__form-group', `prod-card__form-group--${o.class}`]"
    >
      <div>
        <span class="ivan-product-selectors">{{ o.name }}:</span>
        <div :id="[`ivan-js-${o.class}-list`]" :class="[o.class]">
          <label
            v-for="(ov, ov_key) in o.product_option_value"
            :key="`option-value-${ov_key}`"
            :class="[
              'radio-inline',
              `ivan-${o.class}-radio`,
              { check: ov.selected, disabled: ov.disabled_by_selection },
            ]"
          >
            <div :class="[{ krestik: ov.disabled_by_selection }]">
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
            id="tablitsa-razmerov"
            data-fancybox=""
            class="hidden-sm"
            :href="size_list"
            >таблица<br />размеров</a
          >
        </div>
      </div>
    </div>

    <div class="prod-card__form-group prod-card__form-group--count">
      <span class="ivan-product-selectors">кол-во:</span>
      <div class="prod-card__count-wrap">
        <div class="prod-card__count">
          <button
            @click="quantityHandler('-')"
            class="item_minus"
            type="button"
          >
            -
          </button>
          <input
            id="productCounter"
            class="item_col keyPressedNum"
            v-model.number="quantity"
          />
          <button @click="quantityHandler('+')" class="item_plus" type="button">
            +
          </button>

          <span
            v-if="in_stock"
            v-show="quantity >= getActiveMaxQuantity"
            class="catalog__item-count_label js-product-count-block"
          >
            доступно:
            <span class="js-product-count">{{ getActiveMaxQuantity }}</span>
          </span>
        </div>
      </div>
    </div>

    <div v-if="in_stock" class="prod-card__form-group prod-card__form-group--price">
      <div class="prod-card__price">
        <span
          v-if="isSpecial"
          class="prod-card__price-default prod-card__price-ivanold"
          >{{ getActivePrice }} <span class="ruble-sign">Р</span></span
        >
        <span v-if="isSpecial" class="prod-card__price-default"
          >{{ getSpecial }}
          <span v-if="zvezdochka" class="ruble-container-p">
            <span class="ruble-sign">Р</span
            ><span class="ruble-zvezdochka-p">*</span>
          </span>
          <span v-else class="ruble-sign">Р</span>
        </span>

        <span v-if="zvezdochka" class="special-text-info">{{
          special_text
        }}</span>

        <span v-if="!isSpecial" class="prod-card__price-default"
          >{{ getActivePrice }} <span class="ruble-sign">Р</span></span
        >

        <div
          class="prod-card__form-group prod-card__form-group--rating"
          itemprop="aggregateRating"
          itemtype="http://schema.org/AggregateRating"
          itemscope
        >
          <meta itemprop="reviewCount" :content="reviewCount" />
          <meta itemprop="ratingValue" :content="ratingValue" />

          <div class="star-rating star-rating--span">
            <span
              v-for="(r, rKey) in getRating"
              :key="`rating-${rKey}`"
              :class="[
                'fa',
                'fa-lg',
                { 'fa-star': r === true, 'fa-star-o': r === false },
              ]"
            />
          </div>
        </div>
      </div>
    </div>

    <div v-if="in_stock" class="ivan-price-handler">
      <div
        v-if="getProductCountForCurrentSelectedOptions <= 0"
        style="margin: 0px;"
        class="prod-card__form-group prod-card__form-group--send ivan-price-button"
      >
        <a
          id="add_trigger_button"
          @click="addToCart()"
          href="javascript:void(0);"
          ><span
            >Добавить <br />
            В корзину</span
          ></a
        ><br />
      </div>

      <div
        v-else
        style="margin: 0px;"
        class="prod-card__form-group prod-card__form-group--send"
      >
        <div>
          <div
            @click="goToCheckout()"
            class="btn dynamic-add-button dynamic-add-button__count"
          >
            в корзине
            {{ getProductCountForCurrentSelectedOptions }} шт.<br />перейти
          </div>
          <div
            @click="addToCart()"
            class="btn dynamic-add-button dynamic-add-button__plus"
          >
            +1 шт.
          </div>
        </div>
      </div>

      <div
        class="prod-card__form-group--send ivan-price-button one-click-button"
      >
        <div class="modal--send"></div>
        <a
          @click="buyOneClick()"
          href="javascript:void(0);"
          class="fast-order-link btn"
          >Купить <br />
          в 1 клик</a
        >
      </div>
    </div>

    <div v-else class="ivan-price-handler">
      <div
        class="prod-card__form-group--send ivan-price-button one-click-button not-in-stock"
      >
        <div class="modal--send"></div>
        <a
          @click="notifyInStock()"
          href="javascript:void(0);"
          class="fast-order-link btn"
          >Сообщить о поступлении</a
        >
      </div>
    </div>

    <input type="hidden" id="active-image-hash" :value="currentImageHash" />

    <one-click-modal dir="ltr" :width="500" :scrollable="false" />
    <notify-in-stock-modal dir="ltr" :width="500" :scrollable="false" />
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
      this.$modal.show('one-click-modal', {source: 'buy-one-click'})
    },
    notifyInStock() {
      this.$modal.show('one-click-modal', {source: 'notify-in-stock'})
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

<style lang="scss" scoped>
/* KRESTIK */
.krestik {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.krestik::before,
.krestik::after {
  position: absolute;
  content: '';
  width: 100%;
  height: 4px; /* cross thickness */
  background-color: red;
}

.krestik::before {
  transform: rotate(45deg);
}

.krestik::after {
  transform: rotate(-45deg);
}
</style>
