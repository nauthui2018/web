"use client"

import { useState, useEffect } from "react"
import { ArrowLeftIcon, Plus, Trash2, SaveIcon, EyeIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Switch } from "@/components/ui/switch"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import api from "@/lib/axios"
import { toast } from "sonner"

interface Option {
  id: number
  text: string
  isCorrect: boolean
  explanation?: string
}
type QuestionType = "multiple_choice" | "multiple_select" | "true_false" | "short_answer"

interface Question {
  id: number
  text: string
  options: Option[]
  type: QuestionType
  points: number
  timeLimit?: number
  explanation?: string
}

interface QuestionSet {
  title: string
  description: string
  category: string
  difficulty: "Beginner" | "Intermediate" | "Advanced"
  status: "Draft" | "Active"
  isPublic: boolean
  timeLimit?: number
  passingScore: number
  allowRetakes: boolean
  shuffleQuestions: boolean
  showCorrectAnswers: boolean
  questions: Question[]
}

interface Category {
  id: number;
  name: string;
}

export function CreateQuestionSet({ onNavigate, editId }: { onNavigate?: (url: string) => void; editId?: string }) {
  const [isEditMode, setIsEditMode] = useState(!!editId)
  const [isLoading, setIsLoading] = useState(!!editId)

  const [categories, setCategories] = useState<Category[]>([])
  const [categoriesLoading, setCategoriesLoading] = useState(false)
  const [categoriesError, setCategoriesError] = useState<string | null>(null)

  const [questionSet, setQuestionSet] = useState<QuestionSet>({
    title: "",
    description: "",
    category: "",
    difficulty: "Beginner",
    status: "Draft",
    isPublic: false,
    timeLimit: undefined,
    passingScore: 70,
    allowRetakes: true,
    shuffleQuestions: false,
    showCorrectAnswers: true,
    questions: [
      {
        id: 1,
        text: "",
        type: "multiple_choice",
        points: 1,
        options: [
          { id: 1, text: "", isCorrect: false },
          { id: 2, text: "", isCorrect: false },
          { id: 3, text: "", isCorrect: false },
          { id: 4, text: "", isCorrect: false },
        ],
      },
    ],
  })

  const [activeTab, setActiveTab] = useState("details")

  // Fetch categories on mount (create mode only)
  useEffect(() => {
    setCategoriesLoading(true)
    setCategoriesError(null)
    api.get("/v1/categories")
      .then((res) => {
        const items = res.data?.data?.items || []
        setCategories(items)
      })
      .catch(() => setCategoriesError("Failed to load categories"))
      .finally(() => setCategoriesLoading(false))
  }, [])

  // Load existing question set data if editing
  useEffect(() => {
    if (editId) {
      setIsLoading(true)
      api.get(`/v1/management/tests/${editId}`)
        .then((res) => {
          const data = res.data?.data
          if (data) {
            setQuestionSet({
              title: data.title || "",
              description: data.description || "",
              category: data.category?.name || "",
              difficulty: data.difficulty_level || "Beginner",
              status: data.is_active ? "Active" : "Draft",
              isPublic: data.is_public || false,
              timeLimit: data.duration_minutes || undefined,
              passingScore: data.passing_score || 70,
              allowRetakes: true, // Not in API, default true
              shuffleQuestions: false, // Not in API, default false
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
          }
        })
        .catch(() => {
          // Optionally handle error
        })
        .finally(() => setIsLoading(false))
    }
  }, [editId])

  const updateQuestionSet = (updates: Partial<QuestionSet>) => {
    setQuestionSet((prev) => ({ ...prev, ...updates }))
  }

  const addQuestion = () => {
    const newQuestion: Question = {
      id: questionSet.questions.length + 1,
      text: "",
      type: "multiple_choice",
      points: 1,
      options: [
        { id: 1, text: "", isCorrect: false },
        { id: 2, text: "", isCorrect: false },
        { id: 3, text: "", isCorrect: false },
        { id: 4, text: "", isCorrect: false },
      ],
    }
    updateQuestionSet({
      questions: [...questionSet.questions, newQuestion],
    })
  }

  const changeQuestionType = (questionId: number, newType: QuestionType) => {
    updateQuestionSet({
      questions: questionSet.questions.map((q) =>
        q.id === questionId
          ? {
              ...q,
              type: newType,
              options:
                newType === "true_false"
                  ? [
                      { id: 1, text: "True", isCorrect: false },
                      { id: 2, text: "False", isCorrect: false },
                    ]
                  : [
                      { id: 1, text: "", isCorrect: false },
                      { id: 2, text: "", isCorrect: false },
                      { id: 3, text: "", isCorrect: false },
                      { id: 4, text: "", isCorrect: false },
                    ],
            }
          : q
      ),
    });
};


  const removeQuestion = (questionId: number) => {
    if (questionSet.questions.length > 1) {
      updateQuestionSet({
        questions: questionSet.questions.filter((q) => q.id !== questionId),
      })
    }
  }

  const updateQuestion = (questionId: number, updates: Partial<Question>, isTypeChanged: boolean) => {
    updateQuestionSet({
      questions: questionSet.questions.map((q) => (q.id === questionId ? { ...q, ...updates } : q)),
    })
    if (isTypeChanged && updates.type) {
      changeQuestionType(questionId, updates.type as QuestionType);
    }
  }

  const updateOption = (questionId: number, optionId: number, text: string) => {
    updateQuestionSet({
      questions: questionSet.questions.map((q) =>
        q.id === questionId
          ? {
              ...q,
              options: q.options.map((opt) => (opt.id === optionId ? { ...opt, text } : opt)),
            }
          : q,
      ),
    })
  }

 const setCorrectOption = (questionId: number, optionId: number) => {
  updateQuestionSet({
    questions: questionSet.questions.map((q) =>
      q.id === questionId
        ? {
            ...q,
            options: q.options.map((opt) => {
              if (q.type === "multiple_choice" || q.type === "true_false") {
                // Only one can be correct
                return { ...opt, isCorrect: opt.id === optionId };
              } else {
                // Toggle for multiple choice
                return opt.id === optionId
                  ? { ...opt, isCorrect: !opt.isCorrect }
                  : opt;
              }
            }),
          }
        : q,
    ),
  });
};


  const handleSave = async () => {
    if (!questionSet.title.trim()) {
      toast.error("Please enter a title for the question set")
      return
    }

    const validQuestions = questionSet.questions.filter(
      (q) => q.text.trim() && q.options.some((opt) => opt.text.trim() && opt.isCorrect),
    )

    if (validQuestions.length === 0) {
      alert("Please add at least one complete question with a correct answer")
      return
    }

    if (isEditMode && editId) {
      // Update existing question set via API
      try {
        // Find selected category id
        const selectedCategory = categories.find((cat) => cat.name === questionSet.category)
        const category_id = selectedCategory ? selectedCategory.id : undefined
        const requestBody = {
          title: questionSet.title.trim(),
          description: questionSet.description.trim(),
          category_id,
          duration_minutes: questionSet.timeLimit || 0,
          is_active: questionSet.status === "Active",
          is_public: questionSet.isPublic,
          show_correct_answer: questionSet.showCorrectAnswers,
          passing_score: questionSet.passingScore,
          difficulty_level: questionSet.difficulty,
          questions: validQuestions.map((q, idx) => ({
            question_text: q.text,
            question_type: q.type,
            options: q.options.map((opt) => ({
              id: opt.id,
              text: opt.text,
              is_correct: opt.isCorrect,
            })),
            points: q.points,
            order: idx + 1,
          })),
        }
        await api.put(`/v1/management/tests/${editId}`, requestBody)
        toast.success("Question Set has been updated successfully", {
          description: `${questionSet.title}`,
        })
        window.dispatchEvent(new CustomEvent("questionSetsUpdated"))
        onNavigate?.("question-sets")
        return
      } catch (error: any) {
        toast.error("Failed to update question set: " + (error?.response?.data?.message || error.message || "Unknown error"))
        return
      }
    }

    // API call for creating new question set
    try {
      // Find selected category id
      const selectedCategory = categories.find((cat) => cat.name === questionSet.category)
      const category_id = selectedCategory ? selectedCategory.id : undefined
      const requestBody = {
        title: questionSet.title.trim(),
        description: questionSet.description.trim(),
        category_id,
        duration_minutes: questionSet.timeLimit || 0,
        is_active: questionSet.status === "Active",
        is_public: questionSet.isPublic,
        passing_score: questionSet.passingScore,
        difficulty_level: questionSet.difficulty,
        questions: validQuestions.map((q, idx) => ({
          question_text: q.text,
          question_type: q.type,
          options: q.options.map((opt) => ({
            id: opt.id,
            text: opt.text,
            is_correct: opt.isCorrect,
          })),
          points: q.points,
          order: idx + 1,
        })),
      }
      await api.post("/v1/management/tests", requestBody)
      toast.success("Question Set has been created successfully", {
          description: `${questionSet.title}`,
      })
      window.dispatchEvent(new CustomEvent("questionSetsUpdated"))
      onNavigate?.("question-sets")
    } catch (error: any) {
      toast.error("Failed to create question set: " + (error?.response?.data?.message || error.message || "Unknown error"))
    }
  }

  const totalPoints = questionSet.questions.reduce((sum, q) => sum + q.points, 0)

  if (isLoading) {
    return (
      <div className="flex flex-col gap-6 py-6">
        <div className="px-4 lg:px-6">
          <div className="flex items-center justify-center py-12">
            <div className="text-center">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
              <p className="text-muted-foreground">Loading question set...</p>
            </div>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="flex flex-col gap-6 py-6">
      {/* Header */}
      <div className="px-4 lg:px-6">
        <div className="flex items-center gap-4 mb-4">
          <Button variant="ghost" size="sm" onClick={() => onNavigate?.("question-sets")} className="gap-2">
            <ArrowLeftIcon className="w-4 h-4" />
            Back to Question Sets
          </Button>
        </div>
        <div className="flex flex-col gap-2">
          <h1 className="text-3xl font-bold tracking-tight">
            {isEditMode ? "Edit Question Set" : "Create New Question Set"}
          </h1>
          <p className="text-muted-foreground">
            {isEditMode
              ? "Update your quiz with new questions and settings."
              : "Build a comprehensive quiz with detailed settings and multiple question types."}
          </p>
        </div>
      </div>

      {/* Main Content */}
      <div className="px-4 lg:px-6">
        <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="details">Details & Settings</TabsTrigger>
            <TabsTrigger value="questions">Questions ({questionSet.questions.length})</TabsTrigger>
            <TabsTrigger value="preview">Preview</TabsTrigger>
          </TabsList>

          {/* Details & Settings Tab */}
          <TabsContent value="details" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Basic Information</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="title">Title *</Label>
                    <Input
                      id="title"
                      value={questionSet.title}
                      onChange={(e) => updateQuestionSet({ title: e.target.value })}
                      placeholder="Enter question set title..."
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="category">Category</Label>
                    <Select
                      value={questionSet.category}
                      onValueChange={(value) => updateQuestionSet({ category: value })}
                      disabled={categoriesLoading}
                    >
                      <SelectTrigger>
                        <SelectValue placeholder={categoriesLoading ? "Loading..." : "Select category"} />
                      </SelectTrigger>
                      <SelectContent>
                        {categories.map((cat) => (
                          <SelectItem key={cat.id} value={cat.name}>{cat.name}</SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {categoriesError && <div className="text-red-500 text-xs mt-1">{categoriesError}</div>}
                  </div>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="description">Description</Label>
                  <Textarea
                    id="description"
                    value={questionSet.description}
                    onChange={(e) => updateQuestionSet({ description: e.target.value })}
                    placeholder="Describe what this question set covers..."
                    className="min-h-[100px]"
                  />
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="difficulty">Difficulty Level</Label>
                    <Select
                      value={questionSet.difficulty}
                      onValueChange={(value: "Beginner" | "Intermediate" | "Advanced") =>
                        updateQuestionSet({ difficulty: value })
                      }
                    >
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Beginner">Beginner</SelectItem>
                        <SelectItem value="Intermediate">Intermediate</SelectItem>
                        <SelectItem value="Advanced">Advanced</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="space-y-2">
                    <Label>Status</Label>
                    <RadioGroup
                      value={questionSet.status}
                      onValueChange={(value: "Draft" | "Active") => updateQuestionSet({ status: value })}
                      className="flex gap-6"
                    >
                      <div className="flex items-center space-x-2">
                        <RadioGroupItem value="Draft" id="draft" />
                        <Label htmlFor="draft" className="font-normal">
                          Draft
                        </Label>
                      </div>
                      <div className="flex items-center space-x-2">
                        <RadioGroupItem value="Active" id="active" />
                        <Label htmlFor="active" className="font-normal">
                          Active
                        </Label>
                      </div>
                    </RadioGroup>
                    <p className="text-xs text-muted-foreground">
                      Draft question sets are not visible to users. Active question sets can be taken by users.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Quiz Settings</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="passingScore">Passing Score (%)</Label>
                    <Input
                      id="passingScore"
                      type="number"
                      min="0"
                      max="100"
                      value={questionSet.passingScore}
                      onChange={(e) => updateQuestionSet({ passingScore: Number.parseInt(e.target.value) || 70 })}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="timeLimit">Time Limit (minutes)</Label>
                    <Input
                      id="timeLimit"
                      type="number"
                      min="1"
                      value={questionSet.timeLimit || ""}
                      onChange={(e) =>
                        updateQuestionSet({
                          timeLimit: e.target.value ? Number.parseInt(e.target.value) : undefined,
                        })
                      }
                      placeholder="No time limit"
                    />
                  </div>
                </div>
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div className="space-y-0.5">
                      <Label>Public Access</Label>
                      <p className="text-sm text-muted-foreground">Allow anyone to take this quiz</p>
                    </div>
                    <Switch
                      checked={questionSet.isPublic}
                      onCheckedChange={(checked) => updateQuestionSet({ isPublic: checked })}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="space-y-0.5">
                      <Label>Allow Retakes</Label>
                      <p className="text-sm text-muted-foreground">Users can retake the quiz multiple times</p>
                    </div>
                    <Switch
                      checked={questionSet.allowRetakes}
                      onCheckedChange={(checked) => updateQuestionSet({ allowRetakes: checked })}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="space-y-0.5">
                      <Label>Shuffle Questions</Label>
                      <p className="text-sm text-muted-foreground">Randomize question order for each attempt</p>
                    </div>
                    <Switch
                      checked={questionSet.shuffleQuestions}
                      onCheckedChange={(checked) => updateQuestionSet({ shuffleQuestions: checked })}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="space-y-0.5">
                      <Label>Show Correct Answers</Label>
                      <p className="text-sm text-muted-foreground">Display correct answers after completion</p>
                    </div>
                    <Switch
                      checked={questionSet.showCorrectAnswers}
                      onCheckedChange={(checked) => updateQuestionSet({ showCorrectAnswers: checked })}
                    />
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Questions Tab */}
          <TabsContent value="questions" className="space-y-6">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-4">
                <h3 className="text-lg font-semibold">Questions</h3>
                <Badge variant="secondary">
                  {questionSet.questions.length} Questions • {totalPoints} Points Total
                </Badge>
              </div>
              <Button onClick={addQuestion} className="gap-2">
                <Plus className="w-4 h-4" />
                Add Question
              </Button>
            </div>

            <div className="space-y-6">
              {questionSet.questions.map((question, questionIndex) => (
                <Card key={question.id} className="border-2">
                  <CardContent className="p-6">
                    <div className="flex items-center justify-between mb-4">
                      <div className="flex items-center gap-3">
                        <Badge variant="outline" className="text-sm font-medium">
                          Q{questionIndex + 1}
                        </Badge>
                        <Select
                          value={question.type}
                          onValueChange={(value: "multiple_choice" | "multiple_select" | "true_false" | "short_answer") =>
                            updateQuestion(question.id, { type: value }, true)
                          }
                        >
                          <SelectTrigger className="w-40">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="multiple_choice">Multiple Choice</SelectItem>
                            <SelectItem value="multiple_select">Multiple Select</SelectItem>
                            <SelectItem value="true_false">True/False</SelectItem>
                            <SelectItem value="short_answer">Short Answer</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="flex items-center gap-2">
                        <div className="flex items-center gap-2">
                          <Label htmlFor={`points-${question.id}`} className="text-sm">
                            Points:
                          </Label>
                          <Input
                            id={`points-${question.id}`}
                            type="number"
                            min="1"
                            value={question.points}
                            onChange={(e) =>
                              updateQuestion(question.id, {
                                points: Number.parseInt(e.target.value) || 1,
                              },
                              false)
                            }
                            className="w-16"
                          />
                        </div>
                        {questionSet.questions.length > 1 && (
                          <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => removeQuestion(question.id)}
                            className="text-destructive hover:text-destructive hover:bg-destructive/10"
                          >
                            <Trash2 className="w-4 h-4" />
                          </Button>
                        )}
                      </div>
                    </div>

                    <div className="space-y-4">
                      <div className="space-y-2">
                        <Label htmlFor={`question-${question.id}`}>Question Text</Label>
                        <Textarea
                          id={`question-${question.id}`}
                          value={question.text}
                          onChange={(e) => updateQuestion(question.id, { text: e.target.value }, false)}
                          placeholder="Enter your question here..."
                          className="min-h-[80px]"
                        />
                      </div>

                      {question.type === "multiple_choice" && (
                        <div className="space-y-3">
                          <Label>Answer Options (Select the correct answer)</Label>
                          <div className="grid gap-3">
                            {question.options.map((option, optionIndex) => (
                              <div
                                key={option.id}
                                className={`flex items-center space-x-3 p-3 rounded-lg border-2 transition-all ${
                                  option.isCorrect
                                    ? "border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20"
                                    : "border-border hover:border-border/80"
                                }`}
                              >
                                <input
                                  type="radio"
                                  name={`question-${question.id}`}
                                  checked={option.isCorrect}
                                  onChange={() => setCorrectOption(question.id, option.id)}
                                  className="w-4 h-4 text-primary focus:ring-primary focus:ring-2"
                                />
                                <Badge variant="outline" className="text-xs font-mono min-w-[24px] justify-center">
                                  {String.fromCharCode(65 + optionIndex)}
                                </Badge>
                                <Input
                                  value={option.text}
                                  onChange={(e) => updateOption(question.id, option.id, e.target.value)}
                                  className="flex-1 border-0 bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0"
                                  placeholder={`Enter option ${String.fromCharCode(65 + optionIndex)}...`}
                                />
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      {question.type === "multiple_select" && (
                        <div className="space-y-3">
                          <Label>Answer Options (Select the correct answers)</Label>
                          <div className="grid gap-3">
                            {question.options.map((option, optionIndex) => (
                              <div
                                key={option.id}
                                className={`flex items-center space-x-3 p-3 rounded-lg border-2 transition-all ${
                                  option.isCorrect
                                    ? "border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20"
                                    : "border-border hover:border-border/80"
                                }`}
                              >
                                <input
                                  type="checkbox"
                                  name={`question-${question.id}`}
                                  checked={option.isCorrect}
                                  onChange={() => setCorrectOption(question.id, option.id)}
                                  className="w-4 h-4 text-primary focus:ring-primary focus:ring-2"
                                />
                                <Badge variant="outline" className="text-xs font-mono min-w-[24px] justify-center">
                                  {String.fromCharCode(65 + optionIndex)}
                                </Badge>
                                <Input
                                  value={option.text}
                                  onChange={(e) => updateOption(question.id, option.id, e.target.value)}
                                  className="flex-1 border-0 bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0"
                                  placeholder={`Enter option ${String.fromCharCode(65 + optionIndex)}...`}
                                />
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      {question.type === "true_false" && (
                        <div className="space-y-3">
                          <Label>Answer Options (Select the correct answer)</Label>
                          <div className="grid gap-3">
                            {question.options.map((option, optionIndex) => (
                              <div
                                key={option.id}
                                className={`flex items-center space-x-3 p-3 rounded-lg border-2 transition-all ${
                                  option.isCorrect
                                    ? "border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20"
                                    : "border-border hover:border-border/80"
                                }`}
                              >
                                <input
                                  type="radio"
                                  name={`question-${question.id}`}
                                  checked={option.isCorrect}
                                  onChange={() => setCorrectOption(question.id, option.id)}
                                  className="w-4 h-4 text-primary focus:ring-primary focus:ring-2"
                                />
                                <Badge variant="outline" className="text-xs font-mono min-w-[24px] justify-center">
                                  {String.fromCharCode(65 + optionIndex)}
                                </Badge>
                                <Input
                                  value={option.text}
                                  onChange={(e) => updateOption(question.id, option.id, e.target.value)}
                                  className="flex-1 border-0 bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0"
                                  placeholder={`Enter option ${String.fromCharCode(65 + optionIndex)}...`}
                                />
                              </div>
                            ))}
                          </div>
                        </div>
                      )}

                      <div className="space-y-2">
                        <Label htmlFor={`explanation-${question.id}`}>Explanation (Optional)</Label>
                        <Textarea
                          id={`explanation-${question.id}`}
                          value={question.explanation || ""}
                          onChange={(e) => updateQuestion(question.id, { explanation: e.target.value }, false)}
                          placeholder="Explain why this is the correct answer..."
                          className="min-h-[60px]"
                        />
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </TabsContent>

          {/* Preview Tab */}
          <TabsContent value="preview" className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <EyeIcon className="w-5 h-5" />
                  Quiz Preview
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <h2 className="text-2xl font-bold">{questionSet.title || "Untitled Quiz"}</h2>
                  {questionSet.description && <p className="text-muted-foreground">{questionSet.description}</p>}
                  <div className="flex gap-2 flex-wrap">
                    <Badge>{questionSet.category || "General"}</Badge>
                    <Badge variant="outline">{questionSet.difficulty}</Badge>
                    <Badge variant={questionSet.status === "Active" ? "default" : "secondary"}>
                      {questionSet.status}
                    </Badge>
                    <Badge variant="secondary">{questionSet.questions.length} Questions</Badge>
                    <Badge variant="secondary">{totalPoints} Points</Badge>
                  </div>
                </div>
                <Separator />
                <div className="space-y-4">
                  {questionSet.questions.map((question, index) => (
                    <div key={question.id} className="space-y-2">
                      <h4 className="font-medium">
                        {index + 1}. {question.text || "Question text not entered"}
                      </h4>
                      <div className="space-y-1 ml-4">
                          {question.options.map((option, optionIndex) => (
                            <div key={option.id} className="flex items-center gap-2">
                              <span className="text-sm text-muted-foreground">
                                {String.fromCharCode(65 + optionIndex)}.
                              </span>
                              <span className={option.isCorrect ? "font-medium text-green-600" : ""}>
                                {option.text || "Option not entered"}
                              </span>
                              {option.isCorrect && (
                                <Badge variant="outline" className="text-xs">
                                  Correct
                                </Badge>
                              )}
                            </div>
                          ))}
                        </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>

      {/* Footer Actions */}
      <div className="px-4 lg:px-6">
        <div className="flex items-center justify-between pt-6 border-t">
          <Button variant="outline" onClick={() => onNavigate?.("question-sets")}>
            Cancel
          </Button>
          <div className="flex gap-2">
            <Button variant="outline" onClick={() => setActiveTab("preview")}>
              <EyeIcon className="w-4 h-4 mr-2" />
              Preview
            </Button>
            <Button onClick={handleSave}>
              <SaveIcon className="w-4 h-4 mr-2" />
              {isEditMode ? "Update Question Set" : "Save Question Set"}
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}
