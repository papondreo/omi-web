"use client"

import React from "react"
import cx from "classnames"
import { Eye, EyeOff } from "lucide-react"
import { IInput, Input } from "@/shared/ui/input"
import styles from "./password-input.module.scss"

export interface IPasswordInput extends Omit<IInput, "type" | "iconRight"> {}

export const PasswordInput = (props: IPasswordInput) => {
  const [visible, setVisible] = React.useState(false)

  const onClickEye = () => setVisible((it) => !it)
  const Icon = visible ? Eye : EyeOff

  return (
    <Input
      {...props}
      className={cx(styles.password_input, props.className)}
      type={visible ? "text" : "password"}
      iconRight={
        <div className={styles.icon} onClick={onClickEye}>
          <Icon size={20} />
        </div>
      }
    />
  )
}
