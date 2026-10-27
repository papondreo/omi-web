import { ApiError } from "@/shared/api/back/_generated"
import { sanitizeError } from "./sanitize-error"

export const getApiErrors = async (error: unknown): Promise<Record<string, string>> => {
  if (error instanceof ApiError) {
    const status = error.response.status

    try {
      const errorResp = await error.response.json()

      if (errorResp.properties) {
        const properties = errorResp.properties as Record<string, string>
        // Санитизируем каждое свойство
        const sanitizedProperties: Record<string, string> = {}
        for (const [key, value] of Object.entries(properties)) {
          sanitizedProperties[key] = sanitizeError(value)
        }
        return sanitizedProperties
      } else if (errorResp.message) {
        return { global: sanitizeError(errorResp.message) }
      } else if (errorResp.error) {
        return { global: sanitizeError(errorResp.error) }
      }
    } catch {
      if (status === 404) {
        return {
          global:
            "Не удалось выполнить запрос. Возможно, данные уже были изменены или удалены. Обновите страницу и попробуйте снова",
        }
      } else if (status === 500 || status === 502 || status === 503) {
        return {
          global:
            "К сожалению, на сервере произошла ошибка. Мы уже работаем над её устранением. Попробуйте повторить действие через несколько минут",
        }
      } else if (status === 504) {
        return {
          global:
            "Сервер долго не отвечает. Проверьте интернет-соединение и попробуйте ещё раз. Если проблема повторится, напишите нам на support@anketka.ru",
        }
      } else if (status === 401) {
        return { global: "Ваша сессия истекла. Пожалуйста, войдите в аккаунт заново" }
      } else if (status === 403) {
        return {
          global:
            "У вас нет доступа к этому действию. Если считаете это ошибкой, свяжитесь с поддержкой support@anketka.ru",
        }
      } else if (status === 429) {
        return { global: "Вы отправляете слишком много запросов. Подождите минуту и попробуйте снова" }
      } else if (status === 400) {
        return { global: "Проверьте правильность заполнения всех полей и попробуйте снова" }
      } else if (status === 409) {
        return {
          global: "Данные уже существуют или были изменены. Обновите страницу и проверьте актуальную информацию",
        }
      } else if (status === 422) {
        return { global: "Некоторые данные заполнены неверно. Проверьте все поля и попробуйте снова" }
      }
    }

    return {
      global: `Что-то пошло не так (код ошибки: ${status}). Попробуйте ещё раз или напишите нам на support@anketka.ru`,
    }
  } else if (error instanceof TypeError && error.message === "Failed to fetch") {
    return { global: "Не удается подключиться к серверу. Проверьте интернет-соединение и попробуйте снова" }
  } else if (error instanceof DOMException && error.name === "AbortError") {
    return { global: "Действие было отменено. Попробуйте ещё раз" }
  } else if (error instanceof Error) {
    if (error.message.includes("NetworkError") || error.message.includes("ERR_NETWORK")) {
      return { global: "Проблема с интернет-соединением. Проверьте подключение к сети и попробуйте снова" }
    } else if (error.message.includes("timeout") || error.message.includes("Timeout")) {
      return { global: "Запрос выполняется слишком долго. Проверьте интернет-соединение и попробуйте ещё раз" }
    } else if (error.message.includes("CORS")) {
      return {
        global:
          "Проблема с настройками безопасности. Попробуйте обновить страницу. Если не поможет — напишите на support@anketka.ru",
      }
    }

    return { global: sanitizeError(error.message) }
  }

  return {
    global:
      "Что-то пошло не так. Попробуйте обновить страницу. Если проблема останется, напишите нам на support@anketka.ru",
  }
}
