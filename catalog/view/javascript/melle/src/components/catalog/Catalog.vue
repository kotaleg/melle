<template>
    <div>
    <h1>{{ current_category }}</h1>

    <div class="catalog_list_view">
       <ul v-if="product_total > 0" class="catalog__list" id="ivan_products_replace">

          <li v-for="(p, i) in products"
            :class="['catalog__item', p.znachek_class]">

             <a @click="gtmProductClick(i)" :href="p.href" class="catalog__item-link">
                <img :src="p.image" :alt="p.h1" />
             </a>

             <div v-if="p.special_text" class="catalog__item-price super-div" style="top: 0px;">
               <span class="catalog__item-price-default super-text" style="font-size: 0.79vw;">{{ p.special_text }}</span>
             </div>

             <div class="catalog__item-ivaninfo">
                <div class="row">
                   <div class="col-xs-12">
                      <h3 @click="gtmProductClick(i)" class="ivanitemtitle"><a :href="p.href">{{ p.h1 }}</a></h3>
                   </div>
                   <div class="col-xs-7">
                        <span v-if="isSpecial(i)" class="catalog__item-price-old">
                            {{ getPrice(i) }} <span class="ruble-sign">Р</span>
                        </span>
                        <span v-if="isSpecial(i)" class="catalog__item-price-default">
                            {{ getSpecial(i) }}
                            <span v-if="p.zvezdochka" class="ruble-container"><span class="ruble-sign">Р</span><span class="ruble-zvezdochka">*</span></span>
                            <span v-else class="ruble-sign">Р</span>
                        </span>

                        <span v-if="!isSpecial(i)" class="catalog__item-price-default">
                            {{ getPrice(i) }} <span class="ruble-sign">Р</span></span>
                   </div>
                   <div class="col-xs-5">
                      <div><a @click="gtmProductClick(i)" :href="p.href" class="ivanbuybutton">Купить</a></div>
                   </div>
                </div>
             </div>

          </li>

       </ul>
    </div>

    <div v-show="canLoadMore" style="text-align: center;">
        <button  @click="loadMore()" id="view-more-button" class="btn button"><span>ПОКАЗАТЬ ЕЩЁ</span></button>
    </div>

    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    components: {

    },
    computed: {
        ...mapGetters('catalog', [
            'canLoadMore',
            'getRating',
            'getPrice',
            'getProductForGTM',
            'isSpecial',
            'getSpecial',
        ]),
        ...mapState('catalog', [
            'current_category',
            'products',
            'product_total',
        ]),
    },
    methods: {
        ...mapActions('catalog', [
            'loadMoreRequest',
        ]),
        ...mapActions('gtm', [
            'productClick',
        ]),

        loadMore() {
            this.loadMoreRequest()
        },

        gtmProductClick(i) {
            let product = this.getProductForGTM(i)
            this.productClick({page_type: false, product})
        },
    },
    mounted() {
        // GTM
        this.$store.dispatch('gtm/loadCatalog')
    },
}
</script>

<style lang="scss">

</style>