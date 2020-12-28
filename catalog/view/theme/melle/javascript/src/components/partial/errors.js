export default class Errors {
  constructor() {
    this.errors = {}
  }

  has(field) {
    return field in this.errors
  }

  any() {
    return Object.keys(this.errors).length > 0
  }

  first(field) {
    if (this.has(field)) {
      return this.errors[field]
    }
  }

  record(errors) {
    this.errors = errors
  }

  clear(field) {
    if (this.has(field)) {
      delete this.errors[field]
      return
    }

    this.errors = {}
  }
}
