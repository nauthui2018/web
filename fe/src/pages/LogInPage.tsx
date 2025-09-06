import { LoginForm } from '@/components/login-form'
import { useAuth } from '@/contexts/AuthContext'
import { Navigate, useLocation } from 'react-router-dom'

export default function LogInPage() {
  const { isAuthenticated } = useAuth()
  const location = useLocation()

  // If already authenticated, redirect to the intended destination or home
  if (isAuthenticated) {
    const from = location.state?.from?.pathname || '/'
    return <Navigate to={from} replace />
  }

  return (
    <div className="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
      <div className="w-full max-w-sm">
        <LoginForm />
      </div>
    </div>
  )
}