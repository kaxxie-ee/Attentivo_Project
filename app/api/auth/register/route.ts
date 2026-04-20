import { NextRequest, NextResponse } from 'next/server'
import { supabaseAdmin } from '@/lib/supabase'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { email, password, name, role } = body

    if (!email || !password || !name || !role) {
      return NextResponse.json(
        { error: 'All fields are required' },
        { status: 400 }
      )
    }

    if (!['learner', 'teacher'].includes(role)) {
      return NextResponse.json(
        { error: 'Invalid role. Must be "learner" or "teacher"' },
        { status: 400 }
      )
    }

    // Check if user already exists
    const { data: existingUser, error: checkError } = await supabaseAdmin
      .from('users')
      .select('user_id')
      .eq('email', email)

    if (existingUser && existingUser.length > 0) {
      return NextResponse.json(
        { error: 'User already exists' },
        { status: 409 }
      )
    }

    // Create new user
    console.log('[v0] Attempting to insert user:', { email, name, role })
    console.log('[v0] Service role key configured:', !!process.env.SUPABASE_SERVICE_ROLE_KEY)
    
    const { data: newUser, error: insertError } = await supabaseAdmin
      .from('users')
      .insert([
        {
          email,
          password, // In production, hash this with bcrypt
          full_name: name,
          role
        }
      ])
      .select()
      .single()

    if (insertError) {
      console.error('[v0] Insert error details:', insertError)
      console.error('[v0] Error code:', insertError.code)
      console.error('[v0] Error message:', insertError.message)
      return NextResponse.json(
        { error: 'Failed to create user: ' + insertError.message },
        { status: 500 }
      )
    }
    
    console.log('[v0] User created successfully:', newUser.user_id)

    // Create response with auth token
    const response = NextResponse.json(
      { 
        user: {
          id: newUser.user_id,
          email: newUser.email,
          name: newUser.full_name,
          role: newUser.role
        }
      },
      { status: 201 }
    )

    response.cookies.set({
      name: 'auth-token',
      value: String(newUser.user_id),
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7
    })

    return response
  } catch (error) {
    console.error('Register error:', error)
    return NextResponse.json(
      { error: 'Internal server error' },
      { status: 500 }
    )
  }
}
