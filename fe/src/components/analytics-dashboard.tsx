"use client"

import * as React from "react"
import {
  BarChart,
  Bar,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  ResponsiveContainer,
  Tooltip,
  Legend,
} from "recharts"
import {
  TrendingUpIcon,
  TrendingDownIcon,
  UsersIcon,
  BookOpenIcon,
  TargetIcon,
  ClockIcon,
  AwardIcon,
  ActivityIcon,
  CalendarIcon,
  BarChart3Icon,
  Search, Loader
} from "lucide-react"

import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/components/ui/card"
import {Badge} from "@/components/ui/badge"
import {Tabs, TabsContent, TabsList, TabsTrigger} from "@/components/ui/tabs"
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from "@/components/ui/select"
import {Progress} from "@/components/ui/progress"
import Input from "@/components/Input";
import api from "@/lib/axios"
import { set } from "zod"

interface OverviewMetrics {
  totalQuestionSets: number
  totalCompletions: number
  averageScore: number
  activeUsers: number
  completionRate: number
  averageTimeSpent: number // in minutes
}

const topPerformingQuestionSets = [
  {id: 1, title: "JavaScript Fundamentals", completions: 127, averageScore: 78.5, category: "Programming"},
  {id: 2, title: "React Hooks Deep Dive", completions: 234, averageScore: 81.7, category: "Database"},
  {id: 3, title: "CSS Grid Layout", completions: 89, averageScore: 82.3, category: "React"},
  {id: 4, title: "Node.js Basics", completions: 156, averageScore: 75.8, category: "Backend"},
  {id: 5, title: "TypeScript Advanced Types", completions: 67, averageScore: 79.4, category: "API"},
]

const recentActivity = [
  {
    action: "Quiz Completed",
    user: "John Doe",
    questionSet: "JavaScript Fundamentals",
    score: 85,
    time: "2 minutes ago",
  },
  {
    action: "Quiz Started",
    user: "Jane Smith",
    questionSet: "React Hooks Deep Dive",
    score: null,
    time: "5 minutes ago",
  },
  {action: "Quiz Completed", user: "Mike Johnson", questionSet: "Node.js Basics", score: 72, time: "8 minutes ago"},
  {action: "Quiz Completed", user: "Sarah Wilson", questionSet: "Database Design", score: 91, time: "12 minutes ago"},
  {
    action: "Quiz Started",
    user: "Tom Brown",
    questionSet: "API Design Best Practices",
    score: null,
    time: "15 minutes ago",
  },
  {
    action: "Quiz Completed",
    user: "Lisa Davis",
    questionSet: "TypeScript Advanced",
    score: 78,
    time: "18 minutes ago",
  },
]

const COLORS = ["#0088FE", "#00C49F", "#FFBB28", "#FF8042", "#8884D8", "#82CA9D"]

export function AnalyticsDashboard() {
  const [timeRange, setTimeRange] = React.useState("7d")
  const [searchQuery, setSearchQuery] = React.useState("");
  const filteredQuestionSets = topPerformingQuestionSets.filter((set) =>
      set.title.toLowerCase().includes(searchQuery.toLowerCase())
  );
  const [overviewMetrics, setOverViewMetrics] = React.useState<OverviewMetrics>({
    totalQuestionSets: 0,
    totalCompletions: 0,
    averageScore: 0,
    activeUsers: 0,
    completionRate: 0,
    averageTimeSpent: 0,
  });
  const [completionTrends, setCompletionTrends] = React.useState<any[]>([]);
  const [categoryPerformance, setCategoryPerformance] = React.useState<any[]>([]);
  const [difficultyDistribution, setDifficultyDistribution] = React.useState<any[]>([]);
  const [scoreDistribution, setScoreDistribution] = React.useState<any[]>([]);

  const [isLoading, setIsLoading] = React.useState(false);
  React.useEffect(() => {
    if (searchQuery) {
      setIsLoading(true);
      const timer = setTimeout(() => setIsLoading(false), 300);
      return () => clearTimeout(timer);
    } else {
      setIsLoading(false);
    }
  }, [searchQuery]);

  React.useEffect(() => {
    api.get("/v1/analytics/tests/summary")
    .then((response) => {
       const data = response?.data.data || {};
       console.log("Overview Metrics:", data); 
       setOverViewMetrics({
          totalQuestionSets: data.total_question_sets,
          totalCompletions: data.total_completions,
          averageScore: data.average_score,
          activeUsers: data.active_users || 0,
          completionRate: data.completion_rate,
          averageTimeSpent: data.average_time_spent,
        });
    })
  }, [])

  React.useEffect(() => {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(endDate.getDate() - 13); // 14 days total (including today)

    const formatDate = (date: Date) =>
      date.toISOString().split("T")[0]; // Format: YYYY-MM-DD

    api.get("/v1/analytics/tests/completion-trends", {
      params: {
        start_date: formatDate(startDate),
        end_date: formatDate(endDate),
      },
    })
    .then((response) => {
      const data = response?.data?.data;

      setCompletionTrends(data.map((item: any) => ({
        date: item.date,
        completions: item.completions,
        averageScore: item.avg_score,
      }))
      );
    })
    .catch((error) => {
      console.error("Failed to fetch completion trends:", error);
    });
  }, []);

  React.useEffect(() => {
    api.get("/v1/analytics/tests/category-performance")
      .then((res) => {
        const categories = res?.data?.data?.original?.data || [];
        console.log("Category Performance:", categories);
        setCategoryPerformance(categories);
      })
      .catch((err) => {
        console.error("Failed to load category performance", err);
      });
  }, []);

   React.useEffect(() => {
    api.get("/v1/analytics/tests/difficulty-distribution")
      .then((res) => {
        const difficultyDistribution = res?.data?.data?.original?.data || [];
        setDifficultyDistribution(difficultyDistribution);
      })
      .catch((err) => {
        console.error("Failed to load difficulty distribution", err);
      });
  }, []);

  React.useEffect(() => {
    api.get("/v1/analytics/tests/score-distribution")
      .then((res) => {
        const scoreDistribution = res?.data?.data?.original?.data || [];
        setScoreDistribution(scoreDistribution);
      })
      .catch((err) => {
        console.error("Failed to load score distribution", err);
      });
  }, []);

  return (
      <div className="flex flex-col gap-6 py-6">
        {/* Header */}
        <div className="px-4 lg:px-6">
          <div className="flex flex-col gap-2">
            <h1 className="text-3xl font-bold tracking-tight">Analytics Dashboard</h1>
            <p className="text-muted-foreground">
              Comprehensive insights into your question sets performance and user engagement.
            </p>
          </div>
        </div>

        {/* Time Range Selector */}
        {/* <div className="px-4 lg:px-6">
          <div className="flex items-center gap-4">
            <span className="text-sm font-medium">Time Range:</span>
            <Select value={timeRange} onValueChange={setTimeRange}>
              <SelectTrigger className="w-40">
                <SelectValue/>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7d">Last 7 days</SelectItem>
                <SelectItem value="30d">Last 30 days</SelectItem>
                <SelectItem value="90d">Last 3 months</SelectItem>
                <SelectItem value="1y">Last year</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div> */}

        {/* Overview Metrics */}
        <div className="px-4 lg:px-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Question Sets</CardTitle>
                <BookOpenIcon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.totalQuestionSets}</div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Total Completions</CardTitle>
                <TargetIcon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.totalCompletions}</div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Average Score</CardTitle>
                <AwardIcon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.averageScore}</div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Active Users</CardTitle>
                <UsersIcon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.activeUsers}</div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Completion Rate</CardTitle>
                <BarChart3Icon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.completionRate}%</div>
              </CardContent>
            </Card>

            {/* <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Avg. Time Spent</CardTitle>
                <ClockIcon className="h-4 w-4 text-muted-foreground"/>
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{overviewMetrics.averageTimeSpent}m</div>
                <p className="text-xs text-muted-foreground">
                <span className="text-green-600 flex items-center gap-1">
                  <TrendingUpIcon className="h-3 w-3"/>
                  +5% from last month
                </span>
                </p>
              </CardContent>
            </Card> */}
          </div>
        </div>

        {/* Main Analytics Content */}
        <div className="px-4 lg:px-6">
          <Tabs defaultValue="overview" className="space-y-6">
            {/* <TabsList>
              <TabsTrigger value="overview">Overview</TabsTrigger>
              <TabsTrigger value="performance">Performance</TabsTrigger>
              <TabsTrigger value="users">User Analytics</TabsTrigger>
              <TabsTrigger value="activity">Recent Activity</TabsTrigger>
            </TabsList> */}

            <TabsContent value="overview" className="space-y-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Completion Trends */}
                <Card>
                  <CardHeader>
                    <CardTitle>Completion Trends</CardTitle>
                    <CardDescription>Daily quiz completions and average scores over time</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ResponsiveContainer width="100%" height={300}>
                      <LineChart data={completionTrends}>
                        <CartesianGrid strokeDasharray="3 3"/>
                        <XAxis
                            dataKey="date"
                            tickFormatter={(value) =>
                                new Date(value).toLocaleDateString("en-US", {month: "short", day: "numeric"})
                            }
                        />
                        <YAxis yAxisId="left"/>
                        <YAxis yAxisId="right" orientation="right"/>
                        <Tooltip
                            labelFormatter={(value) => new Date(value).toLocaleDateString()}
                            formatter={(value, name) => {
                              if (name === "Completions") {
                                return [value, "Completions"];
                              }
                              if (name === "Avg Score") {
                                return [`${value}`, "Avg Score"];
                              }
                              return [value, name];
                            }}
                        />
                        <Legend/>
                        <Bar yAxisId="left" dataKey="completions" fill="#8884d8" name="Completions"/>
                        <Line yAxisId="right" type="monotone" dataKey="averageScore" stroke="#82ca9d" name="Avg Score"/>
                      </LineChart>
                    </ResponsiveContainer>
                  </CardContent>
                </Card>

                {/* Category Performance */}
                <Card>
                  <CardHeader>
                    <CardTitle>Performance by Category</CardTitle>
                    <CardDescription>Quiz completions and scores across different categories</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ResponsiveContainer width="100%" height={300}>
                      <BarChart data={categoryPerformance}>
                        <CartesianGrid strokeDasharray="3 3"/>
                        <XAxis dataKey="category"/>
                        <YAxis/>
                        <Tooltip/>
                        <Legend/>
                        <Bar dataKey="completions" fill="#8884d8" name="Completions"/>
                        <Bar dataKey="averageScore" fill="#82ca9d" name="Avg Score"/>
                      </BarChart>
                    </ResponsiveContainer>
                  </CardContent>
                </Card>
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Difficulty Distribution */}
                <Card>
                  <CardHeader>
                    <CardTitle>Question Sets by Difficulty</CardTitle>
                    <CardDescription>Distribution of question sets across difficulty levels</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ResponsiveContainer width="100%" height={300}>
                      <PieChart>
                        <Pie
                            data={difficultyDistribution}
                            cx="50%"
                            cy="50%"
                            labelLine={false}
                            label={({difficulty, percentage}) => `${difficulty} (${percentage}%)`}
                            outerRadius={80}
                            fill="#8884d8"
                            dataKey="count"
                        >
                          {difficultyDistribution.map((entry, index) => (
                              <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]}/>
                          ))}
                        </Pie>
                        <Tooltip/>
                      </PieChart>
                    </ResponsiveContainer>
                  </CardContent>
                </Card>

                {/* Score Distribution */}
                <Card>
                  <CardHeader>
                    <CardTitle>Score Distribution</CardTitle>
                    <CardDescription>How users are performing across all quizzes</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    {scoreDistribution.map((item, index) => (
                        <div key={index} className="space-y-2">
                          <div className="flex items-center justify-between text-sm">
                            <span>{item.range}</span>
                            <span className="font-medium">
                          {item.count} users ({item.percentage}%)
                        </span>
                          </div>
                          <Progress value={item.percentage} className="h-2"/>
                        </div>
                    ))}
                  </CardContent>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="performance" className="space-y-6">
              <div className="p-4">
                <div className="relative max-w-md">
                  <Input
                      type="text"
                      placeholder="Search question sets by title..."
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      className="pl-10"
                  />
                  <div className="absolute left-3 top-1/2 -translate-y-1/2">
                    {isLoading ? (
                        <Loader className="h-5 w-5 text-muted-foreground animate-spin"/>
                    ) : (
                        <Search className="h-5 w-5 text-muted-foreground"/>
                    )}
                  </div>
                </div>
              </div>

              <Card>
                <CardHeader>
                  <CardTitle>Question Sets Performance</CardTitle>
                </CardHeader>

                <CardContent>
                  <div className="space-y-4">
                    {filteredQuestionSets.length > 0 ? (
                        filteredQuestionSets.map((questionSet, index) => (
                            <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                              <div className="space-y-1">
                                <div className="font-medium">{questionSet.title}</div>
                                <div className="flex items-center gap-2">
                                  <Badge variant="outline">{questionSet.category}</Badge>
                                  <span
                                      className="text-sm text-muted-foreground">{questionSet.completions} completions</span>
                                </div>
                              </div>
                              <div className="text-right">
                                <div className="text-2xl font-bold">{questionSet.averageScore}%</div>
                                <div className="text-sm text-muted-foreground">avg score</div>
                              </div>
                            </div>
                        ))
                    ) : (
                        <div className="text-center text-muted-foreground">No question sets found</div>
                    )}
                  </div>
                </CardContent>
              </Card>

              {/* Category Performance Details */}
              <Card>
                <CardHeader>
                  <CardTitle>Category Performance Details</CardTitle>
                  <CardDescription>Detailed breakdown of performance by category</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {categoryPerformance.map((category, index) => (
                        <div key={index} className="space-y-2">
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                              <span className="font-medium">{category.category}</span>
                              <Badge variant="secondary">{category.questionSets} question sets</Badge>
                            </div>
                            <div className="text-right">
                              <div className="font-medium">{category.averageScore}% avg score</div>
                              <div className="text-sm text-muted-foreground">{category.completions} completions</div>
                            </div>
                          </div>
                          <Progress value={category.averageScore} className="h-2"/>
                        </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="users" className="space-y-6">
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* User Engagement Metrics */}
                <Card>
                  <CardHeader>
                    <CardTitle>User Engagement</CardTitle>
                    <CardDescription>Key metrics about user behavior and engagement</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="flex items-center justify-between">
                      <span>Daily Active Users</span>
                      <span className="font-bold">127</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Weekly Active Users</span>
                      <span className="font-bold">342</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Monthly Active Users</span>
                      <span className="font-bold">1,247</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Average Session Duration</span>
                      <span className="font-bold">12.4 minutes</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Returning Users</span>
                      <span className="font-bold">68.5%</span>
                    </div>
                  </CardContent>
                </Card>

                {/* User Performance */}
                <Card>
                  <CardHeader>
                    <CardTitle>User Performance Insights</CardTitle>
                    <CardDescription>How users are performing overall</CardDescription>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="flex items-center justify-between">
                      <span>Users with 90%+ scores</span>
                      <span className="font-bold text-green-600">234 (12.7%)</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Users with 80%+ scores</span>
                      <span className="font-bold text-blue-600">690 (37.4%)</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Users needing improvement</span>
                      <span className="font-bold text-orange-600">245 (13.3%)</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Quiz completion rate</span>
                      <span className="font-bold">68.5%</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span>Average attempts per quiz</span>
                      <span className="font-bold">1.4</span>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </TabsContent>

            <TabsContent value="activity" className="space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <ActivityIcon className="h-5 w-5"/>
                    Recent Activity
                  </CardTitle>
                  <CardDescription>Latest quiz activities and user interactions</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {recentActivity.map((activity, index) => (
                        <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                          <div className="space-y-1">
                            <div className="flex items-center gap-2">
                              <Badge variant={activity.action === "Quiz Completed" ? "default" : "secondary"}>
                                {activity.action}
                              </Badge>
                              <span className="font-medium">{activity.user}</span>
                            </div>
                            <div className="text-sm text-muted-foreground">{activity.questionSet}</div>
                          </div>
                          <div className="text-right">
                            {activity.score && <div className="font-bold text-lg">{activity.score}%</div>}
                            <div className="text-sm text-muted-foreground flex items-center gap-1">
                              <CalendarIcon className="h-3 w-3"/>
                              {activity.time}
                            </div>
                          </div>
                        </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </div>
      </div>
  )
}
