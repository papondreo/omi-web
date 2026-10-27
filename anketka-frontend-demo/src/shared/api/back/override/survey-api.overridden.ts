import useSWR, { SWRConfiguration, SWRResponse } from "swr"
import { GetSurveysResponseDto, SurveyApi, SurveyDto } from "@/shared/api/back/_generated"
import * as runtime from "@/shared/api/back/_generated/runtime"
import { getAuthed } from "@/shared/api/back/lib"

export class SurveyApiOverridden extends SurveyApi {
  useSurveys = (() => {
    const key = () => {
      if (!getAuthed()) throw new Error("not authed")
      return this.surveysRequestOpts()
    }
    const fetcher = (context: runtime.RequestOpts) => this.swrFetch(this.surveysFetch(context))
    const fn = (config?: SWRConfiguration<GetSurveysResponseDto>): SWRResponse<GetSurveysResponseDto> => {
      return useSWR<GetSurveysResponseDto>(() => key(), fetcher, config)
    }
    fn.key = key
    return fn
  })()

  useSurvey = (() => {
    const key = (id: number) => {
      if (!getAuthed()) throw new Error("not authed")
      return this.surveyRequestOpts({ id })
    }
    const fetcher = (context: runtime.RequestOpts) => this.swrFetch(this.surveyFetch(context))
    const fn = (id: number, config?: SWRConfiguration<SurveyDto>): SWRResponse<SurveyDto> => {
      return useSWR<SurveyDto>(() => key(id), fetcher, config)
    }
    fn.key = key
    return fn
  })()
}
