<template>
    <div>
    <div class="catalog_list_view">
       <div>Найдено: {{ product_total }}</div>
       <ul v-if="product_total > 0" class="catalog__list" id="ivan_products_replace">

          <li v-for="(p, i) in products"
            :class="['catalog__item', p.znachek_class]">

             <a :href="p.href" class="catalog__item-link">
                <img :src="p.image">
             </a>

             <div class="catalog__item-ivaninfo">
                <div class="row">
                   <div class="col-xs-12">
                      <h3 class="ivanitemtitle"><a :href="p.href">{{ p.h1 }}</a></h3>
                   </div>
                   <div class="col-xs-7"><span class="catalog__item-price-default">{{ getPrice(i) }} <span class="ruble-sign">Р</span></span></div>
                   <div class="col-xs-5">
                      <div><a :href="p.href" class="ivanbuybutton">Купить</a></div>
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
        ]),
        ...mapState('catalog', [
            'products',
            'product_total',
        ]),
    },
    methods: {
        ...mapActions('catalog', [
            'loadMoreRequest',
        ]),

        loadMore() {
            this.loadMoreRequest()
        },
    },
    created() {
        this.$store.dispatch('catalog/initData')
        this.$store.dispatch('filter/initData')
    },
}
</script>

<style lang="scss">

</style>