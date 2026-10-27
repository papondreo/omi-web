export class LocalStorage {
  static getItem<T = string>(key: string): T | undefined {
    if (typeof localStorage === "undefined" || !localStorage) return undefined

    return localStorage.getItem(key) as any
  }

  static setItem(key: string, value: string | undefined): void {
    if (typeof localStorage === "undefined" || !localStorage) return undefined

    if (value !== undefined) {
      localStorage.setItem(key, value)
    } else {
      localStorage.removeItem(key)
    }
    LocalStorage.notify(key, value)
  }

  static removeItem(key: string): void {
    if (typeof localStorage === "undefined" || !localStorage) return undefined

    localStorage.removeItem(key)
    LocalStorage.notify(key, undefined)
  }

  static getJson<T = any>(key: string): T | undefined {
    const raw = LocalStorage.getItem(key)
    if (!raw) return undefined
    return JSON.parse(raw)
  }

  static setJSON(key: string, json: any): void {
    const value = JSON.stringify(json)
    LocalStorage.setItem(key, value)
  }

  private static subscribers: { [key: string]: ((value: string | undefined) => void)[] } = {}

  static subscribe = (key: string, fn: (value: string | undefined) => void) => {
    const unsubscribeFromCurrentPage = LocalStorage.subscribeToCurrentTab(key, fn)
    const unsubscribeFromOtherTabs = LocalStorage.subscribeToOtherTabs(key, fn)

    return () => {
      unsubscribeFromCurrentPage()
      unsubscribeFromOtherTabs()
    }
  }

  private static notify = (key: string, value: string | undefined) => {
    for (const it of LocalStorage.subscribers[key] || []) {
      it(value)
    }
  }

  static subscribeToCurrentTab = (key: string, fn: (value: string | undefined) => void) => {
    if (!LocalStorage.subscribers[key]) {
      LocalStorage.subscribers[key] = []
    }

    LocalStorage.subscribers[key].push(fn)

    return () => {
      LocalStorage.subscribers[key] = LocalStorage.subscribers[key]?.filter((it) => it !== fn)
    }
  }

  static subscribeToOtherTabs = (key: string, fn: (value: string | undefined) => void): (() => void) => {
    if (typeof window === "undefined") return () => {}

    const listener = (e: StorageEvent) => {
      // after localStorage.clear()
      if (e.key == null) {
        fn(undefined)
      }

      if (e.key === key) {
        fn(e.newValue || undefined)
      }
    }

    window.addEventListener("storage", listener)

    return () => {
      window.removeEventListener("storage", listener)
    }
  }
}
