import Vue from 'vue'
import { isNil, has } from 'lodash'
import notify from '../components/partial/notify'

export default {
  getInlineState(codename = false, cb) {
    if (codename === false) {
      codename = Vue.prototype.$codename
    } else {
      codename = Vue.prototype.$codename + codename
    }
    if (!isNil(window['__' + codename + '__'])) {
      cb(window['__' + codename + '__'])
    }
  },
   makeRequest(data, cb) {
    const url = data.url
    delete data.url

    Vue.prototype.$http.post(url, data)
    .then(res => {
      cb(res)
    })
    .catch(error => {
      if (has(error.response, 'data')) {
        notify.messageHandler(error.response.data)
      }
      cb(false)
    });
  },
  makeGetRequest(data, cb) {
    const url = data.url
    delete data.url

    Vue.prototype.$http.get(url, {params: data})
    .then(res => {
      cb(res)
    })
    .catch(error => {
      if (has(error.response, 'data')) {
        notify.messageHandler(error.response.data)
      }
      cb(false)
    });
  },
}
