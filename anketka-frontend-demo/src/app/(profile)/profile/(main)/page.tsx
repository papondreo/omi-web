"use client"

import React from "react"
import { Surveys } from "./_sections/surveys"
import { HiddenSurveys } from "./_sections/hidden-surveys"
import { api } from "@/shared/api"
import { SurveyDto } from "@/shared/api/back"
import { pluralize } from "@/shared/lib/pluralize"
import { getApiErrors } from "@/shared/lib/get-api-errors"
import { showActionToast, showErrorToast } from "@/features/custom-toast"
import styles from "./page.module.scss"

const withoutSurvey = (list: SurveyDto[] | undefined, survey: SurveyDto) =>
  (list ?? []).filter((it) => it.id !== survey.id)
const withSurvey = (list: SurveyDto[] | undefined, survey: SurveyDto) => [...withoutSurvey(list, survey), survey]

const ProfilePage = () => {
  const surveys = api.survey.useSurveys({
    refreshInterval: 0,
    revalidateOnFocus: false,
    revalidateOnReconnect: true,
  })
  const hiddenSurveys = api.survey.useHiddenSurveys({
    refreshInterval: 0,
    revalidateOnFocus: false,
  })

  // Оптимистично переносим опрос между списками, не дожидаясь сервера: интерфейс откликается сразу.
  // После запроса перезапрашиваем оба списка — при ошибке это вернёт всё как было
  const moveSurvey = async (survey: SurveyDto, hide: boolean) => {
    surveys.mutate(
      (it) => it && { surveys: hide ? withoutSurvey(it.surveys, survey) : withSurvey(it.surveys, survey) },
      { revalidate: false },
    )
    hiddenSurveys.mutate(
      (it) => ({ surveys: hide ? withSurvey(it?.surveys, survey) : withoutSurvey(it?.surveys, survey) }),
      { revalidate: false },
    )

    try {
      if (hide) {
        await api.survey.hideSurvey(survey.id!)
        showActionToast("Опрос скрыт", { label: "Вернуть", onClick: () => moveSurvey(survey, false) })
      } else {
        await api.survey.unhideSurvey(survey.id!)
      }
    } catch (error) {
      const errors = await getApiErrors(error)
      showErrorToast(hide ? "Не удалось скрыть опрос" : "Не удалось вернуть опрос", errors.global)
    } finally {
      surveys.mutate()
      hiddenSurveys.mutate()
    }
  }

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
        <Surveys data={surveys.data} isLoading={surveys.isLoading} onHide={(survey) => moveSurvey(survey, true)} />
      )}

      <HiddenSurveys surveys={hiddenSurveys.data?.surveys} onUnhide={(survey) => moveSurvey(survey, false)} />
    </div>
  )
}

export default ProfilePage
