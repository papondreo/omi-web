"use client"

import React from "react"
import cx from "classnames"
import styles from "./mobile-cards-swiper.module.scss"

interface MobileCardsSwiperProps {
  className?: string
  headerContent?: React.ReactNode
  children: React.ReactNode
  showNavigation?: boolean
  headerClassName?: string
  spaceBetween?: number
}

export const MobileCardsSwiper: React.FC<MobileCardsSwiperProps> = ({
  className,
  headerContent,
  headerClassName,
  children,
  showNavigation = true,
  spaceBetween = 16,
}) => {
  const childrenArray = React.Children.toArray(children)
  const hasSingleCard = childrenArray.length === 1

  return (
    <div className={cx(styles.mobile_cards_swiper, className)}>
      {headerContent && (
        <div className={cx(styles.swiper_header, headerClassName)}>
          <div className={styles.header_content}>{headerContent}</div>
        </div>
      )}

      <div className={styles.swiper_container}>
        <div className={cx(styles.swiper_wrapper, hasSingleCard && styles.single_card)}>
          {React.Children.map(children, (child, index) => (
            <div key={index} className={styles.swiper_slide}>
              {child}
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
