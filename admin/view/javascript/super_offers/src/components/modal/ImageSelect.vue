<template>
    <div class="combination-image-select">
        <div class="row">
        <div v-for="(i, k) in images" :key="k" class="col-md-4 text-center">
            <img
                @click="selectImage(i.imagePath)"
                :src="i.imagePopup"
                :alt="i.hash"
                :class="['img-responsive select-handler', {'selected': selectedImage == i.imagePath}]"
            />
        </div>
        </div>

        <div class="clear-selection text-center">
            <div @click="clearSelection" class="btn btn-default btn-small">Очистить выбор</div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'

export default {
  props: {
    combid: {
        type: Number,
        required: true,
    },
    images: {
        type: Array,
    },
    selectedImage: {
        type: String,
    },
  },
  computed: {
    ...mapState('shop', [
        'text_cancel',
    ]),
  },
  methods: {
    ...mapActions('shop', [
        'updateCombinationValue',
    ]),

    selectImage(value) {
        this.updateCombinationValue({
            combination_id: this.combid,
            key: 'image',
            value,
        })
        this.$emit('close')
    },

    clearSelection() {
        this.selectImage('')
    }
  }
}
</script>

<style lang="scss">
.combination-image-select {
    padding: 10px;

    img {
        border: 1px rgba(0, 0, 0, 0.8) solid;
        cursor: pointer;

        &.select-handler {
            display: inline-block;
            vertical-align: middle;
            margin-bottom: 10px;
        }

        &.selected {
            opacity: 0.5;
            border-color: red;
        }
    }
}
</style>
</style>
