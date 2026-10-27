import React from "react"
import { Metadata } from "next"
import { Button } from "@/shared/ui/button"
import { typograf } from "@/shared/lib/typograf"
import styles from "./page.module.scss"

export const metadata: Metadata = {
  title: "Анкетка — заработок на опросах",
  description: "Проходите опросы и получайте вознаграждение. Демо-версия.",
  alternates: {
    canonical: "/",
  },
}

const steps = [
  { title: "Регистрация", text: "Создайте аккаунт по e-mail — ключ доступа сохранится в браузере" },
  { title: "Опросы", text: "В личном кабинете появятся опросы, которые подходят именно вам" },
  { title: "Вознаграждение", text: "За каждый пройденный опрос начисляются баллы" },
]

const HomePage = () => {
  return (
    <div className={styles.page}>
      <section className={styles.hero}>
        <h1>{typograf("Зарабатывайте на опросах в свободное время")}</h1>
        <p>{typograf("Это демо-страница: она рендерится на сервере, а текст проходит через типограф")}</p>

        <div className={styles.actions}>
          <Button size="large" href="/register">
            Начать
          </Button>
          <Button size="large" type="secondary" href="/login">
            Уже есть аккаунт
          </Button>
        </div>
      </section>

      <section className={styles.steps}>
        {steps.map((it, index) => (
          <div key={it.title} className={styles.step}>
            <div className={styles.step_number}>{index + 1}</div>
            <h3>{it.title}</h3>
            <p>{typograf(it.text)}</p>
          </div>
        ))}
      </section>
    </div>
  )
}

export default HomePage
