<template>
    <carousel
        :id="getCarouselId"
        class="ivan-slideshow"
        :autoplay="true"
        :autoplayTimeout="10000"
        :perPage="1"
        :navigationEnabled="true"
        :paginationEnabled="false"
        navigationPrevLabel="<svg height='1cm' width='.7cm' enable-background='new 0 0 25 36' viewBox='0 0 25 36' xmlns='http://www.w3.org/2000/svg'><path d='m18.7 32.2-14.5-14.3 14.5-15.7' fill='none' stroke='#fff' stroke-width='4'/></svg>"
        navigationNextLabel="<svg  height='1cm' width='.7cm' enable-background='new 0 0 25 36' viewBox='0 0 25 36' xmlns='http://www.w3.org/2000/svg'><path d='m5.5 2 14.5 15.6-14.5 14.4' fill='none' stroke='#fff' stroke-width='4'/></svg>">

        <slide v-for="(p, i) in getSlidesForModule(moduleId)" :key="i"
            class="i-slide"
            :data-href="getDataFromSlide(p, 'link')"
            @slideclick="handleSlideClick">

            <img :src="getDataFromSlide(p, 'image')" :alt="getDataFromSlide(p, 'title')">

        </slide>

    </carousel>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { has } from 'lodash'
import { Carousel, Slide } from 'vue-carousel'

export default {
    props: {
        moduleId: {
            type: [Number, String],
        },
    },
    components: {
        Carousel,
        Slide,
    },
    computed: {
        ...mapGetters('slideshow', [
            'getSlidesForModule',
        ]),

        getCarouselId() {
            return `ivan-slideshow-${this.moduleId}`
        },
    },
    methods: {
        handleSlideClick(dataset) {
            console.log(`REDIRECTING TO ${dataset.href}`);
            window.location.href = dataset.href;
        },

        getDataFromSlide(slide, dataType) {
            const type = this.getTypeFromWidth()

            if (has(slide, type)) {
                return slide[type][dataType]
            }
            return ''
        },

        getTypeFromWidth() {
            if (this.windowWidth <= 767) {
                return 'small'
            } else if (this.windowWidth > 767 && this.windowWidth < 1199) {
                return 'medium'
            } else {
                return 'big'
            }
        }
    },
    data() {
        return {
            windowWidth: 0,
        }
    },
    mounted() {
        this.windowWidth = window.innerWidth
        this.$nextTick(() => {
            window.addEventListener('resize', () => {
                this.windowWidth = window.innerWidth
            })
        })
        this.$store.dispatch('slideshow/initData', this.moduleId)
    },
}
</script>