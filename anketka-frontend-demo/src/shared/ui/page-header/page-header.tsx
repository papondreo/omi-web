"use client"

import React, { CSSProperties } from "react"
import cx from "classnames"
import Link from "next/link"
import dynamic from "next/dynamic"
import styles from "./page-header.module.scss"

const HeaderAuthButtons = dynamic(() => import("./header-auth-buttons").then((it) => it.HeaderAuthButtons), {
  ssr: false,
})

export interface IPageHeader {
  className?: string
  style?: CSSProperties
  htmlId?: string
}

export const PageHeader = (props: IPageHeader) => {
  return (
    <header className={cx(styles.page_header, props.className)} style={props.style} id={props.htmlId}>
      <div className={styles.container}>
        <Link className={styles.logo} href="/">
          Анкетка <span className={styles.badge}>demo</span>
        </Link>

        <HeaderAuthButtons />
      </div>
    </header>
  )
}
