import Vue from 'vue'
import { isEmpty, isNumber, forEach, trim, has } from 'lodash'

export default {
  initQuery(to, from) {
    let storeQuery = {}
    let filterQuery = {}
    let storePath = ''

    // FILL WITH CURRENT QUERIES
    if (!isEmpty(to.query)) {
      forEach(to.query, (v, k) => {
        if (this.getDefaultQueryParams().includes(k)) {
          filterQuery[k] = v
        } else {
          storeQuery[k] = v
        }
      })
    }

    Vue.prototype.$filterQuery = filterQuery
    Vue.prototype.$storeQuery = storeQuery
    Vue.prototype.$storePath = storePath = to.path

    return { storeQuery, storePath, filterQuery }
  },
  prepareFullQuery(filter_data) {
    let query = Object.assign({}, Vue.prototype.$storeQuery)

    forEach(filter_data, (v, k) => {
      if (this.getDefaultQueryParams().includes(k)) {
        if (k === 'act' && v === true) {
          query[k] = 1
        }
        if (k === 'neww' && v === true) {
          query[k] = 1
        }
        if (k === 'hit' && v === true) {
          query[k] = 1
        }
        if (k === 'search' && v !== null) {
          query[k] = trim(v)
        }

        if (k === 'min_den' && v !== '') {
          query[k] = v
        }
        if (k === 'max_den' && v !== '' && v !== 0) {
          query[k] = v
        }
        if (k === 'min_price' && v !== '') {
          query[k] = v
        }
        if (k === 'max_price' && v !== '' && v !== 0) {
          query[k] = v
        }
        if (k === 'material' && v !== null && v !== '') {
          if (has(v, 'value')) {
            query[k] = trim(v.value)
          }
        }
        if (k === 'color' && v !== null && v !== '') {
          if (has(v, 'value')) {
            query[k] = trim(v.value)
          }
        }
        if (k === 'size' && v !== null && v !== '') {
          if (has(v, 'value')) {
            query[k] = trim(v.value)
          }
        }
        if (k === 'manufacturers' && v !== null) {
          let m = ''
          forEach(v, (man_v) => {
            if (man_v.checked === true) {
              m += `${man_v.value},`
            }
          })
          m = trim(m, ',')
          if (m !== '') {
            query[k] = trim(m)
          }
        }
        if (k === 'page' && isNumber(v)) {
          if (v > 1) {
            query[k] = v
          }
        }
      }
    })

    return query
  },
  getDefaultQueryParams() {
    return [
      'hit',
      'act',
      'neww',
      'min_den',
      'max_den',
      'min_price',
      'max_price',
      'color',
      'material',
      'size',
      'search',
      'manufacturers',
      'page',
    ]
  },
}
