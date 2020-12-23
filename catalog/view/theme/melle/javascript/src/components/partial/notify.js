import Vue from 'vue'
import store from './../../store'
import { isArray, has, forEach } from 'lodash'

export default {
  messageHandler(data, codename = false) {
    if (codename === false) {
      codename = Vue.prototype.$codename
    } else {
      // sidebar overfloat fix
      if (codename == '_header') {
        if (store.getters['header/isSidebarOpened']) {
          codename = '_sidebar'
        }
      }

      codename = `${Vue.prototype.$codename}${codename}`
    }

    if (has(data, 'success') && isArray(data.success)) {
      forEach(data.success, (element) => {
        Vue.prototype.$notify({
          group: codename,
          type: 'success',
          text: element,
        })
      })
    } else if (has(data, 'error') && isArray(data.error)) {
      forEach(data.error, (element) => {
        Vue.prototype.$notify({
          group: codename,
          type: 'warn',
          text: element,
        })
      })
    } else if (has(data, 'info') && isArray(data.info)) {
      forEach(data.info, (element) => {
        Vue.prototype.$notify({
          group: codename,
          type: 'info',
          text: element,
        })
      })
    }
  },
}
