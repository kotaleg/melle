import Vue from 'vue'
import { isArray, has } from 'lodash'
import store from '../../store/index'

export default {
    messageHandler(data) {
        if (has(data, 'success') && isArray(data.success)) {
            data.success.forEach(function(element) {
                Vue.prototype.$notify({
                    group: Vue.prototype.$codename,
                    type: 'success',
                    title: store.state.shop.text_success,
                    text: element,
                })
            }, this)
        } else if (has(data, 'error') && isArray(data.error)) {
            data.error.forEach(function(element) {
                Vue.prototype.$notify({
                    group: Vue.prototype.$codename,
                    type: 'warn',
                    title: store.state.shop.text_warning,
                    text: element,
                })
            }, this)
        }
    },
}
