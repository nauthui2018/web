import type React from "react"
import { BrowserRouter as Router, Routes, Route, Navigate } from "react-router-dom"
import DashboardPage from "./pages/DashboardPage"
import UserTest from "./pages/UserTest"
import HomePage from "./pages/HomePage"
import LogIn from "./pages/LogInPage"
import SignUp from "./pages/SignUpPage"
import QuizListPage from "./pages/QuizListPage"
import QuizTakingPage from "./pages/QuizTakingPage"
import CompletedPage from "@/pages/CompletedQuizPage";
import AdminDashboard from "@/pages/AdminDashboard";
import { Toaster, toast } from 'sonner'
import { AuthProvider } from './contexts/AuthContext'
import { ProtectedRoute } from './components/ProtectedRoute'
import { SessionManager } from './components/SessionManager'

const App: React.FC = () => {
  return (
    <Router>
      <AuthProvider>
        <div className="App h-full">
          <SessionManager />
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/dashboard" element={
              <ProtectedRoute>
                <DashboardPage />
              </ProtectedRoute>
            } />
            <Route path="/admin" element={
              <ProtectedRoute requireAdmin>
                <AdminDashboard />
              </ProtectedRoute>
            } />
            <Route path="/test/:testId" element={
              <ProtectedRoute>
                <UserTest />
              </ProtectedRoute>
            } />
            <Route path="/login" element={<LogIn />} />
            <Route path="/signup" element={<SignUp />} />
            <Route path="/quiz" element={
              <ProtectedRoute>
                <QuizListPage />
              </ProtectedRoute>
            } />
            <Route path="/quiz/:quizId" element={
              <ProtectedRoute>
                <QuizTakingPage />
              </ProtectedRoute>
            } />
            <Route path="/quiz/:quizId/completed" element={
              <ProtectedRoute>
                <CompletedPage />
              </ProtectedRoute>
            } />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
          <Toaster position="top-center" richColors />
        </div>
      </AuthProvider>
    </Router>
  )
}

export default App
