"use client"

import { useState, useEffect } from "react"
import { X, Eye, Users, Target, Settings } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Separator } from "@/components/ui/separator"
import { ScrollArea } from "@/components/ui/scroll-area"

import api from "@/lib/axios"

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

interface QuestionSetPreviewModalProps {
  questionSetId: string
  isOpen: boolean
  onClose: () => void
}

export function QuestionSetPreviewModal({ questionSetId, isOpen, onClose }: QuestionSetPreviewModalProps) {
  useEffect(() => {
    if (questionSetId != null) {
      api.get(`/v1/management/tests/${questionSetId}`)
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
              type: q.question_type === "multiple_choice" ? "multiple-choice" : (q.question_type?.replace("_", "-") || "multiple-choice"),
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
      .finally()
    }
  }, [questionSetId])

  const [questionSet, setQuestionSet] = useState<QuestionSet>()

  if (!questionSet) return null

  const totalPoints = questionSet.questions?.reduce((sum, q) => sum + (q.points || 1), 0) || 0
  const hasQuestions = questionSet.questions && questionSet.questions.length > 0

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <DialogHeader className="space-y-3">
          <div className="flex items-center justify-between">
            <DialogTitle className="text-2xl font-bold flex items-center gap-2">
              <Eye className="w-6 h-6" />
              Question Set Preview
            </DialogTitle>
          </div>
        </DialogHeader>

        <ScrollArea className="flex-1 overflow-y-auto">
          <div className="space-y-6 pr-4">
            {/* Quiz Overview */}
            <Card>
              <CardHeader>
                <div className="space-y-3">
                  <div className="flex items-start justify-between">
                    <div className="space-y-2">
                      <CardTitle className="text-2xl">{questionSet.title || "Untitled Quiz"}</CardTitle>
                      {questionSet.description && (
                        <p className="text-muted-foreground text-base">{questionSet.description}</p>
                      )}
                    </div>
                  </div>

                  <div className="flex gap-2 flex-wrap">
                    <Badge variant="outline">{questionSet.category || "General"}</Badge>
                    <Badge variant="secondary">{questionSet.difficulty}</Badge>
                    <Badge variant="outline">
                      {questionSet.questions?.length || 0} Questions
                    </Badge>
                    <Badge variant="outline">{totalPoints} Points Total</Badge>
                  </div>
                </div>
              </CardHeader>
            </Card>

            {/* Quiz Statistics */}
            {/* {(questionSet.completions !== undefined || questionSet.averageScore !== undefined) && (
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg flex items-center gap-2">
                    <Users className="w-5 h-5" />
                    Performance Stats
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div className="text-center">
                      <div className="text-2xl font-bold">{questionSet.completions || 0}</div>
                      <div className="text-sm text-muted-foreground">Completions</div>
                    </div>
                    <div className="text-center">
                      <div className="text-2xl font-bold">
                        {questionSet.averageScore ? `${questionSet.averageScore}%` : "-"}
                      </div>
                      <div className="text-sm text-muted-foreground">Avg Score</div>
                    </div>
                    <div className="text-center">
                      <div className="text-2xl font-bold">{questionSet.settings?.passingScore || 70}%</div>
                      <div className="text-sm text-muted-foreground">Passing Score</div>
                    </div>
                    <div className="text-center">
                      <div className="text-2xl font-bold">
                        {questionSet.settings?.timeLimit ? `${questionSet.settings.timeLimit}m` : "∞"}
                      </div>
                      <div className="text-sm text-muted-foreground">Time Limit</div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )} */}

            {/* Quiz Settings */}
            {questionSet && (
              <Card>
                <CardHeader>
                  <CardTitle className="text-lg flex items-center gap-2">
                    <Settings className="w-5 h-5" />
                    Quiz Settings
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                      <span className="text-sm font-medium">Public Access</span>
                      <Badge variant={questionSet.isPublic ? "default" : "secondary"}>
                        {questionSet.isPublic ? "Enabled" : "Disabled"}
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                      <span className="text-sm font-medium">Allow Retakes</span>
                      <Badge variant={questionSet.allowRetakes !== false ? "default" : "secondary"}>
                        {questionSet.allowRetakes !== false ? "Enabled" : "Disabled"}
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                      <span className="text-sm font-medium">Shuffle Questions</span>
                      <Badge variant={questionSet.shuffleQuestions ? "default" : "secondary"}>
                        {questionSet.shuffleQuestions ? "Enabled" : "Disabled"}
                      </Badge>
                    </div>
                    <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                      <span className="text-sm font-medium">Show Correct Answers</span>
                      <Badge variant={questionSet.showCorrectAnswers !== false ? "default" : "secondary"}>
                        {questionSet.showCorrectAnswers !== false ? "Enabled" : "Disabled"}
                      </Badge>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            <Separator />

            {/* Questions Preview */}
            <Card>
              <CardHeader>
                <CardTitle className="text-lg flex items-center gap-2">
                  <Target className="w-5 h-5" />
                  Questions Overview
                </CardTitle>
              </CardHeader>
              <CardContent>
                {hasQuestions ? (
                  <div className="space-y-6">
                    {questionSet.questions.map((question, index) => (
                      <div key={question.id} className="space-y-3 p-4 border rounded-lg">
                        <div className="flex items-start justify-between">
                          <div className="flex items-center gap-3">
                            <Badge variant="outline" className="text-sm font-medium">
                              Q{index + 1}
                            </Badge>
                            <Badge variant="secondary" className="text-xs">
                              {question.type?.replace("-", " ") || "Multiple Choice"}
                            </Badge>
                          </div>
                          <Badge variant="outline" className="text-xs">
                            {question.points || 1} {(question.points || 1) === 1 ? "point" : "points"}
                          </Badge>
                        </div>

                        <div className="space-y-3">
                          <h4 className="font-medium text-base">{question.text || "Question text not entered"}</h4>

                          <div className="space-y-2 ml-4">
                              {question.options.map((option, optionIndex) => (
                                <div key={option.id} className="flex items-center gap-3">
                                  <span className="text-sm text-muted-foreground font-mono">
                                    {String.fromCharCode(65 + optionIndex)}.
                                  </span>
                                  <span
                                    className={`flex-1 ${option.isCorrect ? "font-medium text-green-600 dark:text-green-400" : ""}`}
                                  >
                                    {option.text || "Option not entered"}
                                  </span>
                                  {option.isCorrect && (
                                    <Badge
                                      variant="outline"
                                      className="text-xs bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800"
                                    >
                                      Correct
                                    </Badge>
                                  )}
                                </div>
                              ))}
                          </div>

                          {question.explanation && (
                            <div className="mt-3 p-3 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
                              <p className="text-sm text-blue-800 dark:text-blue-200">
                                <span className="font-medium">Explanation:</span> {question.explanation}
                              </p>
                            </div>
                          )}
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    <Target className="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>No questions have been added to this question set yet.</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </ScrollArea>

        {/* Footer */}
        <div className="flex items-center justify-end pt-4 border-t">
          <Button onClick={onClose}>Close Preview</Button>
        </div>
      </DialogContent>
    </Dialog>
  )
}
