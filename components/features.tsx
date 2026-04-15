export function Features() {
  const features = [
    {
      title: 'Real-Time Monitoring',
      description: 'Track student attention levels in real-time during class sessions',
      icon: '📊'
    },
    {
      title: 'Detailed Analytics',
      description: 'Get comprehensive reports on student engagement patterns and trends',
      icon: '📈'
    },
    {
      title: 'Class Management',
      description: 'Easily create and manage classes, invite students, and organize content',
      icon: '👥'
    },
    {
      title: 'MCQ Testing',
      description: 'Build and administer multiple choice questions to assess understanding',
      icon: '✍️'
    },
    {
      title: 'Actionable Insights',
      description: 'Get AI-powered recommendations to improve teaching methods',
      icon: '💡'
    },
    {
      title: 'Student Dashboard',
      description: 'Students track their own progress and engagement metrics',
      icon: '📱'
    }
  ]

  return (
    <section id="features" className="py-20 md:py-32 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
            Powerful Features for Educators
          </h2>
          <p className="text-xl text-gray-600">
            Everything you need to monitor and improve student engagement
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, idx) => (
            <div
              key={idx}
              className="p-8 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all"
            >
              <div className="text-4xl mb-4">{feature.icon}</div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">
                {feature.title}
              </h3>
              <p className="text-gray-600">
                {feature.description}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
