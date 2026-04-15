export type UserRole = 'student' | 'teacher' | 'admin'

export interface User {
  id: string
  email: string
  name: string
  role: UserRole
  created_at: string
}

export interface Class {
  id: string
  name: string
  code: string
  teacher_id: string
  created_at: string
}

export interface Session {
  id: string
  class_id: string
  title: string
  start_time: string
  end_time: string
  status: 'active' | 'completed'
}

export interface AttentionScore {
  id: string
  session_id: string
  student_id: string
  score: number
  timestamp: string
}
