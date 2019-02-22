<template>
    <form class="form-horizontal" id="sl-form">
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Название</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="name" placeholder="Название" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="input-image">Изображение</label>
            <div class="col-sm-9 col-lg-5">
                <a href="" id="thumb-image" data-toggle="image" class="img-thumbnail">
                    <img :src="thumb" alt="" title="" data-placeholder="Изображение" />
                </a>
                <input type="hidden" name="image" id="input-image" />
                <input type="hidden" name="image_original" v-model="image" id="input-image-original" />
            </div>
        </div>

    </form>
</template>

<script>
import { isEmpty, isString, trim } from 'lodash'
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

        name: {
            get () { return this.getItemValue('name') },
            set (v) { this.updateItemValue({k: 'name', v}) }
        },
        image: {
            get () { return this.getItemValue('image') },
            set (v) { this.updateItemValue({k: 'image', v}) }
        },
        thumb: {
            get () { return this.getItemValue('thumb') },
            set (v) { this.updateItemValue({k: 'thumb', v}) }
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
