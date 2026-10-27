"use client"

import React, { useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import { useAuthed } from "@/shared/api/back"

export default function ProfileLayout({ children }: { children: React.ReactNode }) {
  const authed = useAuthed()
  const router = useRouter()
  // Ключ лежит в localStorage, на сервере его нет: до монтирования ничего не рендерим,
  // иначе серверный и клиентский HTML разойдутся
  const [mounted, setMounted] = useState(false)

  useEffect(() => {
    setMounted(true)
  }, [])

  useEffect(() => {
    if (mounted && !authed) {
      router.replace("/login")
    }
  }, [mounted, authed, router])

  if (!mounted || !authed) {
    return null
  }

  return <>{children}</>
}
