import { BackApi } from "@/shared/api/back"

export const api = new BackApi()

if (typeof window !== "undefined") {
  ;(window as any).api = api
}
