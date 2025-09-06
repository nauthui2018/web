"use client"

import { useState, useEffect, useMemo } from "react"
import { useNavigate } from "react-router-dom"
import { Search, Filter, BookOpen, Clock, Target, Users, ChevronDown } from "lucide-react"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuCheckboxItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import api from "@/lib/axios"

interface Quiz {
  id: number
  title: string
  description: string
  durationMinutes?: number
  category: string
  difficulty: "Beginner" | "Intermediate" | "Advanced"
  questionCount: number
  timeLimit?: number
  passingScore: number
  tags: string[]
  createdAt: string
  isPublic: boolean
}

const categories = ["All", "Programming", "React", "CSS", "Backend", "TypeScript", "Database"]
const difficulties = ["All", "Beginner", "Intermediate", "Advanced"]
const sortOptions = [
  { value: "popular", label: "Most Popular" },
  { value: "rating", label: "Highest Rated" },
  { value: "newest", label: "Newest" },
  { value: "easiest", label: "Easiest First" },
  { value: "hardest", label: "Hardest First" },
]

export default function QuizListPage() {
  const navigate = useNavigate()
  const [searchTerm, setSearchTerm] = useState("")
  const [selectedCategory, setSelectedCategory] = useState("All")
  const [selectedDifficulty, setSelectedDifficulty] = useState("All")
  const [selectedTags, setSelectedTags] = useState<string[]>([])
  const [sortBy, setSortBy] = useState("popular")
  const [loadingSets, setLoadingSets] = useState(false)
  const [questionSets, setQuestionSets] = useState<Quiz[]>([])
  const [errorSets, setErrorSets] = useState<string | null>(null)

  useEffect(() => {
    setLoadingSets(true)
    api.get("v1/tests")
    .then((res) => {
          const items = res.data?.data?.items || []
          // Map API data to table format
          setQuestionSets(items.map((item: any) => ({
            id: item.id,
            title: item.title,
            durationMinutes: item.duration_minutes || 0,
            description: item.description,
            questionCount: item.questions_count || 0,
            status: item.is_active ? "Active" : "Draft",
            createdAt: item.created_at,
            lastModified: item.updated_at,
            category: item.category?.name || "General",
            isPublic: item.is_public,
            difficulty: item.difficulty_level || "Beginner",
            passingScore: item.passing_score || 0,
            tags: item.tags || [],
          })))
        })
        .catch((error) => {
          setErrorSets("Failed to load question sets. Please try again later.")
        })
    .finally(() => setLoadingSets(false))
  }, [])

  // Filter and sort the quizzes based on current filters
  const filteredAndSortedQuizzes = useMemo(() => {
    let filtered = [...questionSets]

    // Apply search filter
    if (searchTerm.trim()) {
      const searchLower = searchTerm.toLowerCase()
      filtered = filtered.filter(quiz => 
        quiz.title.toLowerCase().includes(searchLower) ||
        quiz.description.toLowerCase().includes(searchLower) ||
        quiz.category.toLowerCase().includes(searchLower) ||
        quiz.tags.some(tag => tag.toLowerCase().includes(searchLower))
      )
    }

    // Apply category filter
    if (selectedCategory !== "All") {
      filtered = filtered.filter(quiz => quiz.category === selectedCategory)
    }

    // Apply difficulty filter
    if (selectedDifficulty !== "All") {
      filtered = filtered.filter(quiz => quiz.difficulty === selectedDifficulty)
    }

    // Apply tag filter
    if (selectedTags.length > 0) {
      filtered = filtered.filter(quiz => 
        selectedTags.some(tag => quiz.tags.includes(tag))
      )
    }

    // Apply sorting
    switch (sortBy) {
      case "newest":
        filtered.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
        break
      case "easiest":
        filtered.sort((a, b) => {
          const difficultyOrder = { "Beginner": 1, "Intermediate": 2, "Advanced": 3 }
          return difficultyOrder[a.difficulty] - difficultyOrder[b.difficulty]
        })
        break
      case "hardest":
        filtered.sort((a, b) => {
          const difficultyOrder = { "Beginner": 1, "Intermediate": 2, "Advanced": 3 }
          return difficultyOrder[b.difficulty] - difficultyOrder[a.difficulty]
        })
        break
      case "popular":
        // For now, sort by question count as a proxy for popularity
        filtered.sort((a, b) => b.questionCount - a.questionCount)
        break
      case "rating":
        // For now, sort by creation date as a proxy for rating
        filtered.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
        break
      default:
        break
    }

    return filtered
  }, [questionSets, searchTerm, selectedCategory, selectedDifficulty, selectedTags, sortBy])

  const handleStartQuiz = (quizId: number) => {
    navigate(`/quiz/${quizId}`)
  }

  const handleTagToggle = (tag: string) => {
    setSelectedTags((prev) => (prev.includes(tag) ? prev.filter((t) => t !== tag) : [...prev, tag]))
  }

  const clearFilters = () => {
    setSearchTerm("")
    setSelectedCategory("All")
    setSelectedDifficulty("All")
    setSelectedTags([])
    setSortBy("popular")
  }

  const getDifficultyColor = (difficulty: string) => {
    switch (difficulty) {
      case "Beginner":
        return "bg-green-100 text-green-800 border-green-200"
      case "Intermediate":
        return "bg-yellow-100 text-yellow-800 border-yellow-200"
      case "Advanced":
        return "bg-red-100 text-red-800 border-red-200"
      default:
        return "bg-gray-100 text-gray-800 border-gray-200"
    }
  }

  // Get unique categories from the actual data
  const availableCategories = useMemo(() => {
    const categories = ["All", ...new Set(questionSets.map(quiz => quiz.category))]
    return categories
  }, [questionSets])

  // Get unique tags from the actual data
  const availableTags = useMemo(() => {
    const allTags = questionSets.flatMap(quiz => quiz.tags)
    return [...new Set(allTags)]
  }, [questionSets])

  if (loadingSets) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading quizzes...</p>
        </div>
      </div>
    )
  }

  if (errorSets) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <p className="text-red-500 mb-4">{errorSets}</p>
          <Button onClick={() => window.location.reload()}>Try Again</Button>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Header/Navbar */}
      <header className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky top-0 z-50">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              <h1 className="text-2xl font-bold">Quiz Hub</h1>
              <Badge variant="secondary" className="hidden sm:inline-flex">
                {filteredAndSortedQuizzes.length} of {questionSets.length} Available Quizzes
              </Badge>
            </div>
            <div className="flex items-center space-x-2">
              <Button onClick={() => navigate("/dashboard")}>
                Create Quiz
              </Button>
              <Button variant="outline" onClick={() => navigate("/")}>
                Home
              </Button>
            </div>
          </div>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        {/* Search and Filters */}
        <div className="mb-8 space-y-4">
          {/* Search Bar */}
          <div className="relative max-w-md">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
            <Input
              placeholder="Search quizzes, topics, or tags..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="pl-10"
            />
          </div>

          {/* Filters Row */}
          <div className="flex flex-wrap items-center gap-4">
            {/* Category Filter */}
            <Select value={selectedCategory} onValueChange={setSelectedCategory}>
              <SelectTrigger className="w-40">
                <SelectValue placeholder="Category" />
              </SelectTrigger>
              <SelectContent>
                {availableCategories.map((category) => (
                  <SelectItem key={category} value={category}>
                    {category}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Difficulty Filter */}
            <Select value={selectedDifficulty} onValueChange={setSelectedDifficulty}>
              <SelectTrigger className="w-40">
                <SelectValue placeholder="Difficulty" />
              </SelectTrigger>
              <SelectContent>
                {difficulties.map((difficulty) => (
                  <SelectItem key={difficulty} value={difficulty}>
                    {difficulty}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Sort */}
            <Select value={sortBy} onValueChange={setSortBy}>
              <SelectTrigger className="w-40">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                {sortOptions.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Clear Filters */}
            {(searchTerm || selectedCategory !== "All" || selectedDifficulty !== "All" || selectedTags.length > 0) && (
              <Button variant="ghost" onClick={clearFilters} className="text-muted-foreground">
                Clear Filters
              </Button>
            )}
          </div>

          {/* Active Filters Display */}
          {selectedTags.length > 0 && (
            <div className="flex flex-wrap gap-2">
              <span className="text-sm text-muted-foreground">Active tags:</span>
              {selectedTags.map((tag) => (
                <Badge
                  key={tag}
                  variant="secondary"
                  className="cursor-pointer hover:bg-destructive hover:text-destructive-foreground"
                  onClick={() => handleTagToggle(tag)}
                >
                  {tag} ×
                </Badge>
              ))}
            </div>
          )}

          {/* Available Tags */}
          {availableTags.length > 0 && (
            <div className="flex flex-wrap gap-2">
              <span className="text-sm text-muted-foreground">Available tags:</span>
              {availableTags.map((tag) => (
                <Badge
                  key={tag}
                  variant={selectedTags.includes(tag) ? "default" : "outline"}
                  className="cursor-pointer hover:bg-primary hover:text-primary-foreground"
                  onClick={() => handleTagToggle(tag)}
                >
                  {tag}
                </Badge>
              ))}
            </div>
          )}
        </div>

        {/* Quiz Grid */}
        {filteredAndSortedQuizzes.length === 0 ? (
          <div className="text-center py-12">
            <BookOpen className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
            <h3 className="text-xl font-semibold mb-2">No quizzes found</h3>
            <p className="text-muted-foreground mb-4">
              {questionSets.length === 0 
                ? "No quizzes are available at the moment."
                : "Try adjusting your search terms or filters to find more quizzes."
              }
            </p>
            {questionSets.length > 0 && <Button onClick={clearFilters}>Clear All Filters</Button>}
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredAndSortedQuizzes.map((quiz) => (
              <Card key={quiz.id} className="hover:shadow-lg transition-all duration-200 hover:scale-[1.02]">
                <CardHeader className="space-y-3">
                  <div className="flex items-start justify-between">
                    <div className="space-y-2 flex-1">
                      <CardTitle className="text-lg leading-tight">{quiz.title}</CardTitle>
                      <CardDescription className="line-clamp-2 text-sm">{quiz.description}</CardDescription>
                    </div>
                    <BookOpen className="w-5 h-5 text-primary flex-shrink-0 ml-2" />
                  </div>

                  {/* Tags */}
                  {quiz.tags.length > 0 && (
                    <div className="flex flex-wrap gap-1">
                      {quiz.tags.slice(0, 3).map((tag) => (
                        <Badge key={tag} variant="outline" className="text-xs">
                          {tag}
                        </Badge>
                      ))}
                      {quiz.tags.length > 3 && (
                        <Badge variant="outline" className="text-xs">
                          +{quiz.tags.length - 3}
                        </Badge>
                      )}
                    </div>
                  )}

                  {/* Category and Difficulty */}
                  <div className="flex items-center gap-2">
                    <Badge variant="secondary">{quiz.category}</Badge>
                    <Badge className={getDifficultyColor(quiz.difficulty)}>{quiz.difficulty}</Badge>
                  </div>
                </CardHeader>

                <CardContent className="space-y-4">
                  {/* Quiz Stats */}
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div className="flex items-center gap-2">
                      <BookOpen className="w-4 h-4 text-muted-foreground" />
                      <span>{quiz.questionCount} Questions</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-muted-foreground" />
                      <span>{quiz.durationMinutes ? `${quiz.durationMinutes} min` : "No limit"}</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Target className="w-4 h-4 text-muted-foreground" />
                      <span>{quiz.passingScore}% to pass</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge className={getDifficultyColor(quiz.difficulty)}>{quiz.difficulty}</Badge>
                    </div>
                  </div>

                  {/* Start Quiz Button */}
                  <Button onClick={() => handleStartQuiz(quiz.id)} className="w-full" size="sm">
                    Start Quiz
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
