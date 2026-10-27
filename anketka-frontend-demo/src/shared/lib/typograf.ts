// @ts-ignore
import Typograf from "typograf"

const tp = new Typograf({ locale: ["ru", "en-US"] })

export const typograf = (str?: string | null) => {
  if (!str) return str

  return tp.execute(str)
}
