const yandexMetrikaId = process.env.NEXT_PUBLIC_YANDEX_METRIKA_ID!

interface IYandexMetrikaParams {
  [key: string]: string | number | boolean | null | undefined | Array<string | number>
}

export const yandexMetrika = {
  reachGoal: (goal: string) => (window as any).ym?.(yandexMetrikaId, "reachGoal", goal),
  params: (params: IYandexMetrikaParams) => (window as any).ym?.(yandexMetrikaId, "params", params),
  hit: (url: string) => (window as any).ym?.(yandexMetrikaId, "hit", url),
  getClientID: () => (window as any).Ya?.Metrika2(yandexMetrikaId).getClientID(),
  getId: () => yandexMetrikaId,
}
