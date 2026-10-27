import { AuthApi, Configuration } from "./_generated"
import { BackApiConfig } from "./config"
import { SurveyApiOverridden } from "@/shared/api/back/override"

export class BackApi {
  readonly auth = new AuthApi(this.config)

  readonly survey = new SurveyApiOverridden(this.config)

  constructor(private config: Configuration = new BackApiConfig()) {}
}
