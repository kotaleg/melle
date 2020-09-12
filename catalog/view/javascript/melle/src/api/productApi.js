import { isArray, isObject, has, sortBy } from 'lodash'

export default {
  clearOptions(options) {
    let new_options = []
    if (isArray(options)) {
      options.forEach((value, index) => {
        if (isObject(value)) {
          if (has(value, 'option_a') && has(value, 'option_value_a')) {
            new_options.push({
              option_a: value.option_a,
              option_value_a: value.option_value_a,
            })
          }
        }
      })
    }

    if (new_options.length > 0) {
      new_options = sortBy(new_options, ['option_a'])
    }

    return new_options
  },
}
