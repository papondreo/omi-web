export const pluralize = (
  num: number | undefined | null,
  options: {
    one: (num: number) => string
    few: (num: number) => string
    many: (num: number) => string

    [valueNum: string]: (num: number) => string
  },
) => {
  if (options["value" + num]) return options["value" + num](num!)

  if (!num) return options.many(0)

  const _num = Math.abs(num)

  const res = _num % 100
  if (11 <= res && res <= 20) return options.many(num)

  const rest = _num % 10
  if (rest === 0) return options.many(num)
  if (rest === 1) return options.one(num)
  if (rest < 5) return options.few(num)
  return options.many(num)
}
