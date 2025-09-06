"use client"

import { useState, useEffect } from "react"
import { useParams, useNavigate } from "react-router-dom"
import { toast } from "sonner"
import {
  ArrowLeftIcon,
  ArrowRightIcon,
  FlagIcon,
  PlayIcon,
  XCircleIcon,
  BookOpenIcon,
  TargetIcon,
  TimerIcon,
  AlertCircleIcon,
  HomeIcon,
  CheckCircleIcon,
  LoaderIcon,
  TrophyIcon,
  EyeIcon,
  RotateCcwIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Progress } from "@/components/ui/progress"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { Checkbox } from "@/components/ui/checkbox"
import { Textarea } from "@/components/ui/textarea"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import api from "@/lib/axios"

interface UserAnswer {
  questionId: number
  selectedOptionIds: number[]
  textAnswer?: string
  timeSpent: number
}

interface QuizResult {
  id: number
  testId: number
  userId: number
  title: string
  score: number
  passingScore?: number
  status: string
  correctAnswers: number
  totalQuestions: number
  durationTaken: number
  percentage: number
  passed: boolean
  allowRetakes: boolean
  answers: UserAnswer[]
}

interface Option {
  id: number
  text: string
  isCorrect: boolean
  explanation?: string
}

interface Question {
  id: number
  text: string
  options: Option[]
  type: "multiple_choice" | "multiple_select" | "true_false" | "short_answer" | "essay"
  points: number
  explanation?: string
}

interface QuestionSet {
  id: number
  title: string
  description: string
  category: string
  difficulty: "Beginner" | "Intermediate" | "Advanced"
  status: "Draft" | "Active"
  isPublic: boolean
  durationMinutes?: number
  passingScore: number
  allowRetakes: boolean
  shuffleQuestions: boolean
  showCorrectAnswers: boolean
  questions: Question[]
}

type QuizState = "start" | "taking" | "completed" | "review" | "not-found"

export default function QuizTakingPage() {
  const { quizId } = useParams<{ quizId: string }>()
  const navigate = useNavigate()

  const [quiz, setQuiz] = useState<QuestionSet | null>(null)
  const [quizState, setQuizState] = useState<QuizState>("start")
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0)
  const [userAnswers, setUserAnswers] = useState<Map<number, UserAnswer>>(new Map())
  const [timeRemaining, setTimeRemaining] = useState<number>(0)
  const [startTime, setStartTime] = useState<Date | null>(null)
  const [questionStartTime, setQuestionStartTime] = useState<Date | null>(null)
  const [showSubmitDialog, setShowSubmitDialog] = useState(false)
  const [quizResult, setQuizResult] = useState<QuizResult | null>(null)
  const [flaggedQuestions, setFlaggedQuestions] = useState<Set<number>>(new Set())
  const [attemptId, setAttemptId] = useState<number | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [loading, setLoading] = useState(true)

  // Load quiz data
  useEffect(() => {
    if (!quizId) {
      setQuizState("not-found")
      return
    }

    const loadQuiz = async () => {
      try {
        const response = await api.get(`v1/tests/${quizId}/take`)
        const data = response.data?.data?.test

        if (data) {
          setQuiz({
            id: data.id,
            title: data.title || "",
            description: data.description || "",
            category: data.category?.name || "",
            difficulty: data.difficulty_level || "Beginner",
            durationMinutes: data.duration_minutes || 0,
            status: data.is_active ? "Active" : "Draft",
            isPublic: data.is_public || false,
            passingScore: data.passing_score || 70,
            allowRetakes: true,
            shuffleQuestions: false,
            showCorrectAnswers: data.show_correct_answer || false,
            questions: (data.questions || []).map((q: any, idx: number) => ({
              id: q.id || idx + 1,
              text: q.question_text || "",
              type: q.question_type || "multiple_choice",
              points: q.points || 1,
              options: (q.options || []).map((opt: any) => ({
                id: opt.id,
                text: opt.text,
                isCorrect: opt.is_correct,
              })),
            })),
          })
        } else {
          setQuizState("not-found")
        }
      } catch (error) {
        console.error("Error loading quiz:", error)
        toast.error("Failed to load quiz")
        setQuizState("not-found")
      } finally {
        setLoading(false)
      }
    }

    loadQuiz()
  }, [quizId])

  // Timer effect
  useEffect(() => {
    if (quizState === "taking" && timeRemaining > 0) {
      const timer = setInterval(() => {
        setTimeRemaining((prev) => {
          if (prev <= 1) {
            handleSubmitQuiz()
            return 0
          }
          return prev - 1
        })
      }, 1000)
      return () => clearInterval(timer)
    }
  }, [quizState, timeRemaining])

  const startQuiz = async () => {
    try {
      const response = await api.post(`v1/tests/${quizId}/start`)
      const data = response.data?.data

      if (data) {
        setAttemptId(data.id)
        setQuizState("taking")
        setStartTime(new Date())
        setQuestionStartTime(new Date())

        if (quiz?.durationMinutes) {
          setTimeRemaining(quiz.durationMinutes * 60) // Convert minutes to seconds
        }

        toast.success("Quiz started! Good luck!")
      }
    } catch (error) {
      console.error("Error starting quiz:", error)
      toast.error("Failed to start quiz")
    }
  }

  const handleAnswerSelect = (questionId: number, optionId: number, isMultiSelect = false) => {
    const timeSpent = questionStartTime ? (Date.now() - questionStartTime.getTime()) / 1000 : 0

    setUserAnswers((prev) => {
      const newAnswers = new Map(prev)
      const existingAnswer = newAnswers.get(questionId)

      if (isMultiSelect) {
        // Handle multiple select questions
        const currentSelections = existingAnswer?.selectedOptionIds || []
        let newSelections: number[]

        if (currentSelections.includes(optionId)) {
          // Remove if already selected
          newSelections = currentSelections.filter((id) => id !== optionId)
        } else {
          // Add if not selected
          newSelections = [...currentSelections, optionId]
        }

        newAnswers.set(questionId, {
          questionId,
          selectedOptionIds: newSelections,
          timeSpent,
        })
      } else {
        // Handle single select questions
        newAnswers.set(questionId, {
          questionId,
          selectedOptionIds: [optionId],
          timeSpent,
        })
      }

      return newAnswers
    })
  }

  const handleTextAnswer = (questionId: number, textAnswer: string) => {
    const timeSpent = questionStartTime ? (Date.now() - questionStartTime.getTime()) / 1000 : 0

    setUserAnswers((prev) => {
      const newAnswers = new Map(prev)
      newAnswers.set(questionId, {
        questionId,
        selectedOptionIds: [],
        textAnswer,
        timeSpent,
      })
      return newAnswers
    })
  }

  const goToQuestion = (index: number) => {
    setCurrentQuestionIndex(index)
    setQuestionStartTime(new Date())
  }

  const nextQuestion = () => {
    if (quiz && currentQuestionIndex < quiz.questions.length - 1) {
      goToQuestion(currentQuestionIndex + 1)
    }
  }

  const previousQuestion = () => {
    if (currentQuestionIndex > 0) {
      goToQuestion(currentQuestionIndex - 1)
    }
  }

  const toggleFlag = (questionId: number) => {
    setFlaggedQuestions((prev) => {
      const newFlagged = new Set(prev)
      if (newFlagged.has(questionId)) {
        newFlagged.delete(questionId)
        toast.success("Question unflagged")
      } else {
        newFlagged.add(questionId)
        toast.success("Question flagged for review")
      }
      return newFlagged
    })
  }

  const handleSubmitQuiz = async () => {
    if (!attemptId || isSubmitting) return

    setIsSubmitting(true)
    setShowSubmitDialog(false)

    try {
      // Prepare answers in the required format
      const answers = Array.from(userAnswers.values()).map((answer) => ({
        question_id: answer.questionId,
        selected_option_ids: answer.selectedOptionIds,
        text_answer: answer.textAnswer || null,
      }))

      // Submit to API
      const response = await api.post(`v1/attempts/${attemptId}/submit`, {
        answers,
      })

      const result = response.data?.data

      if (result) {
        // Create quiz result from API response
        const quizResult: QuizResult = {
          id: result.id || 0,
          testId: result.test_id || 0,
          userId: result.user_id || 0,
          title: quiz?.title || "",
          score: result.score || 0,
          passingScore: quiz?.passingScore || 0,
          percentage: result.percentage || 0,
          totalQuestions: quiz?.questions.length || 0,
          correctAnswers: result.correct_answers || 0,
          durationTaken: result.duration_taken || 0,
          status: result.status || "completed",
          passed: result.passed || false,
          allowRetakes: quiz?.allowRetakes || false,
          answers: Array.from(userAnswers.values()),
        }

        setQuizResult(quizResult)
        setQuizState("completed")

        // Show success/failure toast
        if (quizResult.score >= (quiz?.passingScore ?? 0)) {
          quizResult.passed = true
          toast.success(`Congratulations! You passed with ${quizResult.score}`)
        } else {
          toast.error(`You scored ${quizResult.score}. You need ${quiz?.passingScore} to pass.`)
        }

        // Navigate to results page with state
        navigate(`/quiz/${quizId}/completed`, {
          state: quizResult,
        })
      }
    } catch (error) {
      console.error("Error submitting quiz:", error)
      toast.error("Failed to submit quiz. Please try again.")
    } finally {
      setIsSubmitting(false)
    }
  }

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}:${secs.toString().padStart(2, "0")}`
  }

  const getAnsweredCount = () => userAnswers.size
  const getProgressPercentage = () => (quiz ? (getAnsweredCount() / quiz.questions.length) * 100 : 0)

  const currentQuestion = quiz?.questions[currentQuestionIndex]
  const currentAnswer = currentQuestion ? userAnswers.get(currentQuestion.id) : undefined

  // Loading state
  if (loading) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <LoaderIcon className="animate-spin h-8 w-8 mx-auto mb-4 text-primary" />
          <p className="text-muted-foreground">Loading quiz...</p>
        </div>
      </div>
    )
  }

  // Not found state
  if (quizState === "not-found" || !quiz) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center p-4">
        <Card className="max-w-md w-full">
          <CardHeader className="text-center">
            <div className="mx-auto w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mb-4">
              <XCircleIcon className="w-8 h-8 text-destructive" />
            </div>
            <CardTitle>Quiz Not Found</CardTitle>
            <CardDescription>The quiz you're looking for doesn't exist or has been removed.</CardDescription>
          </CardHeader>
          <CardContent className="text-center space-y-2">
            <Button onClick={() => navigate("/quiz")} className="gap-2 w-full">
              <BookOpenIcon className="w-4 h-4" />
              Browse Quizzes
            </Button>
            <Button variant="outline" onClick={() => navigate("/")} className="gap-2 w-full">
              <HomeIcon className="w-4 h-4" />
              Go Home
            </Button>
          </CardContent>
        </Card>
      </div>
    )
  }

  // Start screen
  if (quizState === "start") {
    return (
      <div className="min-h-screen bg-background">
        <div className="container mx-auto px-4 py-8 max-w-4xl">
          <Card>
            <CardHeader className="text-center space-y-4">
              <div className="mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                <BookOpenIcon className="w-8 h-8 text-primary" />
              </div>
              <div className="space-y-2">
                <CardTitle className="text-3xl">{quiz.title}</CardTitle>
                {quiz.description && <CardDescription className="text-lg">{quiz.description}</CardDescription>}
              </div>
              <div className="flex justify-center gap-2 flex-wrap">
                <Badge variant="outline">{quiz.category}</Badge>
                <Badge variant="secondary">{quiz.difficulty}</Badge>
                <Badge variant="outline">{quiz.questions.length} Questions</Badge>
                {quiz.durationMinutes && <Badge variant="outline">{quiz.durationMinutes} Minutes</Badge>}
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-3">
                  <h4 className="font-semibold flex items-center gap-2">
                    <TargetIcon className="w-4 h-4" />
                    Quiz Details
                  </h4>
                  <div className="space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Total Questions:</span>
                      <span className="font-medium">{quiz.questions.length}</span>
                    </div>
                    <div className="flex justify-between">
                      <span>Passing Score:</span>
                      <span className="font-medium">{quiz.passingScore}%</span>
                    </div>
                    {quiz.durationMinutes && (
                      <div className="flex justify-between">
                        <span>Time Limit:</span>
                        <span className="font-medium">{quiz.durationMinutes} minutes</span>
                      </div>
                    )}
                    <div className="flex justify-between">
                      <span>Retakes Allowed:</span>
                      <span className="font-medium">{quiz.allowRetakes ? "Yes" : "No"}</span>
                    </div>
                  </div>
                </div>
                <div className="space-y-3">
                  <h4 className="font-semibold flex items-center gap-2">
                    <AlertCircleIcon className="w-4 h-4" />
                    Instructions
                  </h4>
                  <div className="space-y-2 text-sm text-muted-foreground">
                    <p>• Read each question carefully before selecting your answer</p>
                    <p>• You can navigate between questions using the navigation buttons</p>
                    <p>• Flag questions you want to review later</p>
                    {quiz.durationMinutes && <p>• Keep an eye on the timer in the top right</p>}
                    <p>• Submit your quiz when you're ready or when time runs out</p>
                  </div>
                </div>
              </div>
              <Separator />
              <div className="flex gap-4 justify-center">
                <Button variant="outline" onClick={() => navigate("/quiz")} className="gap-2">
                  <ArrowLeftIcon className="w-4 h-4" />
                  Back to Quizzes
                </Button>
                <Button onClick={startQuiz} size="lg" className="gap-2">
                  <PlayIcon className="w-5 h-5" />
                  Start Quiz
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  // Taking quiz state
  if (quizState === "taking") {
    return (
      <div className="min-h-screen bg-background flex flex-col">
        {/* Header */}
        <div className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
          <div className="container mx-auto px-4 py-4">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-4">
                <h1 className="text-xl font-semibold">{quiz.title}</h1>
                <Badge variant="outline">
                  Question {currentQuestionIndex + 1} of {quiz.questions.length}
                </Badge>
              </div>
              <div className="flex items-center gap-4">
                {quiz.durationMinutes && (
                  <div className="flex items-center gap-2">
                    <TimerIcon className="w-4 h-4" />
                    <span className={`font-mono ${timeRemaining < 300 ? "text-red-600" : ""}`}>
                      {formatTime(timeRemaining)}
                    </span>
                  </div>
                )}
                <Button variant="outline" size="sm" onClick={() => setShowSubmitDialog(true)} disabled={isSubmitting}>
                  {isSubmitting ? (
                    <>
                      <LoaderIcon className="w-4 h-4 mr-2 animate-spin" />
                      Submitting...
                    </>
                  ) : (
                    "Submit Quiz"
                  )}
                </Button>
              </div>
            </div>
            <div className="mt-4">
              <div className="flex items-center justify-between text-sm text-muted-foreground mb-2">
                <span>
                  Progress: {getAnsweredCount()} of {quiz.questions.length} answered
                </span>
                <span>{Math.round(getProgressPercentage())}% complete</span>
              </div>
              <Progress value={getProgressPercentage()} className="h-2" />
            </div>
          </div>
        </div>

        <div className="flex flex-1 overflow-hidden">
          {/* Question Navigation Sidebar */}
          <div className="w-64 border-r bg-muted/30 p-4 overflow-y-auto">
            <h3 className="font-semibold mb-4">Questions</h3>
            <div className="grid grid-cols-4 gap-2">
              {quiz.questions.map((question, index) => {
                const isAnswered = userAnswers.has(question.id)
                const isFlagged = flaggedQuestions.has(question.id)
                const isCurrent = index === currentQuestionIndex

                return (
                  <Button
                    key={question.id}
                    variant={isCurrent ? "default" : isAnswered ? "secondary" : "outline"}
                    size="sm"
                    className={`relative h-10 ${isFlagged ? "ring-2 ring-orange-500" : ""}`}
                    onClick={() => goToQuestion(index)}
                  >
                    {index + 1}
                    {isFlagged && (
                      <FlagIcon className="absolute -top-1 -right-1 w-3 h-3 text-orange-500 fill-orange-500" />
                    )}
                  </Button>
                )
              })}
            </div>
            <div className="mt-4 space-y-2 text-xs">
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-primary rounded"></div>
                <span>Current</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 bg-secondary rounded"></div>
                <span>Answered</span>
              </div>
              <div className="flex items-center gap-2">
                <div className="w-3 h-3 border border-border rounded"></div>
                <span>Not answered</span>
              </div>
              <div className="flex items-center gap-2">
                <FlagIcon className="w-3 h-3 text-orange-500 fill-orange-500" />
                <span>Flagged</span>
              </div>
            </div>
          </div>

          {/* Main Question Area */}
          <div className="flex-1 flex flex-col">
            <div className="flex-1 p-6 overflow-y-auto">
              <div className="max-w-4xl mx-auto">
                {currentQuestion && (
                  <Card>
                    <CardHeader>
                      <div className="flex items-start justify-between">
                        <div className="space-y-2">
                          <div className="flex items-center gap-2">
                            <Badge variant="outline">Question {currentQuestionIndex + 1}</Badge>
                            <Badge variant="secondary">
                              {currentQuestion.points} point{currentQuestion.points !== 1 ? "s" : ""}
                            </Badge>
                            <Badge variant="outline" className="capitalize">
                              {currentQuestion.type.replace("-", " ")}
                            </Badge>
                          </div>
                          <CardTitle className="text-xl leading-relaxed">{currentQuestion.text}</CardTitle>
                        </div>
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => toggleFlag(currentQuestion.id)}
                          className={flaggedQuestions.has(currentQuestion.id) ? "text-orange-500" : ""}
                        >
                          <FlagIcon
                            className={`w-4 h-4 ${flaggedQuestions.has(currentQuestion.id) ? "fill-current" : ""}`}
                          />
                        </Button>
                      </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      {/* Multiple Choice Questions */}
                      {currentQuestion.type === "multiple_choice" && (
                        <RadioGroup
                           value={
                            currentAnswer?.selectedOptionIds?.length && currentAnswer.selectedOptionIds.length > 0
                              ? currentAnswer.selectedOptionIds[0].toString() 
                              : ""
                          }
                          onValueChange={(value) => handleAnswerSelect(currentQuestion.id, Number.parseInt(value))}
                        >
                          <div className="space-y-3">
                            {currentQuestion.options.map((option, index) => (
                              <div
                                key={option.id}
                                className="flex items-center space-x-3 p-3 rounded-lg border hover:bg-muted/50"
                              >
                                <RadioGroupItem value={option.id.toString()} id={`option-${option.id}`} />
                                <Label htmlFor={`option-${option.id}`} className="flex-1 cursor-pointer">
                                  <span className="font-medium mr-2">{String.fromCharCode(65 + index)}.</span>
                                  {option.text}
                                </Label>
                              </div>
                            ))}
                          </div>
                        </RadioGroup>
                      )}

                      {/* Multiple Select Questions */}
                      {currentQuestion.type === "multiple_select" && (
                        <div className="space-y-3">
                          <p className="text-sm text-muted-foreground">Select all that apply:</p>
                          {currentQuestion.options.map((option, index) => (
                            <div
                              key={option.id}
                              className="flex items-center space-x-3 p-3 rounded-lg border hover:bg-muted/50"
                            >
                              <Checkbox
                                id={`option-${option.id}`}
                                checked={currentAnswer?.selectedOptionIds?.includes(option.id) || false}
                                onCheckedChange={() => handleAnswerSelect(currentQuestion.id, option.id, true)}
                              />
                              <Label htmlFor={`option-${option.id}`} className="flex-1 cursor-pointer">
                                <span className="font-medium mr-2">{String.fromCharCode(65 + index)}.</span>
                                {option.text}
                              </Label>
                            </div>
                          ))}
                        </div>
                      )}

                      {/* True/False Questions */}
                      {currentQuestion.type === "true_false" && (
                        <RadioGroup
                          value={currentAnswer?.selectedOptionIds?.[0]?.toString()}
                          onValueChange={(value) => handleAnswerSelect(currentQuestion.id, Number.parseInt(value))}
                        >
                          <div className="space-y-3">
                            {currentQuestion.options.map((option) => (
                              <div
                                key={option.id}
                                className="flex items-center space-x-3 p-3 rounded-lg border hover:bg-muted/50"
                              >
                                <RadioGroupItem value={option.id.toString()} id={`option-${option.id}`} />
                                <Label htmlFor={`option-${option.id}`} className="flex-1 cursor-pointer">
                                  {option.text}
                                </Label>
                              </div>
                            ))}
                          </div>
                        </RadioGroup>
                      )}

                      {/* Short Answer Questions */}
                      {currentQuestion.type === "short_answer" && (
                        <div className="space-y-3">
                          <Label htmlFor="text-answer">Your Answer:</Label>
                          <Textarea
                            id="text-answer"
                            placeholder="Type your answer here..."
                            value={currentAnswer?.textAnswer || ""}
                            onChange={(e) => handleTextAnswer(currentQuestion.id, e.target.value)}
                            className="min-h-[100px]"
                          />
                        </div>
                      )}
                    </CardContent>
                  </Card>
                )}
              </div>
            </div>

            {/* Navigation Footer */}
            <div className="border-t p-4">
              <div className="max-w-4xl mx-auto flex items-center justify-between">
                <Button
                  variant="outline"
                  onClick={previousQuestion}
                  disabled={currentQuestionIndex === 0}
                  className="gap-2 bg-transparent"
                >
                  <ArrowLeftIcon className="w-4 h-4" />
                  Previous
                </Button>
                <div className="text-sm text-muted-foreground">
                  Question {currentQuestionIndex + 1} of {quiz.questions.length}
                </div>
                <Button
                  variant="outline"
                  onClick={nextQuestion}
                  disabled={currentQuestionIndex === quiz.questions.length - 1}
                  className="gap-2 bg-transparent"
                >
                  Next
                  <ArrowRightIcon className="w-4 h-4" />
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Submit Confirmation Dialog */}
        <Dialog open={showSubmitDialog} onOpenChange={setShowSubmitDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Submit Quiz?</DialogTitle>
              <DialogDescription>
                Are you sure you want to submit your quiz? You have answered {getAnsweredCount()} out of{" "}
                {quiz.questions.length} questions.
                {getAnsweredCount() < quiz.questions.length && (
                  <span className="block mt-2 text-orange-600">
                    You have {quiz.questions.length - getAnsweredCount()} unanswered questions.
                  </span>
                )}
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" onClick={() => setShowSubmitDialog(false)} disabled={isSubmitting}>
                Continue Quiz
              </Button>
              <Button onClick={handleSubmitQuiz} disabled={isSubmitting}>
                {isSubmitting ? (
                  <>
                    <LoaderIcon className="w-4 h-4 mr-2 animate-spin" />
                    Submitting...
                  </>
                ) : (
                  "Submit Quiz"
                )}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    )
  }

  // Results Screen
  if (quizState === "completed") {
    const passed = true;
    const correctAnswers =
      quizResult?.answers.filter((answer) => {
        const question = quiz?.questions.find((q) => q.id === answer.questionId)
        if (question) {
          const selectedOptionIds = answer.selectedOptionIds
          const selectedOptions = question.options.filter((o) => selectedOptionIds.includes(o.id))
          const correctOptions = question.options.filter((o) => o.isCorrect)
          return selectedOptions.length === correctOptions.length && selectedOptions.every((o) => o.isCorrect)
        }
        return false
      }).length || 0

    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
        <header className="w-full px-6 py-4 bg-white/80 backdrop-blur-md shadow-sm border-b border-white/20">
          <div className="max-w-4xl mx-auto flex items-center justify-between">
            <button
              onClick={() => navigate("/quiz")}
              className="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors"
            >
              <HomeIcon className="w-4 h-4" />
              Back to Quizzes
            </button>
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                <TrophyIcon className="w-5 h-5 text-white" />
              </div>
              <span className="font-semibold text-gray-900">QuizMaster</span>
            </div>
          </div>
        </header>

        <main className="max-w-4xl mx-auto px-6 py-12">
          <div className="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 text-center">
            {/* Result Icon */}
            <div
              className={`w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 ${
                passed ? "bg-green-100" : "bg-red-100"
              }`}
            >
              {passed ? (
                <TrophyIcon className="w-12 h-12 text-green-600" />
              ) : (
                <XCircleIcon className="w-12 h-12 text-red-600" />
              )}
            </div>

            {/* Result Message */}
            <h1 className={`text-3xl font-bold mb-4 ${passed ? "text-green-600" : "text-red-600"}`}>
              {passed ? "Congratulations!" : "Keep Trying!"}
            </h1>

            <p className="text-gray-600 text-lg mb-8">
              {passed
                ? `You passed the quiz with a score of ${quizResult?.percentage}%!`
                : `You scored ${quizResult?.percentage}%. You need ${quiz?.passingScore}% to pass.`}
            </p>

            {/* Score Details */}
            <div className="grid md:grid-cols-3 gap-6 mb-8">
              <div className="bg-gray-50 rounded-xl p-6">
                <div className={`text-3xl font-bold mb-2 ${passed ? "text-green-600" : "text-red-600"}`}>
                  {quizResult?.percentage}%
                </div>
                <div className="text-gray-600">Your Score</div>
              </div>
              <div className="bg-gray-50 rounded-xl p-6">
                <div className="text-3xl font-bold text-blue-600 mb-2">
                  {correctAnswers}/{quiz?.questions.length}
                </div>
                <div className="text-gray-600">Correct Answers</div>
              </div>
              <div className="bg-gray-50 rounded-xl p-6">
                <div className="text-3xl font-bold text-gray-600 mb-2">{quiz?.passingScore}%</div>
                <div className="text-gray-600">Passing Score</div>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              {/* {quiz?.settings?.showCorrectAnswers && (
                <button
                  onClick={() => setQuizState("review")}
                  className="flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-colors"
                >
                  <EyeIcon className="w-4 h-4" />
                  Review Answers
                </button>
              )} */}

              {quiz?.allowRetakes && (
                <button
                  onClick={startQuiz}
                  className="flex items-center justify-center gap-2 px-6 py-3 bg-gray-600 text-white rounded-xl font-medium hover:bg-gray-700 transition-colors"
                >
                  <RotateCcwIcon className="w-4 h-4" />
                  Retake Quiz
                </button>
              )}

              <button
                onClick={() => navigate("/quiz")}
                className="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-colors"
              >
                Browse More Quizzes
              </button>
            </div>
          </div>
        </main>
      </div>
    )
  }

  // Review Screen
  if (quizState === "review") {
    const userAnswer = quizResult?.answers.find((a) => a.questionId === quiz?.questions[currentQuestionIndex].id)
    const selectedOptionIds = userAnswer?.selectedOptionIds || []
    const correctOptionIds =
      quiz?.questions[currentQuestionIndex].options.filter((o) => o.isCorrect).map((o) => o.id) || []
    const isCorrect =
      selectedOptionIds.length === correctOptionIds.length &&
      selectedOptionIds.every((id) => correctOptionIds.includes(id))

    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
        <header className="w-full px-6 py-4 bg-white/80 backdrop-blur-md shadow-sm border-b border-white/20">
          <div className="max-w-6xl mx-auto flex items-center justify-between">
            <div className="flex items-center gap-4">
              <button
                onClick={() => setQuizState("completed")}
                className="flex items-center gap-2 text-gray-600 hover:text-blue-600 transition-colors"
              >
                <ChevronLeftIcon className="w-4 h-4" />
                Back to Results
              </button>
              <h1 className="text-lg font-semibold text-gray-900">Review: {quiz?.title}</h1>
            </div>
            <div className="text-sm text-gray-600">
              Question {currentQuestionIndex + 1} of {quiz?.questions.length}
            </div>
          </div>
        </header>

        <div className="max-w-6xl mx-auto px-6 py-8">
          <div className="grid lg:grid-cols-4 gap-8">
            {/* Question Navigation */}
            <div className="lg:col-span-1">
              <div className="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 sticky top-24">
                <h3 className="font-semibold text-gray-900 mb-4">Questions</h3>
                <div className="grid grid-cols-4 gap-2">
                  {quiz?.questions.map((_, index) => {
                    const answer = quizResult?.answers.find((a) => a.questionId === quiz?.questions[index].id)
                    const selectedOptionIds = answer?.selectedOptionIds || []
                    const correctOptionIds =
                      quiz?.questions[index].options.filter((o) => o.isCorrect).map((o) => o.id) || []
                    const wasCorrect =
                      selectedOptionIds.length === correctOptionIds.length &&
                      selectedOptionIds.every((id) => correctOptionIds.includes(id))
                    const isCurrent = index === currentQuestionIndex

                    return (
                      <button
                        key={index}
                        onClick={() => setCurrentQuestionIndex(index)}
                        className={`w-10 h-10 rounded-lg text-sm font-medium transition-all ${
                          isCurrent
                            ? "bg-blue-600 text-white"
                            : wasCorrect
                              ? "bg-green-100 text-green-700 hover:bg-green-200"
                              : selectedOptionIds.length > 0
                                ? "bg-red-100 text-red-700 hover:bg-red-200"
                                : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                        }`}
                      >
                        {index + 1}
                      </button>
                    )
                  })}
                </div>
              </div>
            </div>

            {/* Review Content */}
            <div className="lg:col-span-3">
              <div className="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-8">
                {/* Question Header */}
                <div className="flex items-center justify-between mb-6">
                  <div className="flex items-center gap-3">
                    <span className="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                      Question {currentQuestionIndex + 1}
                    </span>
                    <div className="w-px h-6 bg-gray-300"></div>
                    <div className="text-sm text-gray-600">
                      {Math.round(((currentQuestionIndex + 1) / quiz?.questions.length) * 100)}% Complete
                    </div>
                  </div>
                  <button
                    onClick={() => toggleFlag(quiz?.questions[currentQuestionIndex].id)}
                    className={`flex items-center gap-2 px-3 py-2 rounded-lg transition-colors ${
                      flaggedQuestions.has(quiz?.questions[currentQuestionIndex].id)
                        ? "bg-orange-100 text-orange-700"
                        : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                    }`}
                  >
                    <FlagIcon className="w-4 h-4" />
                    {flaggedQuestions.has(quiz?.questions[currentQuestionIndex].id) ? "Flagged" : "Flag"}
                  </button>
                </div>

                {/* Question */}
                <h2 className="text-2xl font-bold text-gray-900 mb-8 leading-relaxed">
                  {quiz?.questions[currentQuestionIndex].text}
                </h2>

                {/* Options */}
                <div className="space-y-4 mb-8">
                  {quiz?.questions[currentQuestionIndex].options.map((option) => (
                    <div
                      key={option.id}
                      className={`flex items-center p-4 rounded-xl border-2 transition-all ${
                        selectedOptionIds.includes(option.id)
                          ? "border-blue-500 bg-blue-50"
                          : option.isCorrect
                            ? "border-green-500 bg-green-50"
                            : "border-gray-200 hover:border-gray-300 hover:bg-gray-50"
                      }`}
                    >
                      <span className="text-gray-900 text-lg">{option.text}</span>
                      {selectedOptionIds.includes(option.id) && !option.isCorrect && (
                        <XCircleIcon className="w-4 h-4 text-red-600 ml-4" />
                      )}
                      {option.isCorrect && <CheckCircleIcon className="w-4 h-4 text-green-600 ml-4" />}
                    </div>
                  ))}
                </div>

                {/* Explanation */}
                {quiz?.questions[currentQuestionIndex].explanation && (
                  <div className="bg-gray-50 rounded-xl p-6 mb-8">
                    <h3 className="font-semibold text-gray-900 mb-3">Explanation:</h3>
                    <p className="text-gray-700 text-lg">{quiz?.questions[currentQuestionIndex].explanation}</p>
                  </div>
                )}

                {/* Navigation */}
                <div className="flex items-center justify-between">
                  <button
                    onClick={() => setCurrentQuestionIndex(Math.max(0, currentQuestionIndex - 1))}
                    disabled={currentQuestionIndex === 0}
                    className="flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <ChevronLeftIcon className="w-4 h-4" />
                    Previous
                  </button>

                  <button
                    onClick={() =>
                      setCurrentQuestionIndex(Math.min(quiz?.questions.length - 1, currentQuestionIndex + 1))
                    }
                    disabled={currentQuestionIndex === quiz?.questions.length - 1}
                    className="flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    Next
                    <ChevronRightIcon className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return null
}
