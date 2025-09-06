"use client"

import React, { useState, useEffect } from "react"
import { X, Users, Clock, Target, TrendingUp, CheckCircle, XCircle, BarChart3 } from "lucide-react"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from "@/components/ui/button"
import { Loader2 } from "lucide-react"
import api from "@/lib/axios"
import { toast } from "sonner"

interface TestInfo {
  id: number
  title: string
  description: string
  passing_score: number
  total_questions: number
}

interface SummaryStatistics {
  total_attempts: number
  completed_attempts: number
  in_progress_attempts: number
  unique_users: number
  average_score: number
  highest_score: number
  lowest_score: number
  passing_attempts: number
  failing_attempts: number
  pass_rate: number
}

interface UserAttempt {
  user_id: number
  user_name: string
  user_email: string
  score: number
  total_questions: number
  correct_answers: number
  started_at: string
  completed_at: string
  time_taken_minutes: number
  passed: boolean
  percentage: number
}

interface AnalyticsData {
  test_info: TestInfo
  summary_statistics: SummaryStatistics
  user_attempts: {
    data: UserAttempt[]
    pagination: {
      current_page: number
      last_page: number
      per_page: number
      total: number
      from: number
      to: number
    }
  }
}

interface QuizAnalyticsModalProps {
  questionSetId: number | null
  isOpen: boolean
  onClose: () => void
}

export function QuizAnalyticsModal({ questionSetId, isOpen, onClose }: QuizAnalyticsModalProps) {
  const [analyticsData, setAnalyticsData] = useState<AnalyticsData | null>(null)
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (isOpen && questionSetId) {
      fetchAnalytics()
    }
  }, [isOpen, questionSetId])

  const fetchAnalytics = async () => {
    if (!questionSetId) return

    setLoading(true)
    try {
      const response = await api.get(`/v1/analytics/tests/${questionSetId}/detailed`)
      if (response.data.success) {
        setAnalyticsData(response.data.data)
      } else {
        toast.error('Failed to fetch analytics data')
      }
    } catch (error) {
      console.error('Error fetching analytics:', error)
      toast.error('Error fetching analytics data')
    } finally {
      setLoading(false)
    }
  }

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  const formatTime = (minutes: number) => {
    if (minutes < 1) {
      return `${Math.round(minutes * 60)}s`
    }
    return `${Math.round(minutes)}m ${Math.round((minutes % 1) * 60)}s`
  }

  if (!isOpen) return null

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-6xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div className="flex items-center justify-between">
            <DialogTitle className="flex items-center gap-2">
              <BarChart3 className="w-5 h-5" />
              Quiz Analytics
            </DialogTitle>
            <Button variant="ghost" size="sm" onClick={onClose}>
              <X className="w-4 h-4" />
            </Button>
          </div>
        </DialogHeader>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <Loader2 className="w-8 h-8 animate-spin mr-2" />
            <span>Loading analytics...</span>
          </div>
        ) : analyticsData ? (
          <div className="space-y-6">
            {/* Test Info */}
            <Card>
              <CardHeader>
                <CardTitle>{analyticsData.test_info.title}</CardTitle>
                <CardDescription>{analyticsData.test_info.description}</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div className="text-center">
                    <div className="text-2xl font-bold">{analyticsData.test_info.total_questions}</div>
                    <div className="text-sm text-muted-foreground">Total Questions</div>
                  </div>
                  <div className="text-center">
                    <div className="text-2xl font-bold">{analyticsData.test_info.passing_score}%</div>
                    <div className="text-sm text-muted-foreground">Passing Score</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Summary Statistics */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Total Attempts</CardTitle>
                  <Users className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{analyticsData.summary_statistics.total_attempts}</div>
                  <p className="text-xs text-muted-foreground">
                    {analyticsData.summary_statistics.completed_attempts} completed
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Unique Users</CardTitle>
                  <Users className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{analyticsData.summary_statistics.unique_users}</div>
                  <p className="text-xs text-muted-foreground">Different participants</p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Average Score</CardTitle>
                  <Target className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{analyticsData.summary_statistics.average_score.toFixed(1)}%</div>
                  <p className="text-xs text-muted-foreground">
                    {analyticsData.summary_statistics.lowest_score}% - {analyticsData.summary_statistics.highest_score}%
                  </p>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Pass Rate</CardTitle>
                  <TrendingUp className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{analyticsData.summary_statistics.pass_rate.toFixed(1)}%</div>
                  <p className="text-xs text-muted-foreground">
                    {analyticsData.summary_statistics.passing_attempts} passed, {analyticsData.summary_statistics.failing_attempts} failed
                  </p>
                </CardContent>
              </Card>
            </div>

            {/* User Attempts Table */}
            <Card>
              <CardHeader>
                <CardTitle>User Attempts</CardTitle>
                <CardDescription>
                  Detailed breakdown of all attempts for this quiz
                </CardDescription>
              </CardHeader>
              <CardContent>
                {analyticsData.user_attempts.data.length > 0 ? (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>User</TableHead>
                        <TableHead>Score</TableHead>
                        <TableHead>Correct Answers</TableHead>
                        <TableHead>Time Taken</TableHead>
                        <TableHead>Started</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {analyticsData.user_attempts.data.map((attempt, index) => (
                        <TableRow key={index}>
                          <TableCell>
                            <div>
                              <div className="font-medium">{attempt.user_name}</div>
                              <div className="text-sm text-muted-foreground">{attempt.user_email}</div>
                            </div>
                          </TableCell>
                          <TableCell>
                            <div className="font-medium">{attempt.percentage}%</div>
                            <div className="text-sm text-muted-foreground">
                              {attempt.correct_answers}/{attempt.total_questions}
                            </div>
                          </TableCell>
                          <TableCell>{attempt.correct_answers}</TableCell>
                          <TableCell>{formatTime(attempt.time_taken_minutes)}</TableCell>
                          <TableCell>{formatDate(attempt.started_at)}</TableCell>
                          <TableCell>
                            <Badge variant={attempt.passed ? "default" : "destructive"} className="flex gap-1">
                              {attempt.passed ? <CheckCircle className="w-3 h-3" /> : <XCircle className="w-3 h-3" />}
                              {attempt.passed ? "Passed" : "Failed"}
                            </Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    No attempts recorded yet.
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        ) : (
          <div className="text-center py-12 text-muted-foreground">
            No analytics data available.
          </div>
        )}
      </DialogContent>
    </Dialog>
  )
}
