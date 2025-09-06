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

interface UserAnswer {
    questionId: number
    selectedOptionIds: number[]
    textAnswer?: string
    timeSpent: number
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

interface QuizResult {
    id: number,
    testId?: number,
    userId?: number,
    title: string,
    score: number,
    percentage: number,
    passingScore: number,
    totalQuestions: number,
    correctAnswers: number,
    durationTaken: number,
    passed: boolean,
    allowRetakes: boolean
    answers: UserAnswer[]
}

interface QuizResultReviewModalProps {
    quizeResult: QuizResult
    isOpen: boolean;
    onClose: () => void
}

export function QuizResultReviewModal({ quizeResult, onClose, isOpen }: QuizResultReviewModalProps) {
    const [questionSet, setQuestionSet] = useState<QuestionSet>()
    const { title, score, passingScore, totalQuestions, correctAnswers, durationTaken, passed, answers } = quizeResult;
    const formatMinutes = (sec: number) => `${Math.floor(sec / 60)} min ${Math.round(sec % 60)}s`;

    const getTypeLabel = (type: string) => {
        switch (type) {
            case "multiple_choice":
                return "Multiple Choice";
            case "multiple_select":
                return "Multiple Select";
            case "true_false":
                return "True/False";
            case "short_answer":
                return "Short Answer";
            case "essay":
                return "Essay";
            default:
                return "Unknown Type";
        }
    }

    useEffect(() => {
        if (quizeResult?.testId != null) {
            api.get(`/v1/tests/${quizeResult?.testId}`)
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
                .finally()
        }
    }, [quizeResult.testId])

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
                            Quiz Result Details
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

                        {/* Quiz Result Overview */}
                        {questionSet && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2">
                                        <Eye className="w-5 h-5" />
                                        Quiz Result Overview
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Score:</span>
                                            <span className="text-sm font-medium font-green">{score}/{passingScore}</span>
                                        </div>
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Total Questions:</span>
                                            <span className="text-sm font-medium">{totalQuestions}</span>
                                        </div>
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Correct Anwsers:</span>
                                            <span className="text-sm font-medium">{correctAnswers}</span>
                                        </div>
                                        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                            <span className="text-sm font-medium">Duration Take:</span>
                                            <span className="text-sm font-medium">{formatMinutes(durationTaken * 60)}</span>
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
                                        {questionSet.questions.map((question, index) => {
                                            // Find user's answer for this question
                                            const userAnswer = answers?.find(answer => answer.questionId === question.id);
                                            const userSelectedIds = userAnswer?.selectedOptionIds || [];

                                            // Check if answer is correct
                                            const correctOptionIds = question.options?.filter(option => option.isCorrect).map(option => option.id) || [];
                                            const isCorrect = userSelectedIds.length === correctOptionIds.length &&
                                                userSelectedIds.every(id => correctOptionIds.includes(id));

                                            return (
                                                <div key={question.id} className="space-y-3 p-4 border rounded-lg">
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex items-center gap-3">
                                                            <Badge variant="outline" className="text-sm font-medium">
                                                                Q{index + 1}
                                                            </Badge>
                                                            <Badge variant="secondary" className="text-xs">
                                                                {getTypeLabel(question.type)}
                                                            </Badge>
                                                            {/* Show if question was answered correctly */}
                                                            <Badge
                                                                variant={isCorrect ? "default" : "destructive"}
                                                                className={`text-xs ${isCorrect
                                                                    ? "bg-green-100 text-green-700 border-green-300 dark:bg-green-900 dark:text-green-300"
                                                                    : "bg-red-100 text-red-700 border-red-300 dark:bg-red-900 dark:text-red-300"
                                                                    }`}
                                                            >
                                                                {isCorrect ? "Correct" : "Incorrect"}
                                                            </Badge>
                                                        </div>
                                                        <Badge variant="outline" className="text-xs">
                                                            {question.points || 1} {(question.points || 1) === 1 ? "point" : "points"}
                                                        </Badge>
                                                    </div>

                                                    <div className="space-y-3">
                                                        <h4 className="font-medium text-base">{question.text || "Question text not entered"}</h4>
                                                        
                                                        <div className="space-y-2 ml-4">
                                                                {question.options.map((option, optionIndex) => {
                                                                    const isUserSelected = userSelectedIds.includes(option.id);
                                                                    const isCorrectOption = option.isCorrect;

                                                                    return (
                                                                        <div key={option.id} className="flex items-center gap-3">
                                                                            <span className="text-sm text-muted-foreground font-mono">
                                                                                {String.fromCharCode(65 + optionIndex)}.
                                                                            </span>
                                                                            <span
                                                                                className={`flex-1 ${isCorrectOption
                                                                                        ? "font-medium text-green-600 dark:text-green-400"
                                                                                        : isUserSelected && !isCorrectOption
                                                                                            ? "font-medium text-red-600 dark:text-red-400"
                                                                                            : ""
                                                                                    }`}
                                                                            >
                                                                                {option.text || "Option not entered"}
                                                                            </span>

                                                                            <div className="flex gap-2">
                                                                                {/* Show if user selected this option */}
                                                                                {isUserSelected && (
                                                                                    <Badge
                                                                                        variant="outline"
                                                                                        className={`text-xs ${isCorrectOption
                                                                                                ? "bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800"
                                                                                                : "bg-red-50 text-red-700 border-red-200 dark:bg-red-950 dark:text-red-300 dark:border-red-800"
                                                                                            }`}
                                                                                    >
                                                                                        Your Answer
                                                                                    </Badge>
                                                                                )}

                                                                                {/* Show correct answer */}
                                                                                {isCorrectOption && (
                                                                                    <Badge
                                                                                        variant="outline"
                                                                                        className="text-xs bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800"
                                                                                    >
                                                                                        Correct
                                                                                    </Badge>
                                                                                )}
                                                                            </div>
                                                                        </div>
                                                                    );
                                                                })}
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
                                            );
                                        })}
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
                    <Button onClick={onClose}>Close Result Details</Button>
                </div>
            </DialogContent>
        </Dialog>
    );

}