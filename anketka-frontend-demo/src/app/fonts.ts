import { Onest } from "next/font/google"

export const onest = Onest({
  weight: ["400", "500", "600", "700"],
  style: "normal",
  subsets: ["latin", "cyrillic"],
  display: "swap",
  preload: true,
  variable: "--font-onest",
  fallback: ["system-ui", "-apple-system", "sans-serif"],
})
