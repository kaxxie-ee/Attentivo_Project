# Attentivo - Student Engagement Monitoring System

A modern web application for monitoring and analyzing student engagement levels in real-time.

## Features

- **Real-Time Monitoring** - Track student attention levels during class sessions
- **Student Dashboard** - Students can view their engagement metrics and class information
- **Teacher Dashboard** - Teachers can manage classes and monitor student engagement
- **Class Management** - Create classes, invite students, and manage class codes
- **Analytics** - Detailed reports on student engagement patterns and trends
- **Authentication** - Secure session-based authentication for students and teachers

## Tech Stack

- **Frontend**: Next.js 16, React, TypeScript, Tailwind CSS
- **Backend**: Next.js API Routes
- **Database**: Supabase (PostgreSQL)
- **Authentication**: Session cookies
- **Deployment**: Vercel

## Getting Started

### Prerequisites

- Node.js 18+ (pnpm, npm, or yarn)
- Supabase account

### Installation

1. Clone the repository:
```bash
git clone https://github.com/kaxxie-ee/Attentivo_Project.git
cd Attentivo_Project
```

2. Install dependencies:
```bash
pnpm install
# or
npm install
# or
yarn install
```

3. Set up environment variables:
```bash
cp .env.example .env.local
```

4. Add your Supabase credentials to `.env.local`:
```
NEXT_PUBLIC_SUPABASE_URL=your_supabase_url
NEXT_PUBLIC_SUPABASE_ANON_KEY=your_supabase_anon_key
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key
```

5. Run the development server:
```bash
pnpm dev
# or
npm run dev
# or
yarn dev
```

6. Open [http://localhost:3000](http://localhost:3000) in your browser

## Project Structure

```
├── app/
│   ├── api/                    # API routes
│   │   └── auth/              # Authentication endpoints
│   ├── login/                 # Login page
│   ├── register/              # Registration page
│   ├── student/               # Student dashboard
│   ├── teacher/               # Teacher dashboard
│   ├── layout.tsx             # Root layout
│   ├── page.tsx               # Landing page
│   └── globals.css            # Global styles
├── components/
│   ├── ui/                    # Reusable UI components
│   ├── navbar.tsx             # Navigation bar
│   ├── hero.tsx               # Hero section
│   ├── features.tsx           # Features section
│   └── footer.tsx             # Footer
├── lib/
│   ├── supabase.ts            # Supabase client
│   ├── types.ts               # TypeScript types
│   └── middleware.ts          # Authentication middleware
├── public/                    # Static assets
└── scripts/                   # Database setup scripts
```

## Authentication Flow

1. Users register with email, password, name, and role (student/teacher)
2. Login credentials are verified against the database
3. Session cookie is created for authenticated users
4. Middleware protects dashboard routes and requires valid session
5. Users are redirected to their respective dashboard based on role

## Database Schema

- **users** - User accounts (email, password, name, role)
- **classes** - Classes created by teachers
- **participants** - Students enrolled in classes
- **sessions** - Active/completed class sessions
- **attention_scores** - Real-time attention data per student
- **questions** - MCQ questions for classes

## Deployment

### Deploy to Vercel

1. Push your code to GitHub
2. Go to [Vercel Dashboard](https://vercel.com)
3. Import your repository
4. Add environment variables in the Vercel dashboard
5. Click Deploy

The application will be live at `yourdomain.vercel.app`

## Development

### Build for Production

```bash
pnpm build
pnpm start
```

### Format Code

```bash
pnpm format
```

### Type Checking

```bash
pnpm type-check
```

## Features to Implement

- [ ] Real-time attention tracking via camera/eye-tracking
- [ ] WebSocket integration for live updates
- [ ] Advanced analytics dashboard
- [ ] Export attendance reports
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] Role-based access control (RBAC)

## Contributing

1. Create a feature branch (`git checkout -b feature/amazing-feature`)
2. Commit your changes (`git commit -m 'Add amazing feature'`)
3. Push to the branch (`git push origin feature/amazing-feature`)
4. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@attentivo.app or open an issue on GitHub.
