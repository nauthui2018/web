"use client"

import * as React from "react"
import {
  CheckCircleIcon,
  ArrowLeftIcon,
  ArrowRightIcon,
  FlagIcon,
  PlayIcon,
  RotateCcwIcon,
  TrophyIcon,
  XCircleIcon,
  BookOpenIcon,
  TargetIcon,
  TimerIcon,
  AlertCircleIcon,
  HomeIcon,
} from "lucide-react"

import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Progress } from "@/components/ui/progress"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Label } from "@/components/ui/label"
import { Separator } from "@/components/ui/separator"
import { Alert, AlertDescription } from "@/components/ui/alert"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"

interface QuizOption {
  id: number
  text: string
  isCorrect: boolean
}

interface QuizQuestion {
  id: number
  text: string
  options: QuizOption[]
  type: "multiple-choice" | "true-false" | "short-answer"
  points: number
  explanation?: string
}

interface QuizData {
  id: number
  title: string
  description?: string
  category: string
  difficulty: string
  questions: QuizQuestion[]
  settings: {
    timeLimit?: number // in minutes
    passingScore: number
    showCorrectAnswers: boolean
    allowRetakes: boolean
    shuffleQuestions: boolean
  }
}

interface UserAnswer {
  questionId: number
  selectedOptionId?: number
  textAnswer?: string
  isCorrect: boolean
  timeSpent: number
}

interface QuizResult {
  score: number
  percentage: number
  totalQuestions: number
  correctAnswers: number
  timeSpent: number
  passed: boolean
  answers: UserAnswer[]
}

// Sample quiz data - in real app, this would be fetched based on the ID
const sampleQuizzes: Record<string, QuizData> = {
  "1": {
    id: 1,
    title: "JavaScript Fundamentals",
    description: "Test your knowledge of JavaScript basics including variables, functions, and control structures.",
    category: "Programming",
    difficulty: "Beginner",
    settings: {
      timeLimit: 15,
      passingScore: 70,
      showCorrectAnswers: true,
      allowRetakes: true,
      shuffleQuestions: false,
    },
    questions: [
      {
        id: 1,
        text: "What is the correct way to declare a variable in JavaScript?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "var myVariable;", isCorrect: true },
          { id: 2, text: "variable myVariable;", isCorrect: false },
          { id: 3, text: "v myVariable;", isCorrect: false },
          { id: 4, text: "declare myVariable;", isCorrect: false },
        ],
        explanation: "In JavaScript, variables are declared using 'var', 'let', or 'const' keywords.",
      },
      {
        id: 2,
        text: "Which of the following is NOT a JavaScript data type?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "String", isCorrect: false },
          { id: 2, text: "Boolean", isCorrect: false },
          { id: 3, text: "Float", isCorrect: true },
          { id: 4, text: "Number", isCorrect: false },
        ],
        explanation: "JavaScript has Number type for all numeric values, but no specific 'Float' type.",
      },
      {
        id: 3,
        text: "What does the '===' operator do in JavaScript?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "Assigns a value", isCorrect: false },
          { id: 2, text: "Compares values only", isCorrect: false },
          { id: 3, text: "Compares values and types", isCorrect: true },
          { id: 4, text: "Creates a new variable", isCorrect: false },
        ],
        explanation: "The '===' operator performs strict equality comparison, checking both value and type.",
      },
      {
        id: 4,
        text: "How do you create a function in JavaScript?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "function myFunction() {}", isCorrect: true },
          { id: 2, text: "create myFunction() {}", isCorrect: false },
          { id: 3, text: "def myFunction() {}", isCorrect: false },
          { id: 4, text: "func myFunction() {}", isCorrect: false },
        ],
        explanation: "Functions in JavaScript are declared using the 'function' keyword.",
      },
      {
        id: 5,
        text: "What will console.log(typeof null) output?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "null", isCorrect: false },
          { id: 2, text: "undefined", isCorrect: false },
          { id: 3, text: "object", isCorrect: true },
          { id: 4, text: "string", isCorrect: false },
        ],
        explanation: "This is a known quirk in JavaScript - typeof null returns 'object' due to a legacy bug.",
      },
    ],
  },
  "2": {
    id: 2,
    title: "React Hooks Deep Dive",
    description: "Advanced concepts in React Hooks including useEffect, useContext, and custom hooks.",
    category: "React",
    difficulty: "Intermediate",
    settings: {
      timeLimit: 20,
      passingScore: 75,
      showCorrectAnswers: true,
      allowRetakes: true,
      shuffleQuestions: false,
    },
    questions: [
      {
        id: 1,
        text: "Which hook is used for side effects in React?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "useState", isCorrect: false },
          { id: 2, text: "useEffect", isCorrect: true },
          { id: 3, text: "useContext", isCorrect: false },
          { id: 4, text: "useReducer", isCorrect: false },
        ],
        explanation: "useEffect is the hook used for performing side effects in functional components.",
      },
      {
        id: 2,
        text: "What happens when you don't provide a dependency array to useEffect?",
        type: "multiple-choice",
        points: 1,
        options: [
          { id: 1, text: "It runs once on mount", isCorrect: false },
          { id: 2, text: "It runs on every render", isCorrect: true },
          { id: 3, text: "It never runs", isCorrect: false },
          { id: 4, text: "It throws an error", isCorrect: false },
        ],
        explanation: "Without a dependency array, useEffect runs after every render.",
      },
    ],
  },
}

type QuizState = "start" | "taking" | "completed" | "review" | "not-found"

interface QuizPageProps {
  quizId: string
}

export default function QuizPage({ quizId }: QuizPageProps) {
  const [quiz, setQuiz] = React.useState<QuizData | null>(null)
  const [quizState, setQuizState] = React.useState<QuizState>("start")
  const [currentQuestionIndex, setCurrentQuestionIndex] = React.useState(0)
  const [userAnswers, setUserAnswers] = React.useState<Map<number, UserAnswer>>(new Map())
  const [timeRemaining, setTimeRemaining] = React.useState<number>(0)
  const [startTime, setStartTime] = React.useState<Date | null>(null)
  const [questionStartTime, setQuestionStartTime] = React.useState<Date | null>(null)
  const [showSubmitDialog, setShowSubmitDialog] = React.useState(false)
  const [quizResult, setQuizResult] = React.useState<QuizResult | null>(null)
  const [flaggedQuestions, setFlaggedQuestions] = React.useState<Set<number>>(new Set())

  // Navigation functions
  const goHome = () => {
    window.location.href = "/"
    // Or if using React Router: navigate("/")
  }

  const goToQuizList = () => {
    window.location.href = "/quiz"
    // Or if using React Router: navigate("/quiz")
  }

  // Load quiz data
  React.useEffect(() => {
    // First try to load from localStorage (for user-created quizzes)
    const storedQuizzes = localStorage.getItem("questionSets")
    if (storedQuizzes) {
      try {
        const parsedQuizzes = JSON.parse(storedQuizzes)
        const foundQuiz = parsedQuizzes.find((q: any) => q.id.toString() === quizId)
        if (foundQuiz && foundQuiz.questions && foundQuiz.questions.length > 0) {
          // Transform stored quiz to match our interface
          const transformedQuiz: QuizData = {
            id: foundQuiz.id,
            title: foundQuiz.title,
            description: foundQuiz.description || "",
            category: foundQuiz.category || "General",
            difficulty: foundQuiz.difficulty || "Beginner",
            questions: foundQuiz.questions,
            settings: {
              timeLimit: foundQuiz.settings?.timeLimit,
              passingScore: foundQuiz.settings?.passingScore || 70,
              showCorrectAnswers: foundQuiz.settings?.showCorrectAnswers !== false,
              allowRetakes: foundQuiz.settings?.allowRetakes !== false,
              shuffleQuestions: foundQuiz.settings?.shuffleQuestions || false,
            },
          }
          setQuiz(transformedQuiz)
          return
        }
      } catch (error) {
        console.error("Error parsing stored quizzes:", error)
      }
    }

    // Fallback to sample quizzes
    const sampleQuiz = sampleQuizzes[quizId]
    if (sampleQuiz) {
      setQuiz(sampleQuiz)
    } else {
      setQuizState("not-found")
    }
  }, [quizId])

  // Timer effect
  React.useEffect(() => {
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

  const startQuiz = () => {
    setQuizState("taking")
    setStartTime(new Date())
    setQuestionStartTime(new Date())
    if (quiz?.settings.timeLimit) {
      setTimeRemaining(quiz.settings.timeLimit * 60) // Convert minutes to seconds
    }
  }

  const handleAnswerSelect = (questionId: number, optionId: number) => {
    const question = quiz?.questions.find((q) => q.id === questionId)
    const option = question?.options.find((o) => o.id === optionId)

    if (question && option) {
      const timeSpent = questionStartTime ? (Date.now() - questionStartTime.getTime()) / 1000 : 0

      setUserAnswers((prev) => {
        const newAnswers = new Map(prev)
        newAnswers.set(questionId, {
          questionId,
          selectedOptionId: optionId,
          isCorrect: option.isCorrect,
          timeSpent,
        })
        return newAnswers
      })
    }
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
      } else {
        newFlagged.add(questionId)
      }
      return newFlagged
    })
  }

  const calculateResult = (): QuizResult => {
    if (!quiz) {
      return {
        score: 0,
        percentage: 0,
        totalQuestions: 0,
        correctAnswers: 0,
        timeSpent: 0,
        passed: false,
        answers: [],
      }
    }

    const totalQuestions = quiz.questions.length
    const correctAnswers = Array.from(userAnswers.values()).filter((answer) => answer.isCorrect).length
    const score = correctAnswers
    const percentage = (correctAnswers / totalQuestions) * 100
    const totalTimeSpent = startTime ? (Date.now() - startTime.getTime()) / 1000 : 0
    const passed = percentage >= quiz.settings.passingScore

    return {
      score,
      percentage,
      totalQuestions,
      correctAnswers,
      timeSpent: totalTimeSpent,
      passed,
      answers: Array.from(userAnswers.values()),
    }
  }

  const handleSubmitQuiz = () => {
    const result = calculateResult()
    setQuizResult(result)
    setQuizState("completed")
    setShowSubmitDialog(false)
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

  // Not found state
  if (quizState === "not-found") {
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
            <Button onClick={goToQuizList} className="gap-2 w-full">
              <BookOpenIcon className="w-4 h-4" />
              Browse Quizzes
            </Button>
            <Button variant="outline" onClick={goHome} className="gap-2 w-full bg-transparent">
              <HomeIcon className="w-4 h-4" />
              Go Home
            </Button>
          </CardContent>
        </Card>
      </div>
    )
  }

  // Loading state
  if (!quiz) {
    return (
      <div className="min-h-screen bg-background flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading quiz...</p>
        </div>
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
                {quiz.settings.timeLimit && <Badge variant="outline">{quiz.settings.timeLimit} Minutes</Badge>}
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
                      <span className="font-medium">{quiz.settings.passingScore}%</span>
                    </div>
                    {quiz.settings.timeLimit && (
                      <div className="flex justify-between">
                        <span>Time Limit:</span>
                        <span className="font-medium">{quiz.settings.timeLimit} minutes</span>
                      </div>
                    )}
                    <div className="flex justify-between">
                      <span>Retakes Allowed:</span>
                      <span className="font-medium">{quiz.settings.allowRetakes ? "Yes" : "No"}</span>
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
                    {quiz.settings.timeLimit && <p>• Keep an eye on the timer in the top right</p>}
                    <p>• Submit your quiz when you're ready or when time runs out</p>
                  </div>
                </div>
              </div>
              <Separator />
              <div className="flex gap-4 justify-center">
                <Button variant="outline" onClick={goToQuizList} className="gap-2 bg-transparent">
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

  // Taking quiz
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
                {quiz.settings.timeLimit && (
                  <div className="flex items-center gap-2">
                    <TimerIcon className="w-4 h-4" />
                    <span className={`font-mono ${timeRemaining < 300 ? "text-red-600" : ""}`}>
                      {formatTime(timeRemaining)}
                    </span>
                  </div>
                )}
                <Button variant="outline" size="sm" onClick={() => setShowSubmitDialog(true)}>
                  Submit Quiz
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
                      {currentQuestion.type === "multiple-choice" && (
                        <RadioGroup
                          value={currentAnswer?.selectedOptionId?.toString()}
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
              <Button variant="outline" onClick={() => setShowSubmitDialog(false)}>
                Continue Quiz
              </Button>
              <Button onClick={handleSubmitQuiz}>Submit Quiz</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    )
  }

  // Results screen
  if (quizState === "completed" && quizResult) {
    return (
      <div className="min-h-screen bg-background">
        <div className="container mx-auto px-4 py-8 max-w-4xl">
          <Card>
            <CardHeader className="text-center space-y-4">
              <div
                className={`mx-auto w-20 h-20 rounded-full flex items-center justify-center ${
                  quizResult.passed ? "bg-green-100 text-green-600" : "bg-red-100 text-red-600"
                }`}
              >
                {quizResult.passed ? <TrophyIcon className="w-10 h-10" /> : <XCircleIcon className="w-10 h-10" />}
              </div>
              <div className="space-y-2">
                <CardTitle className="text-3xl">{quizResult.passed ? "Congratulations!" : "Quiz Complete"}</CardTitle>
                <CardDescription className="text-lg">
                  {quizResult.passed
                    ? `You passed the quiz with a score of ${quizResult.percentage.toFixed(1)}%`
                    : `You scored ${quizResult.percentage.toFixed(1)}%. The passing score is ${quiz.settings.passingScore}%`}
                </CardDescription>
              </div>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="text-center p-4 border rounded-lg">
                  <div className="text-3xl font-bold text-primary">{quizResult.correctAnswers}</div>
                  <div className="text-sm text-muted-foreground">Correct Answers</div>
                </div>
                <div className="text-center p-4 border rounded-lg">
                  <div className="text-3xl font-bold text-primary">{quizResult.percentage.toFixed(1)}%</div>
                  <div className="text-sm text-muted-foreground">Final Score</div>
                </div>
                <div className="text-center p-4 border rounded-lg">
                  <div className="text-3xl font-bold text-primary">{formatTime(Math.floor(quizResult.timeSpent))}</div>
                  <div className="text-sm text-muted-foreground">Time Spent</div>
                </div>
              </div>

              <div className="space-y-4">
                <Progress value={quizResult.percentage} className="h-3" />
                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>
                    {quizResult.correctAnswers} of {quizResult.totalQuestions} correct
                  </span>
                  <span>{quizResult.percentage.toFixed(1)}%</span>
                </div>
              </div>

              {quiz.settings.showCorrectAnswers && (
                <div className="space-y-4">
                  <Separator />
                  <div className="flex items-center justify-between">
                    <h3 className="text-lg font-semibold">Review Answers</h3>
                    <Button variant="outline" onClick={() => setQuizState("review")}>
                      View Detailed Review
                    </Button>
                  </div>
                </div>
              )}

              <div className="flex gap-4 justify-center flex-wrap">
                {quiz.settings.allowRetakes && (
                  <Button variant="outline" onClick={() => window.location.reload()} className="gap-2">
                    <RotateCcwIcon className="w-4 h-4" />
                    Retake Quiz
                  </Button>
                )}
                <Button variant="outline" onClick={goToQuizList} className="gap-2 bg-transparent">
                  <BookOpenIcon className="w-4 h-4" />
                  More Quizzes
                </Button>
                <Button onClick={goHome} className="gap-2">
                  <HomeIcon className="w-4 h-4" />
                  Back to Home
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  // Review screen
  if (quizState === "review" && quizResult) {
    return (
      <div className="min-h-screen bg-background">
        <div className="container mx-auto px-4 py-8">
          <div className="flex items-center justify-between mb-6">
            <Button variant="ghost" size="sm" onClick={() => setQuizState("completed")} className="gap-2">
              <ArrowLeftIcon className="w-4 h-4" />
              Back to Results
            </Button>
            <h1 className="text-2xl font-bold">Answer Review</h1>
            <Button onClick={goHome} className="gap-2">
              <HomeIcon className="w-4 h-4" />
              Exit
            </Button>
          </div>

          <div className="max-w-4xl mx-auto space-y-6">
            {quiz.questions.map((question, index) => {
              const userAnswer = quizResult.answers.find((a) => a.questionId === question.id)
              const selectedOption = question.options.find((o) => o.id === userAnswer?.selectedOptionId)
              const correctOption = question.options.find((o) => o.isCorrect)

              return (
                <Card
                  key={question.id}
                  className={`border-l-4 ${userAnswer?.isCorrect ? "border-l-green-500" : "border-l-red-500"}`}
                >
                  <CardHeader>
                    <div className="flex items-start justify-between">
                      <div className="space-y-2">
                        <div className="flex items-center gap-2">
                          <Badge variant="outline">Question {index + 1}</Badge>
                          <Badge variant={userAnswer?.isCorrect ? "default" : "destructive"}>
                            {userAnswer?.isCorrect ? "Correct" : "Incorrect"}
                          </Badge>
                        </div>
                        <CardTitle className="text-lg">{question.text}</CardTitle>
                      </div>
                      {userAnswer?.isCorrect ? (
                        <CheckCircleIcon className="w-6 h-6 text-green-500" />
                      ) : (
                        <XCircleIcon className="w-6 h-6 text-red-500" />
                      )}
                    </div>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      {question.options.map((option, optionIndex) => {
                        const isSelected = option.id === userAnswer?.selectedOptionId
                        const isCorrect = option.isCorrect

                        return (
                          <div
                            key={option.id}
                            className={`p-3 rounded-lg border ${
                              isCorrect
                                ? "bg-green-50 border-green-200 text-green-800"
                                : isSelected && !isCorrect
                                  ? "bg-red-50 border-red-200 text-red-800"
                                  : "bg-muted/30"
                            }`}
                          >
                            <div className="flex items-center gap-2">
                              <span className="font-medium">{String.fromCharCode(65 + optionIndex)}.</span>
                              <span>{option.text}</span>
                              {isSelected && (
                                <Badge variant="outline" className="ml-auto">
                                  Your Answer
                                </Badge>
                              )}
                              {isCorrect && (
                                <Badge variant="outline" className="ml-auto bg-green-100 text-green-800">
                                  Correct Answer
                                </Badge>
                              )}
                            </div>
                          </div>
                        )
                      })}
                    </div>

                    {question.explanation && (
                      <Alert>
                        <AlertCircleIcon className="h-4 w-4" />
                        <AlertDescription>
                          <strong>Explanation:</strong> {question.explanation}
                        </AlertDescription>
                      </Alert>
                    )}
                  </CardContent>
                </Card>
              )
            })}
          </div>
        </div>
      </div>
    )
  }

  return null
}
