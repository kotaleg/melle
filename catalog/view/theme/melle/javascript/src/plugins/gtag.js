import { isString, has } from 'lodash'

const isGtag = typeof gtag === 'function'

function transformProduct({ product, list_name, category_name }) {
  let data = {
    id: product.product_id,
    name: product.name,
  }

  if (has(product, 'manufacturer')) {
    data.brand = product.manufacturer
  }

  if (has(product, 'default_values.max_quantity')) {
    data.quantity = parseInt(product.default_values.max_quantity)
  }

  if (
    has(product, 'default_values.price') &&
    isString(product.default_values.price)
  ) {
    data.price = parseFloat(product.default_values.price.replace(/\s/g, ''))
  }

  if (list_name) {
    data.list_name = list_name
  }

  if (category_name) {
    data.category = category_name
  }

  return data
}

export default {
  productClick({ product, list_name = '', category_name = '' }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'select_content', {
      content_type: 'product',
      items: [transformProduct({ product, list_name, category_name })],
    })
  },
  productImpressions({ products, list_name = '', category_name = '' }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'view_item_list', {
      items: products.map((product, index) => {
        let transformed = transformProduct({
          product,
          list_name,
          category_name,
        })
        transformed.list_position = index + 1
        return transformed
      }),
    })
  },
  viewProduct({ product }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'view_item', { items: [transformProduct({ product })] })
  },
  addToCart({ product, category_name = '' }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'add_to_cart', {
      items: [transformProduct({ product, category_name })],
    })
  },
  removeFromCart({ products }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'remove_from_cart', {
      items: products.map((product) => transformProduct({ product })),
    })
  },
  viewCart({ products }) {
    if (!isGtag) {
      return
    }

    gtag('event', 'begin_checkout', {
      items: products.map((product) => transformProduct({ product })),
    })
  },
}
