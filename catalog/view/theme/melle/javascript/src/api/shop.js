import Vue from 'vue'
import {isObject, has} from 'lodash'
import notify from '@/components/partial/notify'

export default {
  getInlineState(codename = false, cb) {
    let code
    if (codename === false) {
      code = Vue.prototype.$codename
    } else {
      code = Vue.prototype.$codename + codename
    }
    if (typeof window['__' + code + '__'] == 'string') {
      try {
        const json = JSON.parse(window['__' + code + '__'])
        cb(json)
      } catch {}
    }
    cb({})
  },
  makeRequest(data, cb) {
    const url = data.url
    delete data.url

    Vue.prototype.$http
      .post(url, data)
      .then((res) => {
        cb(res)
      })
      .catch((error) => {
        if (isObject(error.response) && has(error.response, 'data')) {
          notify.messageHandler(error.response.data)
        }
        cb(false)
      })
  },
  makeGetRequest(data, cb) {
    const url = data.url
    delete data.url

    Vue.prototype.$http
      .get(url, { params: data })
      .then((res) => {
        cb(res)
      })
      .catch((error) => {
        if (isObject(error.response) && has(error.response, 'data')) {
          notify.messageHandler(error.response.data)
        }
        cb(false)
      })
  },
}
