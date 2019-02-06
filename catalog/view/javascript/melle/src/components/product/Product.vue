<template>
    <form class="add-to-cart form-vertical" id="yw3" method="post">

         <div v-for="(o, o_key) in options" :class="['prod-card__form-group', `prod-card__form-group--${o.class}`]"
            v-if="o.type === 'radio'">
            <div>
               <span class="ivan-product-selectors">{{ o.name }}:</span>
               <div :id="[`ivan-js-${o.class}-list`]" :class="[o.class]">

                    <label v-for="(ov, ov_key) in o.product_option_value" :class="['radio-inline', `ivan-${o.class}-radio`]">
                        <input type="radio"
                            @click="radioHandler({o_key: o_key, ov_key: ov_key, status: !ov.selected})"
                            :name="[`ShopCartItem[${o.class}]`]"
                            :placeholder="ov.name"
                            :value="ov.option_value_id"
                            :class="[{'check': ov.selected, 'disabled': ov.disabled_by_selection}]">

                        <span v-if="!ov.image">{{ ov.name }}</span>
                        <span v-if="ov.image">
                            <img :src="ov.image"
                                :class="[`${o.class}-image`]"
                                v-tooltip="{content: ov.name}">
                        </span>
                    </label>

                </div>
            </div>
         </div>

         <div class="prod-card__form-group prod-card__form-group--count">
            <span class="ivan-product-selectors">кол-во:</span>
            <div class="prod-card__count-wrap">
               <div class="prod-card__count">
                <button @click="quantityHandler('-')" class="item_minus" type="button">-</button>
                <input id="productCounter" class="item_col keyPressedNum" v-model.number="quantity">
                <button @click="quantityHandler('+')" class="item_plus" type="button">+</button>

                <span v-show="quantity >= getAvailableQuantity" class="catalog__item-count_label js-product-count-block"">
                    доступно:
                    <span class="js-product-count">{{ getAvailableQuantity }}</span>
                </span>
               </div>
            </div>
         </div>

         <div class="prod-card__form-group prod-card__form-group--price">
            <div class="prod-card__price">
               <span class="prod-card__price-default">{{ getPrice }} <span class="ruble-sign">Р</span></span>
               <div class="prod-card__form-group prod-card__form-group--rating">
                  <div class="star-rating star-rating--span">
                    <span v-for="r in getRating" :class="['fa', 'fa-lg', {'fa-star': r === true, 'fa-star-o': r === false}]" />
                  </div>
               </div>
            </div>
         </div>

         <div id="ivan-price-handler">
            <div style="margin: 0px" class="prod-card__form-group prod-card__form-group--send ivan-price-button">
               <a id="add_trigger_button" @click="addToCart()" href="javascript:void(0);"><span>Добавить <br> В корзину</span></a><br>
            </div>

            <div class="prod-card__form-group--send ivan-price-button one-click-button">
               <div class="modal--send"></div>
               <a @click="buyOneClick()" href="javascript:void(0);" class="fast-order-link btn">Купить <br> в 1 клик</a>
            </div>
         </div>


         <one-click-modal
            dir="ltr"
            :width="500"
            :scrollable="false"/>

    </form>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

import OneClickModal from './../modal/OneClickModal.vue'

export default {
    components: {
        'one-click-modal': OneClickModal,
    },
    computed: {
        ...mapGetters('product', [
            'getRating',
            'getPrice',
            'isSpecial',
            'getSpecial',
            'getAvailableQuantity',
            'getFormValue',
            'getStateValue',
        ]),
        ...mapState('product', [
            'product_id',
            'options',
        ]),

        quantity: {
            get() { return this.getStateValue('quantity') },
            set(v) { this.updateQuantity(v) },
        },
    },
    methods: {
        ...mapActions('product', [
            'quantityHandler',
            'updateQuantity',
            'radioHandler',
            'addToCartRequest',
        ]),

        addToCart() {
            this.addToCartRequest()
        },
        buyOneClick() {
          this.$modal.show('one-click-modal', {});
        },
    },
    created() {
        this.$store.dispatch('product/initData')
    },
}
</script>

<style lang="scss">

</style>