<template>
    <div class="price-list-container">
        <p v-for="pl in priceLists">
            <a :href="pl.downloadLink" class="price-list-link">{{ pl.title }}</a>
        </p>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    computed: {
        ...mapState('header', [
            'phone',
            'is_logged',
            'account_link',
            'logout_link',
        ]),
        ...mapState('priceList', [
            'priceLists',
        ]),
    },
    methods: {
        ...mapActions('header', [
            'enableElement',
        ]),
    },
    created() {
        this.$store.dispatch('priceList/initData')
    }
}
</script>

<style lang="scss">

</style>