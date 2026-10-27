import React from "react"
import { typograf } from "@/shared/lib/typograf"
import styles from "./custom-toast.module.scss"

interface ICustomToast {
  message: string
  subtitle?: string
}

export const CustomToast = (props: ICustomToast) => (
  <div className={styles.toast_container}>
    <div className={styles.text_container}>
      <div className={styles.main_text}>{typograf(props.message)}</div>
      {props.subtitle && <div className={styles.subtitle_text}>{typograf(props.subtitle)}</div>}
    </div>
  </div>
)
