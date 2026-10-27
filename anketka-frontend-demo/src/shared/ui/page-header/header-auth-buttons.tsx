"use client"

import React from "react"
import { useRouter } from "next/navigation"
import { Button } from "@/shared/ui/button"
import { logout, useAuthed } from "@/shared/api/back"
import styles from "./page-header.module.scss"

export const HeaderAuthButtons = () => {
  const authed = useAuthed()
  const router = useRouter()

  const onLogout = async () => {
    await logout()
    router.replace("/")
  }

  if (authed) {
    return (
      <div className={styles.buttons}>
        <Button type="flat" size="small" href="/profile">
          Опросы
        </Button>
        <Button type="secondary" size="small" onClick={onLogout}>
          Выйти
        </Button>
      </div>
    )
  }

  return (
    <div className={styles.buttons}>
      <Button type="flat" size="small" href="/login">
        Войти
      </Button>
      <Button size="small" href="/register">
        Регистрация
      </Button>
    </div>
  )
}
