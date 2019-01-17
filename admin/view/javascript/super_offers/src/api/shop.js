import Vue from 'vue'
import { isUndefined } from 'lodash'

export default {
    getInlineState(cb, errorCb) {
        if (!isUndefined(window['__' + Vue.prototype.$codename + '__'])) {
            cb(window['__' + Vue.prototype.$codename + '__'])
        } else {
            errorCb()
        }
    },
    postSettingData(data, cb) {
        Vue.prototype.$http.post(data.url, data).then(response => {
            cb(response)
        })
    },
    makeRequest(data, cb) {
        Vue.prototype.$http.post(data.url, data).then(response => {
            cb(response)
        })
    },
}
