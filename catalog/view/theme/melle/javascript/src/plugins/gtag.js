import { isString, has } from 'lodash'

const isGtag = typeof gtag === 'function'
const isGtagProductData = typeof gtag_product_data === 'object'

function findProductById(productId) {
  for (let key in gtag_product_data) {
    const product_data = gtag_product_data[key]
    if (
      typeof product_data['id'] === 'string' &&
      product_data['id'] === productId
    ) {
      return product_data
    }
  }
}

function filterProductItem(item) {
  return has(item, 'productId') && has(item, 'name')
}

function transformProductItem(item) {
  const prepared = { id: item.productId, name: item.name }

  if (has(item, 'h1')) {
    prepared.name = item.h1
  }

  if (has(item, 'manufacturer')) {
    prepared.brand = item.manufacturer
  }

  if (has(item, 'stock.price') && isString(item.stock.price)) {
    const priceValue = parseFloat(item.stock.price.replace(/\s/g, ''))
    prepared.price = priceValue
  }

  if (has(item, 'stock.maxQuantity')) {
    prepared.quantity = item.stock.maxQuantity
  }

  return prepared
}

function filterProductListItem(item) {
  return has(item, 'product_id') && has(item, 'name')
}

function transformProductListItem(item) {
  const prepared = { id: item.product_id, name: item.name }

  if (has(item, 'h1')) {
    prepared.name = item.h1
  }

  if (has(item, 'manufacturer')) {
    prepared.brand = item.manufacturer
  }

  if (
    has(item, 'default_values.price') &&
    isString(item.default_values.price)
  ) {
    const priceValue = parseFloat(item.default_values.price.replace(/\s/g, ''))
    prepared.price = priceValue
  }

  if (has(item, 'default_values.max_quantity')) {
    prepared.quantity = item.default_values.max_quantity
  }

  return prepared
}

function filterSearchItem(item) {
  return has(item, 'productId') && has(item, 'name')
}

function transformSearchItem(item) {
  const prepared = {
    item_id: item.productId,
    item_name: item.name,
    currency: 'RUR',
  }

  if (has(item, 'h1') && item.h1) {
    prepared.name = item.h1
  }

  if (has(item, 'price') && item.price > 0) {
    prepared.price = item.price
  }

  if (has(item, 'special') && item.special !== false && item.special > 0) {
    prepared.price = item.special
  }

  return prepared
}

export default {
  productClickRaw(productId) {
    if (!isGtag) {
      return
    }

    gtag('event', 'select_content', {
      content_type: 'product',
      items: [{ id: productId }],
    })
  },
  productClick(product) {
    if (!isGtag) {
      return
    }

    const transformed_items = [product]
      .filter((item) => filterProductListItem(item))
      .map((item) => transformProductListItem(item))

    gtag('event', 'select_content', {
      content_type: 'product',
      items: transformed_items,
    })
  },
  productView(product) {
    if (!isGtag) {
      return
    }

    const transformed_items = [product]
      .filter((item) => filterProductItem(item))
      .map((item) => transformProductItem(item))

    gtag('event', 'view_item', { items: transformed_items })
  },
  search(searchTerm) {
    if (!isGtag) {
      return
    }

    gtag('event', 'search', { search_term: searchTerm })
  },
  viewSearchResults(items) {
    if (!isGtag) {
      return
    }

    const transformed_items = items
      .filter((item) => filterSearchItem(item))
      .map((item) => transformSearchItem(item))

    gtag('event', 'view_search_results', { items: transformed_items })
  },
  searchResultClick(item) {
    if (!isGtag) {
      return
    }

    const transformed_items = [item]
      .filter((item) => filterSearchItem(item))
      .map((item) => transformSearchItem(item))

    gtag('event', 'select_content', {
      content_type: 'product',
      items: transformed_items,
    })
  },
}
