import { NextRequest, NextResponse } from 'next/server'

const protectedRoutes = ['/student', '/teacher', '/admin']
const publicRoutes = ['/', '/login', '/register']

export function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname
  const isProtected = protectedRoutes.some(route => pathname.startsWith(route))
  const isPublic = publicRoutes.includes(pathname)

  const token = request.cookies.get('auth-token')?.value

  if (isProtected && !token) {
    return NextResponse.redirect(new URL('/login', request.url))
  }

  if (isPublic && token && (pathname === '/login' || pathname === '/register')) {
    return NextResponse.redirect(new URL('/student', request.url))
  }

  return NextResponse.next()
}

export const config = {
  matcher: ['/((?!api|_next|static|public).*)']
}
