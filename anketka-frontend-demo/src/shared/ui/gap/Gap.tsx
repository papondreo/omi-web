import React from "react"
import cx from "classnames"
import styles from "./Gap.module.scss"

interface GapProps {
  size?: "xs" | "sm" | "md" | "lg" | "xl" | "xxl" | number
  axis?: "horizontal" | "vertical"
  className?: string
}

export const Gap: React.FC<GapProps> = ({ size = "md", axis = "vertical", className }) => {
  const isNumericSize = typeof size === "number"

  return (
    <div
      className={cx(styles.gap, !isNumericSize && styles[size as keyof typeof styles], styles[axis], className)}
      style={
        isNumericSize
          ? {
              [axis === "vertical" ? "height" : "width"]: `${size}px`,
            }
          : undefined
      }
      aria-hidden="true"
    />
  )
}
