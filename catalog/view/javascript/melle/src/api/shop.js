import Vue from 'vue'
import { isUndefined, has } from 'lodash'

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
    async makeRequest(data, cb) {
        let url = data.url
        delete data.url

        const res = await Vue.prototype.$http.post(url, data)
        .catch(error => {
            if (has(error.response, 'data')) {
                notify.messageHandler(error.response.data)
            }
        });

        if (res) { cb(res) }
        cb(false)
    },
    async makeGetRequest(data, cb) {
        let url = data.url
        delete data.url

        const res = await Vue.prototype.$http.get(url, {params: data})
        .catch(error => {
            if (has(error.response, 'data')) {
                notify.messageHandler(error.response.data)
            }
        });

        if (res) { cb(res) }
        cb(false)
    },
}
