import React, { CSSProperties } from "react"
import cx from "classnames"
import styles from "./page-footer.module.scss"

export interface IPageFooter {
  className?: string
  style?: CSSProperties
  htmlId?: string
}

export const PageFooter = (props: IPageFooter) => {
  return (
    <footer className={cx(styles.page_footer, props.className)} style={props.style} id={props.htmlId}>
      <div className={styles.container}>
        <div className={styles.copyright}>© Анкетка, демо-проект для знакомства со стеком</div>
        <a className={styles.link} href={`${process.env.NEXT_PUBLIC_API_URL}/swagger.html`} target="_blank">
          Swagger демо-бэкенда
        </a>
      </div>
    </footer>
  )
}
