'use client'

import { useState, useEffect } from 'react'
import { useRouter } from 'next/navigation'
import Link from 'next/link'
import { Button } from '@/components/ui/button'

interface Class {
  id: string
  name: string
  code: string
  teacher_id: string
}

export default function StudentDashboard() {
  const router = useRouter()
  const [classes, setClasses] = useState<Class[]>([])
  const [showJoinClass, setShowJoinClass] = useState(false)
  const [classCode, setClassCode] = useState('')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check authentication
    const checkAuth = async () => {
      const response = await fetch('/api/auth/verify', { method: 'GET' })
      if (!response.ok) {
        router.push('/login')
      } else {
        setLoading(false)
      }
    }

    checkAuth()
  }, [router])

  const handleJoinClass = async (e: React.FormEvent) => {
    e.preventDefault()
    // Will be implemented with API
    setShowJoinClass(false)
  }

  const handleLogout = async () => {
    await fetch('/api/auth/logout', { method: 'POST' })
    router.push('/')
  }

  if (loading) {
    return <div className="min-h-screen flex items-center justify-center">Loading...</div>
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
          <h1 className="text-2xl font-bold text-blue-600">Attentivo</h1>
          <button
            onClick={handleLogout}
            className="text-gray-600 hover:text-gray-900"
          >
            Logout
          </button>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="mb-8">
          <h2 className="text-3xl font-bold text-gray-900 mb-4">My Classes</h2>
          <Button
            variant="primary"
            onClick={() => setShowJoinClass(!showJoinClass)}
          >
            Join a Class
          </Button>
        </div>

        {showJoinClass && (
          <div className="bg-white rounded-lg shadow p-6 mb-8">
            <form onSubmit={handleJoinClass} className="flex gap-4">
              <input
                type="text"
                value={classCode}
                onChange={(e) => setClassCode(e.target.value)}
                placeholder="Enter class code"
                className="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
              />
              <Button variant="primary" type="submit">
                Join
              </Button>
            </form>
          </div>
        )}

        {classes.length === 0 ? (
          <div className="text-center py-12">
            <p className="text-gray-600">No classes yet. Join a class to get started.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {classes.map((cls) => (
              <Link
                key={cls.id}
                href={`/student/class/${cls.id}`}
                className="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6"
              >
                <h3 className="text-xl font-semibold text-gray-900 mb-2">
                  {cls.name}
                </h3>
                <p className="text-gray-600">Code: {cls.code}</p>
              </Link>
            ))}
          </div>
        )}
      </main>
    </div>
  )
}
