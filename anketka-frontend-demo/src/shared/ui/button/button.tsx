import React, { CSSProperties } from "react"
import cx from "classnames"
import Link from "next/link"
import styles from "./button.module.scss"

export interface IButton {
  type?: "primary" | "secondary" | "link" | "flat" | "blue-light" | "survey" | "pink-light"
  size?: "large" | "medium" | "small"
  href?: string
  target?: "_blank"
  disabled?: boolean
  loading?: boolean
  onClick?: (e?: React.MouseEvent) => void
  children?: React.ReactNode
  className?: string
  style?: CSSProperties
  htmlId?: string
  [key: `data-${string}`]: string | undefined
  htmlType?: "submit"
}

export const Button = (props: IButton) => {
  const type = props.type || "primary"
  const size = props.size || "medium"

  const isDisabled = Boolean(props.disabled || props.loading)
  const onClick = isDisabled ? undefined : props.onClick

  const dataAttributes = Object.keys(props).reduce(
    (acc, key) => {
      if (key.startsWith("data-")) {
        acc[key] = props[key as keyof IButton] as string
      }
      return acc
    },
    {} as Record<string, string>,
  )

  if (props.href) {
    return (
      <Link
        className={cx(styles.button, styles[type], styles[size], props.className, isDisabled && styles.disabled)}
        style={props.style}
        id={props.htmlId}
        href={isDisabled ? "#" : props.href}
        target={props.target}
        onClick={isDisabled ? (e) => e.preventDefault() : props.onClick}
        {...dataAttributes}
      >
        {props.children}

        {props.loading && <span className={styles.loader} />}
      </Link>
    )
  }

  return (
    <button
      type={props.htmlType}
      className={cx(
        styles.button,
        styles[type],
        styles[size],
        props.className,
        isDisabled && styles.disabled,
        props.loading && styles.with_loader,
      )}
      style={props.style}
      id={props.htmlId}
      disabled={isDisabled}
      onClick={onClick}
      {...dataAttributes}
    >
      {props.children}

      {props.loading && <span className={styles.loader} />}
    </button>
  )
}
