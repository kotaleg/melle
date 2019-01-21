<template>
    <section class="basket-modal">
        <sidebar-buttons />

        <h2 class="basket-modal__title">Ваша корзина</h2>
        <div id="cart-controller">
            <ul class="basket-modal__list">
                <li v-for="(p, i) in products" class="basket-modal__item">
                    <div class="basket-modal__item-left">
                        <a :href="product.href"><img :src="product.thumb" class="basket-modal__prod-img"></a>
                    </div>
                    <div class="basket-modal__item-center">
                        <div class="basket-modal__prod-info">
                            <div class="basket-modal__prod-title"><a :href="product.href">{{ product.name }}</a></div>
                            <div v-if="product.options" v-for="(option) in p.options" class="basket-modal__prod-article">
                                <span>{{ option.name }} </span><span>{{ option.value }}</span>
                            </div>
                        </div>
                        <div class="basket-modal__prod-count">
                            <button class="item_minus" role="button" @click="minusCount(p.cart_id)"><span>-</span></button>
                            <input type="text" :value="product.quantity" @change="changeCount(p.cart_id)" class="item_col keyPressedNum boldCount">
                            <button class="item_plus" role="button" @click="plusCount(p.cart_id)"><span>+</span></button>
                            <span v-show="product.quantity >= product.max_quantity" class="catalog__item-count_label">доступно:<span>{{ product.max_quantity }}</span></span>
                        </div>
                    </div>
                    <div class="basket-modal__item-right">
                        <div class="basket-modal__del">
                            <button @click="removeProduct(p.cart_id)">
                                <svg viewBox="0 0 191.414 191.414" width="21" height="21">
                                    <path d="M107.888,96.142l80.916-80.916c3.48-3.48,3.48-8.701,0-12.181s-8.701-3.48-12.181,0L95.707,83.961L14.791,3.045   c-3.48-3.48-8.701-3.48-12.181,0s-3.48,8.701,0,12.181l80.915,80.916L2.61,177.057c-3.48,3.48-3.48,8.701,0,12.181   c1.74,1.74,5.22,1.74,6.96,1.74s5.22,0,5.22-1.74l80.916-80.916l80.916,80.916c1.74,1.74,5.22,1.74,6.96,1.74   c1.74,0,5.22,0,5.22-1.74c3.48-3.48,3.48-8.701,0-12.181L107.888,96.142z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="basket-modal__price">
                            <span class="basket-modal__price-default">{{ product.price }} <span class="ruble-sign">Р</span></span>
                        </div>
                    </div>
                </li>
            </ul>
            <div class="basket-modal__footer">
                <div class="basket-modal__clean">
                    <a v-if="hasProducts" :href="cart_link">Оформить заказ</a>
                </div>
                <div class="basket-modal__full-price">
                    <span>ИТОГО: </span><span class="boldPrice">{{ total }} <span class="ruble-sign">Р</span></span>
                </div>
                <div v-if="!hasProducts" class="empty_basket">
                    <span class="">корзина пуста</span>
                </div>
            </div>
            <div v-if="hasProducts" class="basket-modal__buttons">
                <a :href="catalog_link">
                    <svg viewBox="0 0 489.2 489.2" width="20" height="20">
                        <path d="M481.044,382.5c0-6.8-5.5-12.3-12.3-12.3h-418.7l73.6-73.6c4.8-4.8,4.8-12.5,0-17.3c-4.8-4.8-12.5-4.8-17.3,0l-94.5,94.5c-4.8,4.8-4.8,12.5,0,17.3l94.5,94.5c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6c4.8-4.8,4.8-12.5,0-17.3l-73.6-73.6h418.8C475.544,394.7,481.044,389.3,481.044,382.5z"></path>
                    </svg>
                    Продолжить покупки
                </a>
                <a href="javascript:void(0)" @click="clearCart()">
                    очистить корзину
                    <svg viewBox="0 0 191.414 191.414" width="12" height="15">
                        <path d="M107.888,96.142l80.916-80.916c3.48-3.48,3.48-8.701,0-12.181s-8.701-3.48-12.181,0L95.707,83.961L14.791,3.045   c-3.48-3.48-8.701-3.48-12.181,0s-3.48,8.701,0,12.181l80.915,80.916L2.61,177.057c-3.48,3.48-3.48,8.701,0,12.181   c1.74,1.74,5.22,1.74,6.96,1.74s5.22,0,5.22-1.74l80.916-80.916l80.916,80.916c1.74,1.74,5.22,1.74,6.96,1.74   c1.74,0,5.22,0,5.22-1.74c3.48-3.48,3.48-8.701,0-12.181L107.888,96.142z"></path>
                    </svg>
                </a>
            </div>
            <div i-if="!hasProducts" class="basket-modal__buttons basket-modal__buttons--empty">
                <a :href="catalog_link">Начать покупки</a>
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
            'catalog_link',
            'cart_link',
            'checkout_link',
        ]),
        ...mapState('header', [
            'phone',
        ]),
        ...mapGetters('header', [
            'phoneLink',
        ]),
        ...mapGetters('cart', [
            'hasProducts',
        ]),
    },
    methods: {
        ...mapActions('header', [
            'enableElement',
        ]),

        minusCount(cart_id) {

        },
        plusCount(cart_id) {

        },
        changeCount(cart_id) {
            // TODO: add changed quantity
        },
        removeProduct(cart_id) {

        },
        clearCart() {

        },
    },
    created() {

    },
}
</script>

<style lang="scss">

</style>