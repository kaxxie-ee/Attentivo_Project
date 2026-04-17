import Link from 'next/link'
import { Button } from './ui/button'

export function Hero() {
  return (
    <section className="bg-gradient-to-b from-white to-gray-50 py-20 md:py-32">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-3xl mx-auto">
          <h1 className="text-5xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
            Monitor Student Engagement <span className="text-blue-600">In Real-Time</span>
          </h1>
          
          <p className="text-xl text-gray-600 mb-8 leading-relaxed">
            Attentivo helps educators understand and improve student attention levels with real-time monitoring, detailed analytics, and actionable insights.
          </p>

          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link href="/register">
              <Button variant="primary" size="lg">
                Get Started Free
              </Button>
            </Link>
            <Link href="#features">
              <Button variant="outline" size="lg">
                Learn More
              </Button>
            </Link>
          </div>

          <p className="text-gray-500 text-sm mt-8">
            No credit card required • 14-day free trial • Cancel anytime
          </p>
        </div>
      </div>
    </section>
  )
}
