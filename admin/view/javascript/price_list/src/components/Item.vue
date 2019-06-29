<template>
    <form class="form-horizontal" id="sl-form">
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Название</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="title" placeholder="Название" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Порядок</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="sortOrder" placeholder="Порядок" class="form-control">
            </div>
        </div>
    </form>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    components: {
    },
    computed: {
        ...mapState('shop', [
            'text_cancel',
        ]),
        ...mapGetters('shop', [
            'getItemValue',
        ]),

        title: {
            get () { return this.getItemValue('title') },
            set (v) { this.updateItemValue({k: 'title', v}) }
        },
        sortOrder: {
            get () { return this.getItemValue('sortOrder') },
            set (v) { this.updateItemValue({k: 'sortOrder', v}) }
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateItemValue',
        ]),
    },
}
</script>

<style lang="scss">

</style>
