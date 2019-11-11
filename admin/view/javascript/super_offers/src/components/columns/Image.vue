<template>
    <div @click="clickHandler" class="combination-image-preview">
        <img v-if="currentImagePreview" :src="currentImagePreview" :alt="getInputName" class="img-responsive">
        <div v-else class="ci-placeholder"></div>

        <input
            type="hidden"
            :name="getInputName"
            :value="getInputValue"
            :placeholder="text_image"
        />
    </div>
</template>

<script type="text/javascript">
import { mapState, mapActions, mapGetters } from 'vuex'
import {isEqual, has, isArray} from 'lodash'

import ImageSelect from '../modal/ImageSelect.vue'

export default {
    props: {
        combid: {
            type: Number,
            required: true,
        },
    },
    computed: {
        ...mapState('shop', [
            'text_image',
            'product_images',
        ]),
        ...mapGetters('shop', [
            'getCombinationDataValue',
        ]),
        getInputName() {
            return 'so_combination['+this.combid+'][image]'
        },
        getInputValue() {
            return this.getCombinationDataValue(this.combid, 'image')
        },

        currentImagePreview() {
            if (isArray(this.product_images)) {
                const currentImage = this.getInputValue

                for (const i in this.product_images) {
                    if (has(this.product_images[i], 'imagePath')) {
                        if (isEqual(this.product_images[i]['imagePath'], currentImage)) {
                            return this.product_images[i]['imagePreview']
                        }
                    }
                }
            }

            return false
        },
    },
    methods: {
        clickHandler() {
            console.log('ad');
            this.$modal.show(ImageSelect, {
                combid: this.combid,
                images: this.product_images,
                selectedImage: this.getInputValue,
            }, {
                draggable: false,
                width: 500,
                scrollable: false,
                height: 'auto',
            })
        },
    },
}
</script>

<style lang="scss">
.combination-image-preview {
    img {
        border: 1px rgba(0, 0, 0, 0.8) solid;
        cursor: pointer;

        &.select {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }
    }

    .ci-placeholder {
        width: 31px;
        height: 31px;
        background-color: rgba(0, 0, 0, 0.3);
        border: 1px rgba(0, 0, 0, 0.8) solid;
        cursor: pointer;
    }
}
</style>