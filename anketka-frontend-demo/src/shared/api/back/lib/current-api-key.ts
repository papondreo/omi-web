import { LocalStorage } from "@/shared/lib/local-storage"

const KEY = process.env.NEXT_PUBLIC_PROJECT_PREFIX + ":api-key"

export const getAuthed = () => {
  return !!getApiKey()
}

export const getApiKey = (): string | undefined => {
  return LocalStorage.getItem(KEY)
}

export const setApiKey = (value: string | undefined) => {
  LocalStorage.setItem(KEY, value)
}

export const logout = async () => {
  setApiKey(undefined)
}

export const onApiKeyChange = (fn: (value: string | undefined) => void) => {
  return LocalStorage.subscribe(KEY, fn)
}
