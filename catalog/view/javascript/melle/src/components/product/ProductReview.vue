<template>
  <div :class="['reviews row', { on: show_form }]">
    <div v-show="!show_form" class="col-md-12">
      <button @click="showForm()" class="btn btn-dark">ОСТАВИТЬ ОТЗЫВ</button>
    </div>
    <div class="col-md-12" v-show="show_form">
      <h4 class="title mb-3">Оставить отзыв</h4>
      <form
        class="reviews__form form-vertical"
        id="reviewForm"
        method="post"
        v-on:submit.prevent="addReview()"
      >
        <div class="form-group">
          <input
            placeholder="представьтесь"
            :class="['form-control', { 'is-invalid': fieldHasError('name') }]"
            type="text"
            v-model.trim="name"
          />
          <div v-show="fieldHasError('name')" class="invalid-feedback">
            {{ getFieldError('name') }}
          </div>
        </div>
        <div class="form-group">
          <textarea
            placeholder="Текст сообщения"
            :class="[
              'form-control',
              { 'is-invalid': fieldHasError('message') },
            ]"
            v-model.trim="message"
          ></textarea>
          <div v-show="fieldHasError('message')" class="invalid-feedback">
            {{ getFieldError('message') }}
          </div>
        </div>

        <div class="form-group">
          <div class="align-items-center d-flex justify-content-around">
            <span>оценка: </span>
            <star-rating
              :item-size="20"
              inactive-color="#d5d5d5"
              active-color="#2b2a29"
              :increment="1"
              v-model="rating"
            />
          </div>
          <div v-show="fieldHasError('rating')" class="invalid-feedback">
            {{ getFieldError('rating') }}
          </div>
        </div>

        <div v-if="isCaptcha && captchaKey" class="form-group">
          <vue-recaptcha
            v-if="isCaptcha"
            ref="review_recaptcha"
            @verify="onCaptchaVerified"
            @expired="onCaptchaExpired"
            size="invisible"
            :sitekey="captchaKey"
          />
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-dark btn-block">
            Отправить
          </button>
        </div>
      </form>
    </div>
    <div v-if="reviews.length > 0" class="col-md-12">
      <h4 class="title">
        Отзывы <span> ( {{ reviews.length }} )</span>
      </h4>
      <div class="row review-list">
        <div class="col-md-4">
          <div
            v-for="review in reviews"
            :key="review.review_id"
            class="d-flex flex-wrap justify-content-between review-item"
            itemprop="review"
            itemtype="http://schema.org/Review"
            itemscope
          >
            <div
              class="person"
              itemprop="author"
              itemtype="http://schema.org/Person"
              itemscope
            >
              <meta itemprop="name" :content="review.author" />
              <span>{{ review.author }}</span>
            </div>
            <div class="date">
              <span>{{ review.date_added }}</span>
            </div>
            <div
              class="rating"
              itemprop="reviewRating"
              itemtype="http://schema.org/Rating"
              itemscope
            >
              <meta itemprop="ratingValue" :content="review.rating" />
              <meta itemprop="bestRating" content="5" />
              <star-rating
                :item-size="20"
                inactive-color="#d5d5d5"
                active-color="#2b2a29"
                :increment="1"
                :rating="review.rating"
                :read-only="true"
                :show-rating="false"
              />
            </div>
            <div class="w-100 mt-3">
              <p>{{ review.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex'
import { StarRating } from 'vue-rate-it'
import VueRecaptcha from 'vue-recaptcha'

export default {
  components: {
    StarRating,
    VueRecaptcha,
  },
  computed: {
    ...mapGetters('header', ['isCaptcha', 'captchaKey']),
    ...mapGetters('review', ['getFormValue', 'fieldHasError', 'getFieldError']),
    ...mapState('review', ['reviews']),

    name: {
      get() {
        return this.getFormValue('name')
      },
      set(v) {
        this.updateFormValue({ k: 'name', v })
      },
    },
    email: {
      get() {
        return this.getFormValue('email')
      },
      set(v) {
        this.updateFormValue({ k: 'email', v })
      },
    },
    message: {
      get() {
        return this.getFormValue('message')
      },
      set(v) {
        this.updateFormValue({ k: 'message', v })
      },
    },
    rating: {
      get() {
        return this.getFormValue('rating')
      },
      set(v) {
        this.updateFormValue({ k: 'rating', v })
      },
    },
  },
  methods: {
    ...mapActions('header', ['captchaRequest']),
    ...mapActions('review', ['updateFormValue', 'addReviewRequest']),

    showForm() {
      this.show_form = !this.show_form
    },
    addReview() {
      if (this.isCaptcha && this.captchaKey && this.$refs.review_recaptcha) {
        this.$refs.review_recaptcha.execute()
      } else {
        this.addReviewRequest().then((res) => {
          if (res === true) {
            this.show_form = false
          }
        })
      }
    },
    onCaptchaVerified(recaptchaToken) {
      this.$refs.review_recaptcha.reset()

      this.captchaRequest(recaptchaToken).then((captcha_res) => {
        if (captcha_res === true) {
          this.addReviewRequest().then((res) => {
            if (res === true) {
              this.show_form = false
            }
          })
        }
      })
    },
    onCaptchaExpired() {
      this.$refs.review_recaptcha.reset()
    },
  },
  data() {
    return {
      show_form: true,
    }
  },
  created() {
    this.$store.dispatch('review/initData')
  },
}
</script>
