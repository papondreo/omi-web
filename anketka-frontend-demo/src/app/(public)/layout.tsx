import React from "react"
import { PageHeader } from "@/shared/ui/page-header"
import { PageFooter } from "@/shared/ui/page-footer"
import styles from "./layout.module.scss"

export default function PublicLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className={styles.layout}>
      <PageHeader />
      <main className={styles.main}>{children}</main>
      <PageFooter />
    </div>
  )
}
