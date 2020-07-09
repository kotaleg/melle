<template>
  <div :class="['reviews', { on: show_form }]">
    <div @click="showForm()" v-show="!show_form" class="rev-btn">
      ОСТАВИТЬ ОТЗЫВ
    </div>
    <div v-show="show_form" class="reviews__right">
      <h2 class="reviews__title">Оставить отзыв</h2>
      <form
        class="reviews__form form-vertical"
        id="reviewForm"
        method="post"
        v-on:submit.prevent="addReview()"
      >
        <div class="reviews__form-group">
          <div
            v-show="fieldHasError('name')"
            class="help-block error"
            id="ProductReviewForm_author_em_"
          >
            {{ getFieldError('name') }}
          </div>
          <input
            placeholder="представьтесь"
            class="reg__form-input"
            id="ProductReviewForm_author"
            type="text"
            v-model.trim="name"
          />
        </div>

        <!--  <div class="reviews__form-group">
                <div v-show="fieldHasError('email')" class="help-block error" id="ProductReviewForm_email_em_">{{ getFieldError('email') }}</div>
                <input placeholder="ваш email" class="reg__form-input" id="ProductReviewForm_email" type="text" v-model.trim="email">
             </div> -->

        <div class="reviews__form-group">
          <div
            v-show="fieldHasError('message')"
            class="help-block error"
            id="ProductReviewForm_content_em_"
          >
            {{ getFieldError('message') }}
          </div>
          <textarea
            placeholder="Текст сообщения"
            class="reg__form-input"
            id="ProductReviewForm_content"
            v-model.trim="message"
          ></textarea>
        </div>

        <div class="reviews__form-group reviews__form-group--rating">
          <span>оценка: </span>
          <star-rating
            :item-size="20"
            inactive-color="#d5d5d5"
            active-color="#2b2a29"
            :increment="1"
            v-model="rating"
          />
          <div
            v-show="fieldHasError('rating')"
            class="help-block error"
            id="ProductReviewForm_content_em_"
          >
            {{ getFieldError('rating') }}
          </div>
        </div>

        <div class="reviews__form-group">
          <vue-recaptcha
            v-if="isCaptcha"
            ref="mailus_recaptcha"
            @verify="onCaptchaVerified"
            @expired="onCaptchaExpired"
            size="invisible"
            :sitekey="captchaKey"
          />
        </div>

        <div class="reviews__form-group reviews__form-group--send">
          <input type="submit" value="Отправить" id="yt1" />
        </div>
      </form>
    </div>
    <div v-if="reviews.length > 0" class="reviews__left">
      <h2 id="reviews" class="reviews__title">Отзывы <span> ( {{ reviews.length }} )</span>
      </h2>
      <ul class="reviews__list">
        <li v-for="review in reviews" :key="review.review_id" class="reviews__item" itemprop="review" itemtype="http://schema.org/Review" itemscope>
          <div class="reviews__person" itemprop="author" itemtype="http://schema.org/Person" itemscope>
            <meta itemprop="name" :content="review.author" />
            <span>{{ review.author }}</span>
          </div>
          <div class="reviews__data">
            <span>{{ review.date_added }}</span>
          </div>
          <div class="reviews__rating" itemprop="reviewRating" itemtype="http://schema.org/Rating" itemscope>
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
          <div class="reviews__text">
            <p>{{ review.text }}</p>
          </div>
        </li>
      </ul>
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
      if (this.isCaptcha) {
        this.$refs.mailus_recaptcha.execute()
      } else {
        this.addReviewRequest().then((res) => {
          if (res === true) {
            this.show_form = false
          }
        })
      }
    },
    onCaptchaVerified(recaptchaToken) {
      this.$refs.mailus_recaptcha.reset()

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
      this.$refs.mailus_recaptcha.reset()
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

<style lang="scss"></style>
