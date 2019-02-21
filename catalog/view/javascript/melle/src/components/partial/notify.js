import Vue from 'vue'
import { isArray, has } from 'lodash'
import store from '../../store/index'

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
                    // title: store.state.header.text_success,
                    text: element,
                    // duration: -1,
                })
            }, this)
        } else if (has(data, 'error') && isArray(data.error)) {
            data.error.forEach(function(element) {
                Vue.prototype.$notify({
                    group: codename,
                    type: 'warn',
                    // title: store.state.header.text_warning,
                    text: element,
                    // duration: -1,
                })
            }, this)
        }
    },
}
