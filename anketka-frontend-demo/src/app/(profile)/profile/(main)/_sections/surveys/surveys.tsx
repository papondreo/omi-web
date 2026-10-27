import React, { CSSProperties, useEffect, useState } from "react"
import cx from "classnames"
import { SurveyCard } from "./_components/survey-card"
import { SurveyCardSkeleton } from "./_components/survey-card-skeleton/survey-card-skeleton"
import { CardsSwiper } from "@/shared/ui/cards-swiper"
import { GetSurveysResponseDto } from "@/shared/api/back/_generated/models"
import styles from "./surveys.module.scss"

export interface ISurveys {
  className?: string
  style?: CSSProperties
  htmlId?: string
  data?: GetSurveysResponseDto
  isLoading?: boolean
}

const MOBILE_WIDTH = 768

export const Surveys = (props: ISurveys) => {
  const [isMobile, setIsMobile] = useState(false)

  useEffect(() => {
    const checkIsMobile = () => {
      setIsMobile(window.innerWidth <= MOBILE_WIDTH)
    }

    checkIsMobile()
    window.addEventListener("resize", checkIsMobile)

    return () => {
      window.removeEventListener("resize", checkIsMobile)
    }
  }, [])

  const surveyData = props.data?.surveys || []
  const isLoading = props.isLoading ?? false

  if (!isLoading && surveyData.length === 0) {
    return null
  }

  return (
    <div className={cx(styles.surveys, props.className)} style={props.style} id={props.htmlId}>
      <CardsSwiper
        headerContent={<h3 className={styles.section_title}>Доступные опросы</h3>}
        headerClassName={styles.header_content}
        useMobileLayout={isMobile}
      >
        {isLoading
          ? // Показываем 3 скелетона во время загрузки
            Array.from({ length: 3 }).map((_, index) => <SurveyCardSkeleton key={`skeleton-${index}`} />)
          : surveyData.map((data) => <SurveyCard key={data.id} data={data} />)}
      </CardsSwiper>
    </div>
  )
}
