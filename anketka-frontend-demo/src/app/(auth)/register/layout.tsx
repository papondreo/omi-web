import { Metadata } from "next"

export const metadata: Metadata = {
  title: "Регистрация | Анкетка",
  description: "Зарегистрируйтесь в Анкетке, чтобы проходить опросы.",
}

export default function RegisterLayout({ children }: { children: React.ReactNode }) {
  return children
}
