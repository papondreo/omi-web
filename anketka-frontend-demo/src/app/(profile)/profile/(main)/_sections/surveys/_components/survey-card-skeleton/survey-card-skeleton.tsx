"use client"

import React from "react"
import cx from "classnames"
import styles from "./survey-card-skeleton.module.scss"

interface SurveyCardSkeletonProps {
  className?: string
}

export const SurveyCardSkeleton: React.FC<SurveyCardSkeletonProps> = ({ className }) => {
  return (
    <div className={cx(styles.survey_card_skeleton, className)}>
      <div className={styles.skeleton_title}></div>
      <div className={styles.skeleton_button}></div>
      <div className={styles.skeleton_description}></div>
    </div>
  )
}
