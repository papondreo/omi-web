"use client"

import React from "react"
import { Surveys } from "./_sections/surveys"
import { api } from "@/shared/api"
import { pluralize } from "@/shared/lib/pluralize"
import styles from "./page.module.scss"

const ProfilePage = () => {
  const surveys = api.survey.useSurveys({
    refreshInterval: 0,
    revalidateOnFocus: false,
    revalidateOnReconnect: true,
  })

  const count = surveys.data?.surveys?.length ?? 0
  const showEmptyMessage = !surveys.isLoading && !surveys.error && count === 0

  return (
    <div className={styles.main_content}>
      <div className={styles.header}>
        <h1>Личный кабинет</h1>
        {!surveys.isLoading && !surveys.error && (
          <p>
            {pluralize(count, {
              one: (n) => `Доступен ${n} опрос`,
              few: (n) => `Доступно ${n} опроса`,
              many: (n) => `Доступно ${n} опросов`,
            })}
          </p>
        )}
      </div>

      {surveys.error && <div className={styles.error}>{surveys.error.error ?? "Не удалось загрузить опросы"}</div>}

      {showEmptyMessage ? (
        <div className={styles.empty_message}>
          <h3>Пока нет новых опросов</h3>
          <h3 className={styles.accent}>Мы сообщим, как только появятся</h3>
        </div>
      ) : (
        <Surveys data={surveys.data} isLoading={surveys.isLoading} />
      )}
    </div>
  )
}

export default ProfilePage
