import React, { CSSProperties, ReactEventHandler, ReactNode } from "react"
import cx from "classnames"
import styles from "./input.module.scss"

export interface IInput {
  type?: "text" | "password" | "email" | "tel"

  name?: string
  value?: string
  onChange?: ReactEventHandler<HTMLInputElement>

  label?: ReactNode
  placeholder?: string
  error?: ReactNode

  icon?: ReactNode
  iconRight?: ReactNode

  className?: string
  style?: CSSProperties
  htmlId?: string

  onFocus?: React.FocusEventHandler<HTMLInputElement>
  onBlur?: () => void
  required?: boolean
  autoComplete?: string
  autoCorrect?: string
  autoCapitalize?: string
  spellCheck?: boolean
  autoFocus?: boolean
  maxLength?: number
  pattern?: string
  inputMode?: "none" | "text" | "tel" | "url" | "email" | "numeric" | "decimal" | "search"
  readOnly?: boolean
  disabled?: boolean
}

export const Input = (props: IInput) => {
  return (
    <div className={cx(styles.input_component, props.className)} style={props.style} id={props.htmlId}>
      {props.label && <div className={styles.label}>{props.label}</div>}

      <div className={styles.input_wrapper}>
        <input
          name={props.name}
          type={props.type || "text"}
          placeholder={props.placeholder}
          className={cx(styles.input, !!props.error && styles.error)}
          value={props.value}
          onChange={props.onChange}
          onFocus={props.onFocus}
          onBlur={props.onBlur}
          required={props.required}
          autoComplete={props.autoComplete}
          autoCorrect={props.autoCorrect}
          autoCapitalize={props.autoCapitalize}
          spellCheck={props.spellCheck}
          autoFocus={props.autoFocus}
          maxLength={props.maxLength}
          pattern={props.pattern}
          inputMode={props.inputMode}
          readOnly={props.readOnly}
          disabled={props.disabled}
        />
        {props.icon && <div className={styles.input_left_icon}>{props.icon}</div>}
        {props.iconRight && <div className={styles.input_right_icon}>{props.iconRight}</div>}
      </div>

      {props.error && <div className={styles.error_text}>{props.error}</div>}
    </div>
  )
}
