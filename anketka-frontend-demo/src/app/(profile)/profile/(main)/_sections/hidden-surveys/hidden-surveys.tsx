import React, { CSSProperties, useState } from "react"
import cx from "classnames"
import { ChevronDown } from "lucide-react"
import { Button } from "@/shared/ui/button"
import { pluralize } from "@/shared/lib/pluralize"
import { SurveyDto } from "@/shared/api/back/_generated/models"
import styles from "./hidden-surveys.module.scss"

export interface IHiddenSurveys {
  className?: string
  style?: CSSProperties
  htmlId?: string
  surveys?: SurveyDto[]
  onUnhide: (survey: SurveyDto) => void
}

export const HiddenSurveys = (props: IHiddenSurveys) => {
  // Свёрнуто по умолчанию: служебный список не должен отвлекать от доступных опросов
  const [open, setOpen] = useState(false)

  const surveys = props.surveys ?? []
  if (surveys.length === 0) {
    return null
  }

  return (
    <div className={cx(styles.hidden_surveys, props.className)} style={props.style} id={props.htmlId}>
      <button type="button" className={styles.toggle} onClick={() => setOpen((it) => !it)} aria-expanded={open}>
        Скрытые опросы ({surveys.length})
        <ChevronDown size={20} className={cx(styles.chevron, open && styles.open)} />
      </button>

      {open && (
        <ul className={styles.list}>
          {surveys.map((survey) => (
            <li key={survey.id} className={styles.item}>
              <div className={styles.info}>
                <div className={styles.title}>
                  №{survey.id} · {survey.title}
                </div>
                <div className={styles.meta}>
                  {pluralize(survey.reward, {
                    one: (n) => `${n} балл`,
                    few: (n) => `${n} балла`,
                    many: (n) => `${n} баллов`,
                  })}{" "}
                  • {survey.duration} мин
                </div>
              </div>

              <Button type="secondary" size="small" onClick={() => props.onUnhide(survey)}>
                Вернуть
              </Button>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
