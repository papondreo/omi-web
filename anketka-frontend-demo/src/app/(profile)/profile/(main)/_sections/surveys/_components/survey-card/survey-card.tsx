import React, { CSSProperties, memo } from "react"
import cx from "classnames"
import Link from "next/link"
import Image from "next/image"
import { X } from "lucide-react"
import { SurveyDto } from "@/shared/api/back"
import { Button } from "@/shared/ui/button"
import { pluralize } from "@/shared/lib/pluralize"
import { SURVEY_PAGE_ENABLED } from "@/shared/lib/features"
import images from "./images"
import styles from "./survey-card.module.scss"

export interface ISurveyCard {
  data: SurveyDto
  onHide?: () => void

  className?: string
  style?: CSSProperties
  htmlId?: string
}

export const SurveyCard = memo((props: ISurveyCard) => {
  const type: "fast" | "expensive" = props.data.reward! >= 50 ? "expensive" : "fast"

  const backgroundImages = {
    fast: images.fastBackground.src,
    expensive: images.expensiveBackground.src,
  }

  const href = SURVEY_PAGE_ENABLED ? `/profile/surveys/${props.data.id}` : props.data.url!

  return (
    <Link
      className={cx(styles.survey_card, styles[type], props.className)}
      style={{ ...props.style, backgroundImage: `url(${backgroundImages[type]})` }}
      id={props.htmlId}
      href={href}
      target={SURVEY_PAGE_ENABLED ? undefined : "_blank"}
    >
      {props.onHide && (
        <button
          type="button"
          className={styles.hide_button}
          aria-label="Скрыть опрос"
          title="Скрыть опрос"
          onClick={(e) => {
            // Вся карточка — ссылка: без этого вместе со скрытием откроется опрос
            e.preventDefault()
            e.stopPropagation()
            props.onHide!()
          }}
        >
          <X size={20} />
        </button>
      )}

      <div className={styles.head}>
        <div className={styles.icon}>
          {type === "fast" && <Image src={images.fast.src} alt="Быстрый опрос" width={24} height={24} />}
          {type === "expensive" && <Image src={images.expensive.src} alt="Дорогой опрос" width={24} height={24} />}
        </div>
        <div className={styles.text}>
          <div className={styles.number}>
            №{props.data.id} · {type === "fast" ? "Быстрый опрос" : "Дорогой опрос"}
          </div>
          <div className={styles.title}>{props.data.title}</div>
          {props.data.description && <div className={styles.description}>{props.data.description}</div>}
        </div>
      </div>

      <div className={styles.actions}>
        <div className={styles.reward}>
          {pluralize(props.data.reward, {
            one: (n) => `${n} балл`,
            few: (n) => `${n} балла`,
            many: (n) => `${n} баллов`,
          })}{" "}
          • {props.data.duration} мин
        </div>
        <Button type="secondary" className={styles.button}>
          Пройти опрос
        </Button>
      </div>
    </Link>
  )
})
