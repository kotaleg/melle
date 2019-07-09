import Vue from 'vue'
import { isUndefined, has } from 'lodash'
import notify from '../components/partial/notify'

export default {
    getInlineState(cb) {
        const codename = Vue.prototype.$codename
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
}
