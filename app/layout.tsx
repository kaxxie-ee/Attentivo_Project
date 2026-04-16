import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'Attentivo - Student Engagement Monitoring',
  description: 'Monitor and improve student engagement in online learning',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className="scroll-smooth">
      <body className="bg-white text-gray-900 antialiased">
        {children}
      </body>
    </html>
  )
}
