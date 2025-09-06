"use client"

import * as React from "react"
import { Area, AreaChart, CartesianGrid, XAxis } from "recharts"

import { useIsMobile } from "@/hooks/use-mobile"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  ChartConfig,
  ChartContainer,
  ChartTooltip,
  ChartTooltipContent,
} from "@/components/ui/chart"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  ToggleGroup,
  ToggleGroupItem,
} from "@/components/ui/toggle-group"
import api from "@/lib/axios"

interface TestAttemptsData {
  date: string
  total_attempts: number
  completed_attempts: number
  in_progress_attempts: number
  average_score: number
}

const chartConfig = {
  total_attempts: {
    label: "Total Attempts",
    color: "hsl(var(--chart-1))",
  },
  completed_attempts: {
    label: "Completed",
    color: "hsl(var(--chart-2))",
  },
  in_progress_attempts: {
    label: "In Progress",
    color: "hsl(var(--chart-3))",
  },
} satisfies ChartConfig

export function ChartAreaInteractive() {
  const isMobile = useIsMobile()
  const [timeRange, setTimeRange] = React.useState("15d")
  const [chartData, setChartData] = React.useState<TestAttemptsData[]>([])
  const [loading, setLoading] = React.useState(true)
  const [error, setError] = React.useState<string | null>(null)

  React.useEffect(() => {
    if (isMobile) {
      setTimeRange("7d")
    }
  }, [isMobile])

  // Fetch test attempts data
  React.useEffect(() => {
    const fetchTestAttempts = async () => {
      try {
        setLoading(true)
        setError(null)
        const response = await api.get("/v1/analytics/tests/attempts-by-day")
        const data = response.data?.data?.daily_data || []
        setChartData(data)
      } catch (err) {
        console.error("Failed to fetch test attempts data:", err)
        setError("Failed to load test attempts data")
      } finally {
        setLoading(false)
      }
    }

    fetchTestAttempts()
  }, [])

  const filteredData = chartData.filter((item) => {
    const date = new Date(item.date)
    const today = new Date()
    let daysToSubtract = 15
    if (timeRange === "15d") {
      daysToSubtract = 15
    } else if (timeRange === "7d") {
      daysToSubtract = 7
    }
    const startDate = new Date(today)
    startDate.setDate(startDate.getDate() - daysToSubtract)
    return date >= startDate
  })

  return (
    <Card className="@container/card">
      <CardHeader className="relative">
        <CardTitle>Total Test Attempts</CardTitle>
        <CardDescription>
          <span className="@[540px]/card:block hidden">
            Test attempts over time
          </span>
          <span className="@[540px]/card:hidden">Test attempts</span>
        </CardDescription>
        <div className="absolute right-4 top-4">
          <ToggleGroup
            type="single"
            value={timeRange}
            onValueChange={setTimeRange}
            variant="outline"
            className="@[767px]/card:flex hidden"
          >
            <ToggleGroupItem value="15d" className="h-8 px-2.5">
              Last 15 days
            </ToggleGroupItem>
            <ToggleGroupItem value="7d" className="h-8 px-2.5">
              Last 7 days
            </ToggleGroupItem>
          </ToggleGroup>
          <Select value={timeRange} onValueChange={setTimeRange}>
            <SelectTrigger
              className="@[767px]/card:hidden flex w-40"
              aria-label="Select a value"
            >
              <SelectValue placeholder="Last 15 days" />
            </SelectTrigger>
            <SelectContent className="rounded-xl">
              <SelectItem value="15d" className="rounded-lg">
                Last 15 days
              </SelectItem>
              <SelectItem value="7d" className="rounded-lg">
                Last 7 days
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardHeader>
      <CardContent className="px-2 pt-4 sm:px-6 sm:pt-6">
        {loading ? (
          <div className="flex items-center justify-center h-[250px]">
            <div className="text-muted-foreground">Loading test attempts data...</div>
          </div>
        ) : error ? (
          <div className="flex items-center justify-center h-[250px]">
            <div className="text-destructive">{error}</div>
          </div>
        ) : (
          <ChartContainer
            config={chartConfig}
            className="aspect-auto h-[250px] w-full"
          >
            <AreaChart data={filteredData}>
              <defs>
                <linearGradient id="fillTotalAttempts" x1="0" y1="0" x2="0" y2="1">
                  <stop
                    offset="5%"
                    stopColor="var(--color-total_attempts)"
                    stopOpacity={1.0}
                  />
                  <stop
                    offset="95%"
                    stopColor="var(--color-total_attempts)"
                    stopOpacity={0.1}
                  />
                </linearGradient>
                <linearGradient id="fillCompletedAttempts" x1="0" y1="0" x2="0" y2="1">
                  <stop
                    offset="5%"
                    stopColor="var(--color-completed_attempts)"
                    stopOpacity={0.8}
                  />
                  <stop
                    offset="95%"
                    stopColor="var(--color-completed_attempts)"
                    stopOpacity={0.1}
                  />
                </linearGradient>
                <linearGradient id="fillInProgressAttempts" x1="0" y1="0" x2="0" y2="1">
                  <stop
                    offset="5%"
                    stopColor="var(--color-in_progress_attempts)"
                    stopOpacity={0.8}
                  />
                  <stop
                    offset="95%"
                    stopColor="var(--color-in_progress_attempts)"
                    stopOpacity={0.1}
                  />
                </linearGradient>
              </defs>
              <CartesianGrid vertical={false} />
              <XAxis
                dataKey="date"
                tickLine={false}
                axisLine={false}
                tickMargin={8}
                minTickGap={32}
                tickFormatter={(value) => {
                  const date = new Date(value)
                  return date.toLocaleDateString("en-US", {
                    month: "short",
                    day: "numeric",
                  })
                }}
              />
              <ChartTooltip
                cursor={false}
                content={
                  <ChartTooltipContent
                    labelFormatter={(value) => {
                      return new Date(value).toLocaleDateString("en-US", {
                        month: "short",
                        day: "numeric",
                      })
                    }}
                    indicator="dot"
                  />
                }
              />
              <Area
                dataKey="total_attempts"
                type="natural"
                fill="url(#fillTotalAttempts)"
                stroke="var(--color-total_attempts)"
                stackId="a"
              />
              <Area
                dataKey="completed_attempts"
                type="natural"
                fill="url(#fillCompletedAttempts)"
                stroke="var(--color-completed_attempts)"
                stackId="a"
              />
              <Area
                dataKey="in_progress_attempts"
                type="natural"
                fill="url(#fillInProgressAttempts)"
                stroke="var(--color-in_progress_attempts)"
                stackId="a"
              />
            </AreaChart>
          </ChartContainer>
        )}
      </CardContent>
    </Card>
  )
}
