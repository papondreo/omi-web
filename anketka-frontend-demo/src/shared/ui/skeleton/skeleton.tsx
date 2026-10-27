"use client"

import React, { CSSProperties } from "react"
import cx from "classnames"
import styles from "./skeleton.module.scss"

export interface ISkeleton {
  className?: string
  style?: CSSProperties
  htmlId?: string
  width?: string | number
  height?: string | number
  borderRadius?: string | number
  variant?: "text" | "circular" | "rectangular"
  animation?: "pulse" | "wave" | "none"
}

export const Skeleton = (props: ISkeleton) => {
  const { variant, animation = "pulse", width, height, borderRadius } = props

  const inlineStyles: CSSProperties = {
    ...props.style,
    width,
    height,
    borderRadius: variant === "circular" ? "50%" : borderRadius,
  }

  return (
    <div
      className={cx(
        styles.skeleton,
        variant && styles[variant],
        animation !== "none" && styles[animation],
        props.className,
      )}
      style={inlineStyles}
      id={props.htmlId}
    />
  )
}
