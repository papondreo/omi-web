import React from "react"
import { PageHeader } from "@/shared/ui/page-header"
import { PageFooter } from "@/shared/ui/page-footer"
import styles from "./layout.module.scss"

export interface IProfileLayout {
  children?: React.ReactNode
}

const ProfileLayout = (props: IProfileLayout) => {
  return (
    <div className={styles.layout}>
      <PageHeader htmlId="app-header" />

      <div className={styles.body}>
        <div className={styles.content}>{props.children}</div>
      </div>

      <PageFooter />
    </div>
  )
}

export default ProfileLayout
