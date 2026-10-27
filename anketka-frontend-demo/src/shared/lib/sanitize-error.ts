/**
 * Определяет, является ли ошибка технической английской ошибкой
 * @param errorMessage - сообщение об ошибке
 * @returns true если это техническая ошибка на английском
 */
const isTechnicalEnglishError = (errorMessage: string): boolean => {
  if (!errorMessage) return false

  // Проверяем, содержит ли сообщение английские технические термины
  const technicalPatterns = [
    // SQL ошибки
    /SQLSTATE/i,
    /Integrity constraint violation/i,
    /Duplicate entry/i,
    /UNIQ_/i,
    /FOREIGN KEY constraint/i,
    /Cannot add or update a child row/i,

    // JSON ошибки
    /Unexpected token/i,
    /is not valid JSON/i,
    /Unexpected non-whitespace character/i,
    /JSON at position/i,
    /SyntaxError/i,

    // HTTP ошибки
    /Internal Server Error/i,
    /Bad Request/i,
    /Unauthorized/i,
    /Forbidden/i,
    /Not Found/i,
    /Method Not Allowed/i,
    /Request Timeout/i,
    /Too Many Requests/i,

    // Системные ошибки
    /Exception occurred/i,
    /Error executing/i,
    /Connection refused/i,
    /Timeout/i,
    /Network error/i,
    /Failed to fetch/i,

    // Другие технические термины
    /stack trace/i,
    /debug/i,
    /console/i,
    /undefined/i,
    /null pointer/i,
    /TypeError/i,
    /ReferenceError/i,
    /RangeError/i,
  ]

  return technicalPatterns.some((pattern) => pattern.test(errorMessage))
}

/**
 * Определяет, содержит ли строка английские символы
 * @param text - текст для проверки
 * @returns true если содержит английские символы
 */
const containsEnglishText = (text: string): boolean => {
  // Проверяем наличие английских букв
  return /[a-zA-Z]/.test(text)
}

/**
 * Определяет, содержит ли строка кириллические символы
 * @param text - текст для проверки
 * @returns true если содержит кириллические символы
 */
const containsCyrillicText = (text: string): boolean => {
  // Проверяем наличие кириллических букв
  return /[а-яёА-ЯЁ]/.test(text)
}

/**
 * Санитизирует ошибку, заменяя технические английские ошибки на понятное сообщение
 * @param errorMessage - оригинальное сообщение об ошибке
 * @returns санитизированное сообщение
 */
export const sanitizeError = (errorMessage: string): string => {
  if (!errorMessage) return errorMessage

  // Исключения - ошибки, которые нужно передавать как есть
  const exceptions = [
    "Registration failed: Member already exist in panel",
    "У вас уже есть активный аккаунт в проекте МоеМнени",
  ]

  if (exceptions.includes(errorMessage)) {
    return errorMessage
  }

  // Если это техническая английская ошибка - заменяем
  if (isTechnicalEnglishError(errorMessage)) {
    return "Что-то пошло не так, попробуйте позже"
  }

  // Если сообщение содержит только английский текст (без кириллицы)
  // и выглядит как техническая ошибка - заменяем
  if (containsEnglishText(errorMessage) && !containsCyrillicText(errorMessage)) {
    // Дополнительные проверки на технические паттерны
    const suspiciousPatterns = [
      /error/i,
      /exception/i,
      /failed/i,
      /invalid/i,
      /unexpected/i,
      /cannot/i,
      /unable to/i,
      /access denied/i,
      /permission denied/i,
      /not found/i,
      /timeout/i,
      /connection/i,
      /network/i,
      /server/i,
      /database/i,
      /query/i,
      /syntax/i,
      /parse/i,
      /token/i,
      /key/i,
      /constraint/i,
      /violation/i,
    ]

    if (suspiciousPatterns.some((pattern) => pattern.test(errorMessage))) {
      return "Что-то пошло не так, попробуйте позже"
    }
  }

  // Возвращаем оригинальное сообщение, если оно не требует санитизации
  return errorMessage
}
