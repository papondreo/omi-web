import { ResponseContext } from "../_generated"
import { setApiKey } from "../lib"

const baseUrl = process.env.NEXT_PUBLIC_API_URL!

export const successAuthMiddleware = async (context: ResponseContext): Promise<Response | void> => {
  let url = context.url

  if (url.startsWith(baseUrl)) {
    url = url.substring(baseUrl.length)
  }

  if ([`/api/login`, `/api/register`].includes(url) && context.init.method === "POST" && context.response.ok) {
    const response = await context.response.json()
    if (response.apiKey) {
      setApiKey(response.apiKey)
    }
  }

  if (context.response.status === 401) {
    setApiKey(undefined)
    return
  }
}
