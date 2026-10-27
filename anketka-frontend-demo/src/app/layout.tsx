import type { Metadata } from "next"
import React from "react"
import { Slide, ToastContainer } from "react-toastify"
import { onest } from "./fonts"
import { YandexMetrika } from "@/features/yandex-metrika"
import "normalize.css"
import "@/shared/styles/app.scss"

export const metadata: Metadata = {
  title: "Анкетка — заработок на опросах",
  description: "Демо-проект фронтенда Анкетки: стек, структура и подход без бизнес-логики.",
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL!),
  icons: {
    icon: "/favicon.svg",
  },
}

export const viewport = {
  width: "device-width",
  initialScale: 1,
  viewportFit: "cover" as const,
}

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode
}>) {
  return (
    <html lang="ru" className={onest.variable}>
      <body className={onest.className}>
        <YandexMetrika />

        {children}

        <ToastContainer
          position="top-center"
          transition={Slide}
          hideProgressBar
          closeButton={false}
          autoClose={3000}
          newestOnTop
          draggable={false}
          closeOnClick
          pauseOnFocusLoss={false}
          pauseOnHover={false}
        />
      </body>
    </html>
  )
}
