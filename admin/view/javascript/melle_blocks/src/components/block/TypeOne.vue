<template>
    <div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">
                <span data-toggle="tooltip" title="ссылка на товар пишется как `=product/product&product_id=42`, т.e. не сео ссылка начинающаяся со знака =">Ссылка</span>
            </label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="link" placeholder="Ссылка" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Текст</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="text" placeholder="Текст" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Текст кнопки</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="buttonText" placeholder="Текст кнопки" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="input-image">Изображение</label>
            <div class="col-sm-9 col-lg-5">
                <a href="" :id="thumbId" data-toggle="image" class="img-thumbnail">
                    <img :src="thumb" alt="" title="" data-placeholder="Изображение" />
                </a>
                <input type="hidden" :name="imageName" :id="inputImageId" :value="image" v-once />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Сортировка</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="sortOrder" placeholder="Сортировка" class="form-control">
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
    props: {
        index: {
            type: Number,
        },
        block: {
            type: Object,
            default: {},
        },
    },
    computed: {
        link: {
            get () { return this.block.link },
            set (v) { this.updateBlockValue({i: this.index, k: 'link', v}) }
        },
        text: {
            get () { return this.block.text },
            set (v) { this.updateBlockValue({i: this.index, k: 'text', v}) }
        },
        buttonText: {
            get () { return this.block.buttonText },
            set (v) { this.updateBlockValue({i: this.index, k: 'buttonText', v}) }
        },
        thumb: {
            get () { return this.block.thumb },
            set (v) { this.updateBlockValue({i: this.index, k: 'thumb', v}) }
        },
        image: {
            get () { return this.block.image },
            set (v) { this.updateBlockValue({i: this.index, k: 'image', v}) }
        },
        sortOrder: {
            get () { return this.block.sortOrder },
            set (v) { this.updateBlockValue({i: this.index, k: 'sortOrder', v}) }
        },

        thumbId() {
            return `thumb-image${this.index}`
        },
        inputImageId() {
            return `input-image${this.index}`
        },
        originalImageId() {
            return `input-image-original${this.index}`
        },
        imageName() {
            return `image-${this.index}`
        },
    },
    methods: {
        ...mapActions('shop', [
            'updateBlockValue',
        ]),
    },
}
</script>