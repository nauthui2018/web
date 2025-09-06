"use client"


import { useState, useEffect } from "react"
import { AppSidebar } from "@/components/app-sidebar"
import { QuestionSetsTable } from "@/components/question-sets-table"
import { DashboardQuestionSetsTable } from "@/components/dashboard-question-sets-table"
import { ChartAreaInteractive } from "@/components/chart-area-interactive"
import { SiteHeader } from "@/components/site-header"
import { SidebarInset, SidebarProvider } from "@/components/ui/sidebar"
import { CreateQuestionSet } from "@/components/create-question-set"
import { AnalyticsDashboard } from "@/components/analytics-dashboard"
import api from "@/lib/axios"

export default function DashboardPage() {
  const [currentView, setCurrentView] = useState("dashboard")
  const [questionSets, setQuestionSets] = useState<any[]>([])
  const [loadingSets, setLoadingSets] = useState(false)
  const [errorSets, setErrorSets] = useState<string | null>(null)
  const [refreshKey, setRefreshKey] = useState(0)
  const [dashboardQuestionSets, setDashboardQuestionSets] = useState<any[]>([])
  const [loadingDashboardSets, setLoadingDashboardSets] = useState(false)

  const handleNavigate = (url: string) => {
    console.log("Navigating to:", url) // Debug log
    setCurrentView(url)
  }

  const handleReloadQuestionSets = () => {
    setRefreshKey((k) => k + 1);
  };

  // Fetch question sets from API when viewing question sets
  useEffect(() => {
    if (currentView === "question-sets" || currentView === "/question-sets") {
      setLoadingSets(true)
      setErrorSets(null)
      api.get("/v1/management/tests")
        .then((res) => {
          const items = res.data?.data?.items || []
          // Map API data to table format
          setQuestionSets(items.map((item: any) => ({
            id: item.id,
            title: item.title,
            questionCount: item.questions_count || 0, // Updated to use questions_count
            status: item.is_active ? "Active" : "Draft",
            createdAt: item.created_at,
            lastModified: item.updated_at,
            category: item.category?.name || "General",
            durationMinutes: item.duration_minutes || 0,
            difficulty: item.difficulty_level || "Beginner",
          })))
        })
        .catch((err) => {
          setErrorSets("Failed to load question sets")
        })
        .finally(() => setLoadingSets(false))
    }
  }, [currentView, refreshKey])

  // Fetch question sets for dashboard
  useEffect(() => {
    const fetchDashboardQuestionSets = async () => {
      setLoadingDashboardSets(true)
      try {
        const response = await api.get("/v1/management/tests")
        const items = response.data?.data?.items || []
        // Map API data to simple table format
        setDashboardQuestionSets(items.map((item: any) => ({
          id: item.id,
          title: item.title,
          description: item.description || "No description available",
          difficulty: item.difficulty_level || "Beginner",
        })))
      } catch (err) {
        console.error("Failed to load dashboard question sets:", err)
      } finally {
        setLoadingDashboardSets(false)
      }
    }

    fetchDashboardQuestionSets()
  }, [])

  const renderContent = () => {
    switch (currentView) {
      case "quiz":
      case "/quiz":
        return (
          <div className="flex flex-col gap-4 py-4 md:gap-6">
            <div className="px-4 lg:px-6">
              <div className="flex flex-col gap-2">
                <h1 className="text-3xl font-bold tracking-tight">Available Quizzes</h1>
                <p className="text-muted-foreground">Select a quiz to take and test your knowledge.</p>
              </div>
            </div>
            <div className="px-4 lg:px-6">
              <div className="aspect-video w-full flex-1 rounded-lg border border-dashed flex items-center justify-center">
                <p className="text-muted-foreground">Quiz selection view coming soon...</p>
              </div>
            </div>
          </div>
        )
      case "question-sets":
      case "/question-sets":
        return (
          <div className="flex flex-col gap-4 py-4 md:gap-6">
            <div className="px-4 lg:px-6">
              <div className="flex flex-col gap-2">
                <h1 className="text-3xl font-bold tracking-tight">Question Sets</h1>
                <p className="text-muted-foreground">Manage your quiz question sets and track their performance.</p>
              </div>
            </div>
            {loadingSets ? (
              <div className="flex justify-center items-center py-10">Loading question sets...</div>
            ) : errorSets ? (
              <div className="flex justify-center items-center py-10 text-red-500">{errorSets}</div>
            ) : (
              <QuestionSetsTable data={questionSets} onNavigate={handleNavigate} onReload={handleReloadQuestionSets} />
            )}
          </div>
        )
      case "create-question-set":
      case "/create-question-set":
        return (
          <CreateQuestionSet onNavigate={handleNavigate} />
        )
      case "analytics":
      case "/analytics":
        return (
          <AnalyticsDashboard />
        )
      case "projects":
      case "/projects":
        return (
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
            <div className="px-4 lg:px-6">
              <div className="flex flex-col gap-2">
                <h1 className="text-3xl font-bold tracking-tight">Projects</h1>
                <p className="text-muted-foreground">Manage your projects and collaborations.</p>
              </div>
            </div>
            <div className="px-4 lg:px-6">
              <div className="aspect-video w-full flex-1 rounded-lg border border-dashed flex items-center justify-center">
                <p className="text-muted-foreground">Projects view coming soon...</p>
              </div>
            </div>
          </div>
        )
      case "team":
      case "/team":
        return (
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
            <div className="px-4 lg:px-6">
              <div className="flex flex-col gap-2">
                <h1 className="text-3xl font-bold tracking-tight">Team</h1>
                <p className="text-muted-foreground">Manage team members and permissions.</p>
              </div>
            </div>
            <div className="px-4 lg:px-6">
              <div className="aspect-video w-full flex-1 rounded-lg border border-dashed flex items-center justify-center">
                <p className="text-muted-foreground">Team view coming soon...</p>
              </div>
            </div>
          </div>
        )
      case "dashboard":
      case "/dashboard":
      default:
        if (currentView.startsWith("edit-question-set-")) {
          const editId = currentView.replace("edit-question-set-", "")
          return (
            <CreateQuestionSet onNavigate={handleNavigate} editId={editId} />
          )
        }
        return (
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
            <div className="px-4 lg:px-6">
              <ChartAreaInteractive />
            </div>
            <div className="px-4 lg:px-6">
              {loadingDashboardSets ? (
                <div className="flex justify-center items-center py-10">Loading question sets...</div>
              ) : (
                <DashboardQuestionSetsTable data={dashboardQuestionSets} />
              )}
            </div>
          </div>
        )
    }
  }

  const getPageTitle = () => {
    switch (currentView) {
      case "quiz":
      case "/quiz":
        return "Available Quizzes"
      case "question-sets":
      case "/question-sets":
        return "Question Sets"
      case "create-question-set":
      case "/create-question-set":
        return "Create Question Set"
      case "analytics":
      case "/analytics":
        return "Analytics"
      case "projects":
      case "/projects":
        return "Projects"
      case "team":
      case "/team":
        return "Team"
      case "dashboard":
      case "/dashboard":
      default:
        if (currentView.startsWith("edit-question-set-")) {
          return "Edit Question Set"
        }
        return "Dashboard"
    }
  }

  return (
    <SidebarProvider>
      <AppSidebar variant="inset" onNavigate={handleNavigate} />
      <SidebarInset>
        <SiteHeader title={getPageTitle()} />
        <div className="flex flex-1 flex-col">
          <div className="@container/main flex flex-1 flex-col gap-2">{renderContent()}</div>
        </div>
      </SidebarInset>
    </SidebarProvider>
  )
}
