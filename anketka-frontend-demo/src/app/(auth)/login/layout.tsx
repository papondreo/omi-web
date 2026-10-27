import { Metadata } from "next"

export const metadata: Metadata = {
  title: "Вход в личный кабинет | Анкетка",
  description: "Войдите в личный кабинет Анкетки и проходите опросы.",
}

export default function LoginLayout({ children }: { children: React.ReactNode }) {
  return children
}
