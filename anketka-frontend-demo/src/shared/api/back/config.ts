import { successAuthMiddleware } from "./middleware"
import { Configuration, ConfigurationParameters, HTTPHeaders } from "./_generated"
import { isBrowser } from "@/shared/lib/is-browser"
import { getApiKey } from "./lib"

const base: ConfigurationParameters = {
  fetchApi: fetch,
  basePath: process.env.NEXT_PUBLIC_API_URL,
}

const browser: ConfigurationParameters = {
  ...base,
  credentials: "include",
  get headers(): HTTPHeaders {
    const apiKey = getApiKey()
    return apiKey ? { "X-API-KEY": apiKey } : {}
  },
  middleware: [{ post: successAuthMiddleware }],
}

const server: ConfigurationParameters = base

export class BackApiConfig extends Configuration {
  constructor(_options: Partial<{ basePath: string }> = {}) {
    super(isBrowser() ? browser : server)
  }
}
