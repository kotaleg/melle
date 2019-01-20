import { has as __has } from 'lodash'

export default class Errors {

    constructor() {
        this.errors = {}
    }

    has(field) {
        return __has(this.errors, field)
    }

    any() {
        return Object.keys(this.errors).length > 0;
    }

    first(field) {
        if (__has(this.errors, field)) {
            return this.errors[field]
        }
    }

    record(errors) {
        this.errors = errors
    }

    clear(field) {
        if (field) {
            delete this.errors[field]
            return
        }

        this.errors = {}
    }
}

