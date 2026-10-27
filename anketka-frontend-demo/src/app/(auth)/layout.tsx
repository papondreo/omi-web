"use client"

import React, { useEffect } from "react"
import { useRouter } from "next/navigation"
import { PageHeader } from "@/shared/ui/page-header"
import { PageFooter } from "@/shared/ui/page-footer"
import { useAuthed } from "@/shared/api/back"
import styles from "./layout.module.scss"

export interface IAuthLayout {
  children?: React.ReactNode
}

const AuthLayout = (props: IAuthLayout) => {
  const authed = useAuthed()
  const router = useRouter()

  useEffect(() => {
    if (authed) {
      router.replace("/profile")
    }
  }, [authed])

  return (
    <div className={styles.auth_layout}>
      <PageHeader />
      {props.children}
      <PageFooter className={styles.footer} />
    </div>
  )
}

export default AuthLayout
