"use client"

import React, { FormEvent, useEffect, useState } from "react"
import { useRouter } from "next/navigation"
import { Input } from "@/shared/ui/input"
import { Button } from "@/shared/ui/button"
import { PasswordInput } from "@/shared/ui/password-input"
import { api } from "@/shared/api"
import { getApiErrors } from "@/shared/lib/get-api-errors"
import { yandexMetrika } from "@/shared/lib/yandex-metrika"
import { showSuccessToast } from "@/features/custom-toast"

const SignUpPage = () => {
  const [loading, setLoading] = useState(false)
  const [errors, setErrors] = useState<Record<string, string>>({})
  const router = useRouter()

  useEffect(() => {
    yandexMetrika.reachGoal("register_view")

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
      await api.auth.register({
        email: inputs.email as string,
        password: inputs.password as string,
        passwordRepeat: inputs.passwordRepeat as string,
      })
      yandexMetrika.reachGoal("register_submit")
      showSuccessToast("Аккаунт создан", "Добро пожаловать в Анкетку")
    } catch (error) {
      setErrors(await getApiErrors(error))
    } finally {
      setLoading(false)
    }
  }

  return (
    <form className="auth_form" onSubmit={onSubmit}>
      <div className="header">
        <h1>Регистрация для участия в опросах</h1>
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
          label="Придумайте пароль"
          placeholder="Пароль"
          required
          error={errors.password}
          autoComplete="new-password"
        />
        <PasswordInput
          name="passwordRepeat"
          label="Повторите пароль"
          placeholder="Введите пароль ещё раз"
          required
          error={errors.passwordRepeat}
          autoComplete="new-password"
        />
      </div>

      {errors.global && <div className="global_error">{errors.global}</div>}

      <div className="actions">
        <Button htmlType="submit" loading={loading}>
          Зарегистрироваться
        </Button>
        <Button type="secondary" href="/login">
          Уже есть аккаунт
        </Button>
      </div>
    </form>
  )
}

export default SignUpPage
