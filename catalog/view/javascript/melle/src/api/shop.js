import Vue from 'vue'
import { isUndefined } from 'lodash'

export default {
    getInlineState(codename = false, cb) {
        if (codename === false) {
            codename = Vue.prototype.$codename
        } else {
            codename = Vue.prototype.$codename + codename
        }
        if (!isUndefined(window['__' + codename + '__'])) {
            cb(window['__' + codename + '__'])
        }
    },
    postSettingData(data, cb) {
        let url = data.url
        delete data.url
        Vue.prototype.$http.post(url, data).then(response => {
            cb(response)
        })
    },
    makeRequest(data, cb) {
        let url = data.url
        delete data.url
        Vue.prototype.$http.post(url, data).then(response => {
            cb(response)
        })
    },
}
