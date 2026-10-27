"use client"

import React from "react"
import { useParams } from "next/navigation"
import { api } from "@/shared/api"
import { Button } from "@/shared/ui/button"
import { Skeleton } from "@/shared/ui/skeleton"
import { pluralize } from "@/shared/lib/pluralize"
import { typograf } from "@/shared/lib/typograf"
import styles from "./page.module.scss"

const SurveyPage = () => {
  const params = useParams<{ id: string }>()
  const survey = api.survey.useSurvey(Number(params.id), { shouldRetryOnError: false })

  if (survey.error) {
    return (
      <div className={styles.survey_page}>
        <h1>{survey.error.error ?? "Не удалось загрузить опрос"}</h1>
        <Button type="secondary" href="/profile" className={styles.back}>
          Ко всем опросам
        </Button>
      </div>
    )
  }

  if (!survey.data) {
    return (
      <div className={styles.survey_page}>
        <Skeleton height={44} width="60%" borderRadius={8} />
        <Skeleton height={20} width="80%" borderRadius={4} />
        <Skeleton height={56} width={220} borderRadius={999} />
      </div>
    )
  }

  return (
    <div className={styles.survey_page}>
      <div className={styles.number}>Опрос №{survey.data.id}</div>
      <h1>{typograf(survey.data.title)}</h1>
      <p className={styles.description}>{typograf(survey.data.description)}</p>

      <div className={styles.meta}>
        <div className={styles.meta_item}>
          {pluralize(survey.data.reward, {
            one: (n) => `${n} балл`,
            few: (n) => `${n} балла`,
            many: (n) => `${n} баллов`,
          })}
        </div>
        <div className={styles.meta_item}>{survey.data.duration} мин</div>
      </div>

      <div className={styles.actions}>
        <Button size="large" href={survey.data.url!} target="_blank">
          Пройти опрос
        </Button>
        <Button size="large" type="secondary" href="/profile">
          Ко всем опросам
        </Button>
      </div>
    </div>
  )
}

export default SurveyPage
