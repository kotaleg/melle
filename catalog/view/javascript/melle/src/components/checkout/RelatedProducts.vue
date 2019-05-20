<template>
   <div class="other-prod">
      <h3 class="other-prod__title">С этим товаром покупают</h3>
      <carousel
         :perPage="4"
         :navigationEnabled="true"
         navigationPrevLabel="<svg style='transform: rotate(0.75turn);' viewBox='0 0 64 64' height='20' width='20'><path d='M3.352,48.296l28.56-28.328l28.58,28.347c0.397,0.394,0.917,0.59,1.436,0.59c0.52,0,1.04-0.196,1.436-0.59   c0.793-0.787,0.793-2.062,0-2.849l-29.98-29.735c-0.2-0.2-0.494-0.375-0.757-0.475c-0.75-0.282-1.597-0.107-2.166,0.456   L0.479,45.447c-0.793,0.787-0.793,2.062,0,2.849C1.273,49.082,2.558,49.082,3.352,48.296z'></path></svg>"
         navigationNextLabel="<svg style='transform: rotate(0.25turn);' viewBox='0 0 64 64' height='20' width='20'><path d='M3.352,48.296l28.56-28.328l28.58,28.347c0.397,0.394,0.917,0.59,1.436,0.59c0.52,0,1.04-0.196,1.436-0.59   c0.793-0.787,0.793-2.062,0-2.849l-29.98-29.735c-0.2-0.2-0.494-0.375-0.757-0.475c-0.75-0.282-1.597-0.107-2.166,0.456   L0.479,45.447c-0.793,0.787-0.793,2.062,0,2.849C1.273,49.082,2.558,49.082,3.352,48.296z'></path></svg>">

         <slide v-for="(p, i) in cart_related_products" :key="i" class="other-prod__item">
            <a :href="p.href" class="other-prod__item-link"><img :src="p.thumb" :alt="p.h1"></a>
            <div class="other-prod__item-ivaninfo">
               <div class="row">
                  <div class="col-xs-12">
                     <h3 class="ivanitemtitle"><a :href="p.href">{{ p.h1 }} </a></h3>
                  </div>
                  <div class="col-xs-7"><span class="other-prod__item-price-default">{{ p.price }}<span class="ruble-sign">Р</span></span></div>
                  <div class="col-xs-5">
                     <div><a :href="p.href" class="ivanbuybutton">Купить</a></div>
                  </div>
               </div>
            </div>
         </slide>

      </carousel>
   </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { Carousel, Slide } from 'vue-carousel';

export default {
    components: {
        Carousel,
        Slide
    },
    computed: {
        ...mapState('checkoutrp', [
            'cart_related_products',
        ]),
    },
    methods: {

    },
    created() {
      this.$store.dispatch('checkoutrp/initData')
    },
    mounted() {
        // REMOVE PRERENDERED CONTENT
        let crpprerender = document.getElementById('rendered-checkout-rp')
        console.log(crpprerender)
        if (crpprerender) { prerender.remove() }
    }
}
</script>

<style lang="scss">

</style>