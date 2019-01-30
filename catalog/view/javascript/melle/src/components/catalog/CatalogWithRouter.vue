<template>
    <router-view></router-view>
</template>

<script>
export default {
    components: {

    },
    computed: {

    },
    methods: {

    },
    created() {
        this.$store.dispatch('catalog/initData')
        this.$store.dispatch('filter/initData')
    },
}
</script>

<style lang="scss">

</style>