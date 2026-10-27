import { NextRequest, NextResponse } from "next/server"
import { SURVEY_PAGE_ENABLED } from "@/shared/lib/features"

export function middleware(request: NextRequest) {
  const response = NextResponse.next()
  const { pathname, search } = request.nextUrl

  // Выключенная фича недоступна и по прямой ссылке
  const DISABLED_PATHS = [...(SURVEY_PAGE_ENABLED ? [] : ["/profile/surveys"])]
  if (DISABLED_PATHS.some((it) => pathname === it || pathname.startsWith(`${it}/`))) {
    return NextResponse.redirect(new URL("/profile", request.url))
  }

  // Security-заголовки (CSP, HSTS, nosniff) в бою отдаёт nginx, а не middleware

  if (pathname.match(/\.(ico|jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|otf|js|css)$/)) {
    response.headers.set("Cache-Control", "public, max-age=31536000, immutable")
  }

  // URL с query-параметрами не индексируем: они плодят дубли страниц в поиске
  const isAsset = /\.(ico|jpg|jpeg|png|gif|webp|svg|woff|woff2|ttf|otf|js|css|txt|xml|json|map)$/.test(pathname)
  if (search && !isAsset && !pathname.startsWith("/api")) {
    response.headers.set("X-Robots-Tag", "noindex, follow")
  }

  // Инвариант: страница либо в src/app/sitemap.ts, либо здесь. Запрет отдаём заголовком,
  // а не Disallow в robots.txt: робот должен скачать страницу, чтобы увидеть noindex
  const NOINDEX_PATHS = ["/login", "/register", "/profile"]
  if (NOINDEX_PATHS.some((it) => pathname === it || pathname.startsWith(`${it}/`))) {
    response.headers.set("X-Robots-Tag", "noindex, follow")
  }

  return response
}

export const config = {
  matcher: ["/((?!_next/static|_next/image|favicon.ico).*)"],
}
