"use client"

import { useEffect, useState } from "react"
import { getApiKey, onApiKeyChange } from "./current-api-key"

export const useAuthed = () => {
  const [authed, setAuthed] = useState(() => !!getApiKey())

  useEffect(() => {
    const unsubscribe = onApiKeyChange((apiKey) => setAuthed(!!apiKey))
    return () => unsubscribe()
  })

  return authed
}
