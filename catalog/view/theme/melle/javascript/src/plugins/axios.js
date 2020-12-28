import Vue from 'vue'
import axios from 'axios'
import { isObject } from 'lodash'
import notify from '@/components/partial/notify'

Vue.prototype.$http = axios

// Response interceptor
axios.interceptors.response.use(
  (response) => {
    const { data } = response

    if (!isObject(data)) {
      notify.messageHandler(
        { info: ['Мы обнаружили что запрос вернул неожиданный результат.'] },
        '_header'
      )
    }

    return response
  },
  (error) => {
    const { status } = error.response

    if (status != 200) {
      notify.messageHandler(
        { info: ['Мы обнаружили что во время работы произошла ошибка.'] },
        '_header'
      )
    }

    return Promise.reject(error)
  }
)
