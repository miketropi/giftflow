/**
 * AsyncEventHub is a class that allows you to register and emit events asynchronously.
 * 
 * @since 1.0.0
 * @author GiftFlow
 */

export class AsyncEventHub {
  constructor() {
    this.events = new Map()
  }

  on(eventName, handler) {
    if (!this.events.has(eventName)) {
      this.events.set(eventName, [])
    }

    this.events.get(eventName).push(handler)

    // unsubscribe
    return () => {
      const list = this.events.get(eventName) || []
      this.events.set(
        eventName,
        list.filter(h => h !== handler)
      )
    }
  }

  async emit(eventName, payload, options = {}) {
    const handlers = this.events.get(eventName) || []
    const {
      mode = 'series', // 'series' | 'parallel'
      stopOnFalse = true
    } = options

    if (mode === 'parallel') {
      const results = await Promise.all(
        handlers.map(h => h(payload))
      )

      if (stopOnFalse && results.includes(false)) {
        throw new Error(`AsyncEventHub: "${eventName}" blocked`)
      }

      return results
    }

    // series (default, safe)
    const results = []

    for (const handler of handlers) {
      const result = await handler(payload)
      results.push(result)

      if (stopOnFalse && result === false) {
        throw new Error(`AsyncEventHub: "${eventName}" blocked`)
      }
    }

    return results
  }
}
