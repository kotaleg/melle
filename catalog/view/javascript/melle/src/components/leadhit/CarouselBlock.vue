<template>
  <carousel
    :perPage="2"
    :perPageCustom="[
      [0, 1],
      [768, 2],
      [1199, 3],
    ]"
    :navigationEnabled="true"
    :paginationEnabled="false"
    navigationPrevLabel="<svg height='1cm' width='.7cm' enable-background='new 0 0 25 36' viewBox='0 0 25 36' xmlns='http://www.w3.org/2000/svg'><path d='m18.7 32.2-14.5-14.3 14.5-15.7' fill='none' stroke='#000' stroke-width='4'/></svg>"
    navigationNextLabel="<svg height='1cm' width='.7cm' enable-background='new 0 0 25 36' viewBox='0 0 25 36' xmlns='http://www.w3.org/2000/svg'><path d='m5.5 2 14.5 15.6-14.5 14.4' fill='none' stroke='#000' stroke-width='4'/></svg>"
  >
    <slide v-for="(p, i) in items" :key="i">
      <div class="rr-product">
        <a :href="p.url" class="rr-pimage"
          ><img :src="p.picture" :alt="p.name"
        /></a>
        <h3 class="">
          <a :href="p.url">{{ p.name }}</a>
        </h3>
        <div class="stars">
          <div v-for="s in getStars(p.rating)" class="star">
            <svg
              v-if="s"
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              width="0.583cm"
              height="0.548cm"
            >
              <path
                fill-rule="evenodd"
                stroke="rgb(0, 0, 0)"
                stroke-width="1.69px"
                stroke-linecap="butt"
                stroke-linejoin="miter"
                fill="rgb(0, 0, 0)"
                d="M7.239,1.429 L4.782,4.938 L0.922,5.991 L3.379,9.500 L3.379,13.712 L7.239,12.659 L11.451,13.712 L11.451,9.851 L13.907,6.693 L10.398,5.289 L7.239,1.429 Z"
              />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              width="0.583cm"
              height="0.548cm"
            >
              <path
                fill-rule="evenodd"
                stroke="rgb(0, 0, 0)"
                stroke-width="1.69px"
                stroke-linecap="butt"
                stroke-linejoin="miter"
                fill="rgb(255, 255, 255)"
                d="M7.677,1.429 L5.220,4.938 L1.359,5.991 L3.816,9.500 L3.816,13.712 L7.677,12.659 L11.888,13.712 L11.888,9.851 L14.345,6.693 L10.835,5.289 L7.677,1.429 Z"
              />
            </svg>
          </div>
        </div>
        <div class="rr-price">
          <span class="other-prod__item-price-default"
            >{{ p.price }}<span class="ruble-sign"> Р</span></span
          >
        </div>
        <div class="rr-buy-icon">
          <img
            src="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAAC8AAAAvCAQAAADZLlsnAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QA/4ePzL8AAAAHdElNRQfjBxESFxgPzES4AAAEMUlEQVRYw6WYTUwcZRjHfzvLh3xuQcGUUhoaU5oIugKnapQ12sRbL/bjoB5MPBjTJhpvpWr02IOBgxcP9kYKXKBRwcWFtJ6sthAPbWKsMa0G2mC2lNayrH8P78wwOzsfu/U/h533eZ//f9553o95nk2IaCQs0oyQpo9umkiRZ5ObXGeZHFf1bwxdEReDjLEa4bDKGINRComw0ScyjJLZaXfyFM2kyHOPX1nzuub4VLkqRs8+ZpxGl05qRusqxbpmdFJdO5RZ9gUqBZheJ29uM/pG2wrHtr5VxqHlORorT5Ixc9uvnCpDTv0OfYxkhDx1TCJkaVSFCsUlqaBRWUZikroQeZJMILRL31Uh7SCrXUbmvPcNvPJjCO3WcojAV3pWNTqgL0L6l7XbDlGAPEcRSmklZBLfEEL1QujdkAesKGXEjvnk6SaPkpoPIb4j1K5JbWtOKaEvQ/zmlTSraG+p/BRCo6GRHRDK2vfTQr2hnqNGcMqzaxMj5KCfn6n1bLjTfE4dAC38RYEeaqilmQIr1PIMKSzb8zAfuqxtnuMXgIwW7V1LFqGlklFsqWSFxVy/e5hLxpa1g8MwQq/6XvJaFeL4VtthYx0SNcCbAB/4zqIHAPTxNQBpNliiye4bJsG8ff82fwAPS7jvm963+Aks1lCPir7RXxRCh+zWsIbcnvvq0BNu6wUhdLGEW1SPEGtYFmk64Ig7TQ42AGiwWz9y2e1pYI3bbqsegHwJ1+IIQAdpy5zpr5Qd1PcBaCQOj0FZcFy9jMUgwKEQ+ZZY+VYA/vFZn7cnyeIgdPJ4Ge0eAM2x8iY4d33WdjoB+iz2wP4A2kaFwTGzs1Vm3w/wpEU7pAJolca+xePtRQqgxaJ2Z3148cBDjkKdJ5RlQWuxKDhSQcFpIg5NIaN/CLBhse5ftXjGEx8cs3I2y+x5W/4W/BZAqzT2jSHBuQGwanEd1lgPkW+tUN6/MNdZBbhucQXghzJadbH3B8fWu2JxCSBbRvt/sV8wP5cgyW3UVXZiHhAi5MPuxWUh9LTvw7/HnJhJS0Um4E/mfM83izVFHBo9oXQwzy2ACRVrgHO8B2d5rcTFTNYWf8fIb3tC6eCs+TmH/a1dQGix5AUbqvoY1nuYi8a24CYijJik1ZtXvliVfIPLK2jA2DLePGcaoTMe+Rs6oR61qU1tTnrqXgnb7lyt+sTlnTE+06Vp1N7oLK1SuFlajz/HPBaVY1YGN8c8/ggZchzcDHk8LL8/b/L77COIu/n9VEh+v1OdJPVRldXJx0rGVSf2G9i11UDFtdWSsxTjaivbdHynMpwrO4u8KGrOWxmeCNAK2iX0csFpdOmUZgPq2lmd8ta1F+gNUgqvyl/mdMVV+Wf6PkQmaq8zxDh3IhzuMM5QlEIi9h+RJGleIs1BummilbtscpNrLLPIVRWj2f8Bwzoopj3uZdIAAAAASUVORK5CYII="
            :alt="p.name"
            width="47px"
            heigh="47px"
          />
        </div>
      </div>
    </slide>
  </carousel>
</template>

<script>
import { Carousel, Slide } from 'vue-carousel'

export default {
  props: {
    items: {
      type: Array,
      default: [],
    },
  },
  components: {
    Carousel,
    Slide,
  },
  computed: {},
  methods: {
    getStars(rating) {
      return [true, true, true, true, true]
    },
  },
}
</script>
