"use client"

import React, { useRef, useState, useEffect } from "react"
import cx from "classnames"
import { MoveLeft, MoveRight } from "lucide-react"
import { Button } from "../button/button"
import { MobileCardsSwiper } from "./components"
import styles from "./cards-swiper.module.scss"

interface CardsSwiperProps {
  className?: string
  /** Гибкий контент для заголовка (любой ReactNode) */
  headerContent?: React.ReactNode
  /** Дочерние элементы (карточки) */
  children: React.ReactNode
  /** Показывать ли навигационные кнопки */
  showNavigation?: boolean
  /** Показывать ли навигационные кнопки на мобильных устройствах */
  showMobileNavigation?: boolean
  /** Дополнительный класс для контейнера заголовка */
  headerClassName?: string
  /** Расстояние между слайдами */
  spaceBetween?: number
  /** Принудительно использовать мобильный layout */
  useMobileLayout?: boolean
  /** Принудительно отключить мобильный layout и использовать десктопный */
  forceDesktopLayout?: boolean
}

const MOBILE_BREAKPOINT = 768

export const CardsSwiper: React.FC<CardsSwiperProps> = ({
  className,
  headerContent,
  headerClassName,
  children,
  showNavigation = true,
  showMobileNavigation = false,
  spaceBetween = 16,
  useMobileLayout = false,
  forceDesktopLayout = false,
}) => {
  const scrollRef = useRef<HTMLDivElement>(null)
  const containerRef = useRef<HTMLDivElement>(null)
  const [showLeft, setShowLeft] = useState(false)
  const [canGoPrev, setCanGoPrev] = useState(false)
  const [canGoNext, setCanGoNext] = useState(false)
  const [needsNavigation, setNeedsNavigation] = useState(false)
  const [isMobileView, setIsMobileView] = useState(false)

  useEffect(() => {
    const checkIsMobile = () => {
      const isMobile = window.innerWidth <= MOBILE_BREAKPOINT
      setIsMobileView(isMobile)

      // Принудительно пересчитываем размеры при изменении режима
      setTimeout(() => {
        calculateContainerWidth()
        updateFades()
      }, 100)
    }

    checkIsMobile()
    window.addEventListener("resize", checkIsMobile)

    return () => {
      window.removeEventListener("resize", checkIsMobile)
    }
  }, [])

  const shouldUseMobileLayout = useMobileLayout || (isMobileView && !forceDesktopLayout)

  // Пересчитываем размеры при изменении режима отображения
  useEffect(() => {
    if (!shouldUseMobileLayout) {
      // Только для десктопного режима - сбрасываем все стили и пересчитываем
      if (containerRef.current && scrollRef.current) {
        // Полностью сбрасываем стили
        containerRef.current.style.width = ""
        containerRef.current.style.maxWidth = ""
        scrollRef.current.style.width = ""
        scrollRef.current.style.maxWidth = ""

        // Принудительно пересчитываем layout
        containerRef.current.offsetHeight
        scrollRef.current.offsetHeight
      }

      setTimeout(() => {
        calculateContainerWidth()
        updateFades()
      }, 200)
    } else {
      // Для мобильного режима - сбрасываем все стили
      if (containerRef.current && scrollRef.current) {
        containerRef.current.style.width = ""
        containerRef.current.style.maxWidth = ""
        scrollRef.current.style.width = ""
        scrollRef.current.style.maxWidth = ""
      }
    }
  }, [shouldUseMobileLayout])

  const calculateContainerWidth = () => {
    if (!containerRef.current || !scrollRef.current) return

    const container = containerRef.current
    const scrollElement = scrollRef.current

    // Полностью сбрасываем все стили
    container.style.width = ""
    container.style.maxWidth = ""
    container.style.minWidth = ""
    scrollElement.style.width = ""
    scrollElement.style.maxWidth = ""
    scrollElement.style.minWidth = ""

    // Принудительно пересчитываем layout
    container.offsetHeight
    scrollElement.offsetHeight

    // Получаем актуальные размеры после сброса
    const containerRect = container.getBoundingClientRect()
    const viewportWidth = document.documentElement.clientWidth
    const distanceToEdge = Math.max(0, viewportWidth - containerRect.left - 1)

    // Устанавливаем новые размеры только если они больше 0
    if (distanceToEdge > 0) {
      container.style.width = `${distanceToEdge}px`
      scrollElement.style.width = `${distanceToEdge}px`
    }
  }

  const updateFades = () => {
    const el = scrollRef.current
    const container = containerRef.current
    if (!el || !container) return

    // Принудительно пересчитываем layout
    el.offsetHeight
    container.offsetHeight

    const atStart = el.scrollLeft <= 1
    const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1

    const originalWidth = container.style.width
    const originalScrollWidth = el.style.width
    container.style.width = ""
    el.style.width = ""

    // Принудительно пересчитываем layout после сброса стилей
    container.offsetHeight
    el.offsetHeight

    const naturalScrollWidth = el.scrollWidth
    const naturalClientWidth = el.clientWidth

    const spacerElement = el.querySelector(`.${styles.swiper_slide_spacer}`)
    let spacerWidth = 0
    if (spacerElement && !isMobileView) {
      // На мобилке spacer скрыт, поэтому не учитываем его ширину
      spacerWidth = spacerElement.getBoundingClientRect().width
    }

    const contentScrollWidth = naturalScrollWidth - spacerWidth
    const needsScrolling = contentScrollWidth > naturalClientWidth + 2

    container.style.width = originalWidth
    el.style.width = originalScrollWidth

    setShowLeft(!atStart)
    setCanGoPrev(!atStart)
    setCanGoNext(!atEnd && needsScrolling)
    setNeedsNavigation(needsScrolling)
  }

  const scrollToNext = () => {
    const el = scrollRef.current
    if (!el) return
    const scrollAmount = el.clientWidth * 0.8
    el.scrollBy({ left: scrollAmount, behavior: "smooth" })
  }

  const scrollToPrev = () => {
    const el = scrollRef.current
    if (!el) return
    const scrollAmount = el.clientWidth * 0.8
    el.scrollBy({ left: -scrollAmount, behavior: "smooth" })
  }

  useEffect(() => {
    const handleResize = () => {
      // При изменении размера окна сбрасываем стили и пересчитываем
      if (containerRef.current && scrollRef.current) {
        containerRef.current.style.width = ""
        containerRef.current.style.maxWidth = ""
        scrollRef.current.style.width = ""
        scrollRef.current.style.maxWidth = ""
      }

      setTimeout(() => {
        calculateContainerWidth()
        updateFades()
      }, 150)
    }

    const timeoutId = setTimeout(() => {
      updateFades()
      calculateContainerWidth()
    }, 100)

    const el = scrollRef.current
    if (!el) return
    el.addEventListener("scroll", updateFades)
    window.addEventListener("resize", handleResize)

    return () => {
      clearTimeout(timeoutId)
      el.removeEventListener("scroll", updateFades)
      window.removeEventListener("resize", handleResize)
    }
  }, [children])

  if (shouldUseMobileLayout) {
    return (
      <MobileCardsSwiper
        className={className}
        headerContent={headerContent}
        headerClassName={headerClassName}
        spaceBetween={spaceBetween}
      >
        {children}
      </MobileCardsSwiper>
    )
  }

  return (
    <div className={cx(styles.cards_swiper, className)}>
      {headerContent && (
        <div className={cx(styles.swiper_header, headerClassName)}>
          <div className={styles.header_content}>{headerContent}</div>
          {((showNavigation && !isMobileView) || (showMobileNavigation && isMobileView)) && needsNavigation && (
            <div
              className={cx(styles.header_navigation, {
                [styles.mobile_navigation]: isMobileView && showMobileNavigation,
              })}
            >
              <Button
                type="secondary"
                size="small"
                className={styles.nav_button}
                onClick={scrollToPrev}
                disabled={!canGoPrev}
              >
                <MoveLeft size={16} />
              </Button>
              <Button
                type="secondary"
                size="small"
                className={styles.nav_button}
                onClick={scrollToNext}
                disabled={!canGoNext}
              >
                <MoveRight size={16} />
              </Button>
            </div>
          )}
        </div>
      )}

      <div ref={containerRef} className={styles.stories_wrap}>
        {showLeft && <div className={styles.fade_left} />}
        <div className={cx(styles.stories)} ref={scrollRef}>
          {React.Children.map(children, (child, index) => (
            <div key={index} className={styles.swiper_slide}>
              {child}
            </div>
          ))}

          {/* Невидимый элемент для возможности долистывания последней карточки */}
          <div className={styles.swiper_slide_spacer} />
        </div>
      </div>
    </div>
  )
}
