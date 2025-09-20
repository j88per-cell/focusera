import { ref } from 'vue'

export function useCart() {
  const loading = ref(false)
  const cart = ref(null)
  const error = ref(null)

  async function fetchCart() {
    loading.value = true
    error.value = null
    try {
      const res = await fetch('/cart', { headers: { 'Accept': 'application/json' } })
      if (!res.ok) throw new Error(`Cart fetch failed: ${res.status}`)
      cart.value = await res.json()
    } catch (e) {
      error.value = e
    } finally {
      loading.value = false
    }
  }

  async function addItem({ photo_id, product_code = null, variant = null, quantity = 1, unit_price_base = 0 }) {
    loading.value = true
    error.value = null
    try {
      const res = await fetch('/cart/items', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ photo_id, product_code, variant, quantity, unit_price_base }),
      })
      if (!res.ok) throw new Error(`Add item failed: ${res.status}`)
      const item = await res.json()
      await fetchCart()
      return item
    } catch (e) {
      error.value = e
      throw e
    } finally {
      loading.value = false
    }
  }

  return { loading, cart, error, fetchCart, addItem }
}

