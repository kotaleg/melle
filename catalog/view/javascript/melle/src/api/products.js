import Vue from 'vue'
import { isUndefined } from 'lodash'

export default {
    async getFromServer(data, cb) {
        let params = !isUndefined(data.params) ? data.params : {}
        const response = await Vue.prototype.$http.post(data.url, params)

        if (!isUndefined(response.data)) {
            cb(response.data)
        } else {
            cb(response.error)
        }
    },
}
