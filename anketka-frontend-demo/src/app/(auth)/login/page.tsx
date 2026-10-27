"use client"

import React, { FormEvent, useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import { Input } from "@/shared/ui/input"
import { Button } from "@/shared/ui/button"
import { PasswordInput } from "@/shared/ui/password-input"
import { api } from "@/shared/api"
import { getApiErrors } from "@/shared/lib/get-api-errors"
import { showSuccessToast } from "@/features/custom-toast"

const LoginPage = () => {
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})
  const router = useRouter()

  useEffect(() => {
    router.prefetch("/profile")
  }, [])

  const onSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    if (loading) return

    const formData = new FormData(e.target as HTMLFormElement)
    const inputs = Object.fromEntries(formData)

    try {
      setLoading(true)
      setErrors({})
      // apiKey из ответа сохранит successAuthMiddleware, редирект в кабинет сделает (auth)/layout
      await api.auth.login({
        email: inputs.email as string,
        password: inputs.password as string,
      })
      showSuccessToast("С возвращением!", "Загружаем доступные опросы")
    } catch (error) {
      setErrors(await getApiErrors(error))
    } finally {
      setLoading(false)
    }
  }

  return (
    <form className="auth_form" onSubmit={onSubmit}>
      <div className="header">
        <h1>Вход в личный кабинет Анкетки</h1>
      </div>

      <div className="inputs">
        <Input
          type="email"
          name="email"
          label="Ваш e-mail"
          placeholder="E-mail"
          required
          error={errors.email}
          autoComplete="email"
        />
        <PasswordInput
          name="password"
          label="Ваш пароль"
          placeholder="Пароль"
          required
          error={errors.password}
          autoComplete="current-password"
        />
      </div>

      {errors.global && <div className="global_error">{errors.global}</div>}

      <div className="actions">
        <Button htmlType="submit" loading={loading}>
          Войти
        </Button>

        <Button type="secondary" href="/register">
          Создать аккаунт
        </Button>
      </div>
    </form>
  )
}

export default LoginPage
