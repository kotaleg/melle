<template>
    <div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">
                <span data-toggle="tooltip" title="ссылка на товар пишется как `=product/product&product_id=42`, т.e. не сео ссылка начинающаяся со знака =">Ссылка</span>
            </label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="block.link" placeholder="Ссылка" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Текст</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="block.text" placeholder="Текст" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="input-image">Изображение</label>
            <div class="col-sm-9 col-lg-5">
                <a href="" :id="thumbId" data-toggle="image" class="img-thumbnail">
                    <img :src="block.thumb" alt="" title="" data-placeholder="Изображение" />
                </a>
                <input type="hidden" :name="imageName" :id="inputImageId" :value="block.image" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label">Сортировка</label>
            <div class="col-sm-9 col-lg-5">
                <input type="text" v-model="block.sortOrder" placeholder="Сортировка" class="form-control">
            </div>
        </div>
    </div>
</template>

<script>
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
    }
}
</script>