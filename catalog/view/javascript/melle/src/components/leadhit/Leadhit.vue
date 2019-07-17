<template>
    <div id="retail-rocket-section">
        <div class="col-sm-12 col-md-6 rr-block">
            <h3>Хиты продаж</h3>
            <carousel-block :items="hits" />
        </div>
        <div class="col-sm-12 col-md-6 rr-block">
            <h3>Мы рекомендуем Вам</h3>
            <carousel-block :items="recommend" />
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import CarouselBlock from './CarouselBlock.vue'

export default {
    components: {
        CarouselBlock,
    },
    computed: {
        ...mapState('leadhit', [
            'hits',
            'recommend',
        ]),
    },
    methods: {

    },
    created() {
        this.$store.dispatch('leadhit/initData')
    },
    mounted() {
        this.$store.dispatch('leadhit/getHits')
        this.$store.dispatch('leadhit/getRecommend')
    }
}
</script>

<style lang="scss">

</style>