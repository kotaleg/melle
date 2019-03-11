import Vue from 'vue'
import { isArray, has } from 'lodash'

export default {
    messageHandler(data, codename = false) {
        if (codename === false) {
            codename = Vue.prototype.$codename
        } else {
            codename = `${Vue.prototype.$codename}${codename}`
        }

        if (has(data, 'success') && isArray(data.success)) {
            data.success.forEach(function(element) {
                Vue.prototype.$notify({
                    group: codename,
                    type: 'success',
                    text: element,
                })
            }, this)
        } else if (has(data, 'error') && isArray(data.error)) {
            data.error.forEach(function(element) {
                Vue.prototype.$notify({
                    group: codename,
                    type: 'warn',
                    text: element,
                })
            }, this)
        }
    },
}
