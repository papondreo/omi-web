import React from "react"
import cx from "classnames"
import { typograf } from "@/shared/lib/typograf"
import styles from "./custom-toast.module.scss"

export interface IToastAction {
  label: string
  onClick: () => void
}

interface ICustomToast {
  message: string
  subtitle?: string
  action?: IToastAction
}

export const CustomToast = (props: ICustomToast) => (
  <div className={styles.toast_container}>
    <div className={cx(styles.text_container, props.action && styles.with_action)}>
      <div className={styles.main_text}>{typograf(props.message)}</div>
      {props.subtitle && <div className={styles.subtitle_text}>{typograf(props.subtitle)}</div>}
    </div>

    {props.action && (
      // Клик по тосту и так его закрывает (closeOnClick), отдельно закрывать не нужно
      <button type="button" className={styles.action} onClick={props.action.onClick}>
        {props.action.label}
      </button>
    )}
  </div>
)
