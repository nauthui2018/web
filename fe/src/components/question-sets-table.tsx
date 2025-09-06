"use client"

import * as React from "react"
import {
  DndContext,
  KeyboardSensor,
  MouseSensor,
  TouchSensor,
  closestCenter,
  useSensor,
  useSensors,
  type DragEndEvent,
  type UniqueIdentifier,
} from "@dnd-kit/core"
import {restrictToVerticalAxis} from "@dnd-kit/modifiers"
import {
  SortableContext,
  arrayMove,
  useSortable,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable"
import {CSS} from "@dnd-kit/utilities"
import {
  ColumnDef,
  ColumnFiltersState,
  Row,
  SortingState,
  VisibilityState,
  flexRender,
  getCoreRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table"
import {
  CheckCircle2Icon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronsLeftIcon,
  ChevronsRightIcon,
  ColumnsIcon,
  GripVerticalIcon,
  MoreVerticalIcon, EditIcon,
  PlayIcon, PlusIcon,
  GlobeIcon,
  LockIcon, DeleteIcon,
  BarChart3Icon
} from "lucide-react"
import { z } from "zod"
import { toast } from "sonner"

import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { QuestionSetPreviewModal } from "@/components/question-set-preview-modal"
import { DeleteQuestionSetModal } from "./delete-question-set-modal"
import { QuizAnalyticsModal } from "./quiz-analytics-modal"

export const questionSetSchema = z.object({
  id: z.number(),
  title: z.string(),
  questionCount: z.number(),
  status: z.string(),
  createdAt: z.string(),
  lastModified: z.string(),
  category: z.string(),
  durationMinutes: z.number(),
  difficulty: z.string(),
})

type QuestionSet = z.infer<typeof questionSetSchema>

export function QuestionSetsTable({
  data: initialData,
  onNavigate,
  onReload
}: {
  data: QuestionSet[]
  onNavigate?: (url: string) => void
  onReload?: () => void
}) {
  const [data] = React.useState(initialData)

  const [rowSelection, setRowSelection] = React.useState({})
  const [columnVisibility, setColumnVisibility] = React.useState<VisibilityState>({})
  const [columnFilters, setColumnFilters] = React.useState<ColumnFiltersState>([])
  const [sorting, setSorting] = React.useState<SortingState>([])
  const [pagination, setPagination] = React.useState({
    pageIndex: 0,
    pageSize: 10,
  })
  const [previewQuestionSetId, setPreviewQuestionSetId] = React.useState<any>(null)
  const [deleteQuestionSetId, setDeleteQuestionSetId] = React.useState<any>(null)
  const [analyticsQuestionSetId, setAnalyticsQuestionSetId] = React.useState<any>(null)

  const handlePreview = (questionSetId: number) => {
    setPreviewQuestionSetId(questionSetId)
  }

  const handleDelete = (deleteQuestionSetId: number) => {
    setDeleteQuestionSetId(deleteQuestionSetId)
  }

  const handleAnalytics = (questionSetId: number) => {
    setAnalyticsQuestionSetId(questionSetId)
  }

  const handleDuplicate = (questionSetId: number) => {
    toast.info("Duplicate feature is not supported for API-fetched question sets.")
  }

  // Helper function to get public access status
  const getPublicAccessStatus = (questionSet: any) => {
    // Use isPublic directly from the row data
    return questionSet.isPublic || false
  }

  const columns: ColumnDef<QuestionSet>[] = [
    {
      accessorKey: "title",
      header: "Title",
      cell: ({ row }) => (
        <div className="max-w-[300px]">
          <div className="font-medium text-foreground">{row.original.title}</div>
          <div className="text-sm text-muted-foreground">{row.original.questionCount} questions</div>
        </div>
      ),
      enableHiding: false,
    },
    {
      accessorKey: "category",
      header: "Category",
      cell: ({ row }) => (
        <Badge variant="outline" className="px-2 py-1">
          {row.original.category}
        </Badge>
      ),
    },
    {
      id: "publicAccess",
      header: "Public Access",
      cell: ({ row }) => {
        const isPublic = getPublicAccessStatus(row.original)
        return (
          <Badge variant={isPublic ? "default" : "secondary"} className="flex gap-1 px-2 py-1">
            {isPublic ? <GlobeIcon className="w-3 h-3" /> : <LockIcon className="w-3 h-3" />}
            {isPublic ? "Public" : "Private"}
          </Badge>
        )
      },
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => {
        const status = row.original.status
        const variant = status === "Active" ? "default" : status === "Draft" ? "secondary" : "outline"

        return (
          <Badge variant={variant} className="flex gap-1 px-2 py-1">
            {status === "Active" && <CheckCircle2Icon className="w-3 h-3" />}
            {status}
          </Badge>
        )
      },
    },
    {
      accessorKey: "durationMinutes",
      header: () => <div className="text-right">Duration</div>,
      cell: ({ row }) => (
        <div className="text-right font-medium">
          {row.original.durationMinutes ? `${row.original.durationMinutes} min` : "No limit"}
        </div>
      ),
    },
    {
      accessorKey: "difficulty",
      header: () => <div className="text-right">Difficulty</div>,
      cell: ({ row }) => (
        <div className="text-right">
          <Badge 
            variant={
              row.original.difficulty === "Beginner" ? "default" : 
              row.original.difficulty === "Intermediate" ? "secondary" : 
              "destructive"
            }
            className="px-2 py-1"
          >
            {row.original.difficulty}
          </Badge>
        </div>
      ),
    },
    {
      accessorKey: "createdAt",
      header: "Created",
      cell: ({ row }) => (
        <div className="text-sm text-muted-foreground">{new Date(row.original.createdAt).toLocaleDateString()}</div>
      ),
    },
    {
      id: "actions",
      cell: ({ row }) => (
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button
              variant="ghost"
              className="flex size-8 text-muted-foreground data-[state=open]:bg-muted"
              size="icon"
            >
              <MoreVerticalIcon />
              <span className="sr-only">Open menu</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="w-40">
            <DropdownMenuItem onClick={() => handlePreview(row.original.id)}>
              <PlayIcon className="w-4 h-4 mr-2" />
              Preview
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => handleAnalytics(row.original.id)}>
              <BarChart3Icon className="w-4 h-4 mr-2" />
              View Analytics
            </DropdownMenuItem>
            <DropdownMenuItem onClick={() => onNavigate?.(`edit-question-set-${row.original.id}`)}>
              <EditIcon className="w-4 h-4 mr-2" />
              Edit
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={() => handleDelete(row.original.id)}>
              <DeleteIcon className="w-4 h-4 mr-2" />
              Delete
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ]

  const table = useReactTable({
    data,
    columns,
    state: {
      sorting,
      columnVisibility,
      rowSelection,
      columnFilters,
      pagination,
    },
    getRowId: (row) => row.id.toString(),
    enableRowSelection: true,
    onRowSelectionChange: setRowSelection,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onColumnVisibilityChange: setColumnVisibility,
    onPaginationChange: setPagination,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    getFacetedUniqueValues: getFacetedUniqueValues(),
  })

  return (
    <>
      <div className="flex w-full flex-col justify-start gap-6">
        <div className="flex items-center justify-between px-4 lg:px-6">
          <div className="flex items-center gap-4">
            <Input
              placeholder="Search question sets..."
              value={(table.getColumn("title")?.getFilterValue() as string) ?? ""}
              onChange={(event) => table.getColumn("title")?.setFilterValue(event.target.value)}
              className="max-w-sm"
            />
            <Select
              value={(table.getColumn("status")?.getFilterValue() as string) ?? ""}
              onValueChange={(value) => table.getColumn("status")?.setFilterValue(value === "all" ? "" : value)}
            >
              <SelectTrigger className="w-32">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="Active">Active</SelectItem>
                <SelectItem value="Draft">Draft</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div className="flex items-center gap-2">
            <Button
              onClick={() => onNavigate?.("create-question-set")}
              className="bg-primary text-primary-foreground hover:bg-secondary"
            >
              <PlusIcon className="w-4 h-4 mr-2" />
              Create New Question Set
            </Button>
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm">
                  <ColumnsIcon />
                  <span className="hidden lg:inline">Columns</span>
                  <ChevronDownIcon />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-56">
                {table
                  .getAllColumns()
                  .filter((column) => typeof column.accessorFn !== "undefined" && column.getCanHide())
                  .map((column) => {
                    return (
                      <DropdownMenuCheckboxItem
                        key={column.id}
                        className="capitalize"
                        checked={column.getIsVisible()}
                        onCheckedChange={(value) => column.toggleVisibility(!!value)}
                      >
                        {column.id === "publicAccess" ? "Public Access" : column.id}
                      </DropdownMenuCheckboxItem>
                    )
                  })}
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>

        <div className="relative flex flex-col gap-4 overflow-auto px-4 lg:px-6">
          <div className="overflow-hidden rounded-lg border">
            <Table>
              <TableHeader className="sticky top-0 z-10 bg-muted">
                {table.getHeaderGroups().map((headerGroup) => (
                  <TableRow key={headerGroup.id}>
                    {headerGroup.headers.map((header) => {
                      return (
                        <TableHead key={header.id} colSpan={header.colSpan}>
                          {header.isPlaceholder
                            ? null
                            : flexRender(header.column.columnDef.header, header.getContext())}
                        </TableHead>
                      )
                    })}
                  </TableRow>
                ))}
              </TableHeader>
              <TableBody>
                {table.getRowModel().rows?.length ? (
                  table.getRowModel().rows.map((row) => (
                    <TableRow key={row.id} data-state={row.getIsSelected() && "selected"}>
                      {row.getVisibleCells().map((cell) => (
                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                      ))}
                    </TableRow>
                  ))
                ) : (
                  <TableRow>
                    <TableCell colSpan={columns.length} className="h-24 text-center">
                      No question sets found.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>

          <div className="flex items-center justify-between px-4">
            <div className="hidden flex-1 text-sm text-muted-foreground lg:flex">
              {table.getFilteredSelectedRowModel().rows.length} of {table.getFilteredRowModel().rows.length} row(s)
              selected.
            </div>
            <div className="flex w-full items-center gap-8 lg:w-fit">
              <div className="hidden items-center gap-2 lg:flex">
                <Label htmlFor="rows-per-page" className="text-sm font-medium">
                  Rows per page
                </Label>
                <Select
                  value={`${table.getState().pagination.pageSize}`}
                  onValueChange={(value) => {
                    table.setPageSize(Number(value))
                  }}
                >
                  <SelectTrigger className="w-20" id="rows-per-page">
                    <SelectValue placeholder={table.getState().pagination.pageSize} />
                  </SelectTrigger>
                  <SelectContent side="top">
                    {[10, 20, 30, 40, 50].map((pageSize) => (
                      <SelectItem key={pageSize} value={`${pageSize}`}>
                        {pageSize}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="flex w-fit items-center justify-center text-sm font-medium">
                Page {table.getState().pagination.pageIndex + 1} of {table.getPageCount()}
              </div>
              <div className="ml-auto flex items-center gap-2 lg:ml-0">
                <Button
                  variant="outline"
                  className="hidden h-8 w-8 p-0 lg:flex bg-transparent"
                  onClick={() => table.setPageIndex(0)}
                  disabled={!table.getCanPreviousPage()}
                >
                  <span className="sr-only">Go to first page</span>
                  <ChevronsLeftIcon />
                </Button>
                <Button
                  variant="outline"
                  className="size-8 bg-transparent"
                  size="icon"
                  onClick={() => table.previousPage()}
                  disabled={!table.getCanPreviousPage()}
                >
                  <span className="sr-only">Go to previous page</span>
                  <ChevronLeftIcon />
                </Button>
                <Button
                  variant="outline"
                  className="size-8 bg-transparent"
                  size="icon"
                  onClick={() => table.nextPage()}
                  disabled={!table.getCanNextPage()}
                >
                  <span className="sr-only">Go to next page</span>
                  <ChevronRightIcon />
                </Button>
                <Button
                  variant="outline"
                  className="hidden size-8 lg:flex bg-transparent"
                  size="icon"
                  onClick={() => table.setPageIndex(table.getPageCount() - 1)}
                  disabled={!table.getCanNextPage()}
                >
                  <span className="sr-only">Go to last page</span>
                  <ChevronsRightIcon />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Preview Modal */}
      <QuestionSetPreviewModal
        questionSetId={previewQuestionSetId}
        isOpen={!!previewQuestionSetId}
        onClose={() => setPreviewQuestionSetId(null)}
      />

      {/* Delete Question Set Modal */}
      <DeleteQuestionSetModal
        isOpen={!!deleteQuestionSetId}
        onClose={() => setDeleteQuestionSetId(null)}
        onDeleted={onReload || (() => {})}
        questionSetId={deleteQuestionSetId}
        questionSetTitle={data.find((q) => q.id === deleteQuestionSetId)?.title || ""}
      />

      {/* Analytics Modal */}
      <QuizAnalyticsModal
        questionSetId={analyticsQuestionSetId}
        isOpen={!!analyticsQuestionSetId}
        onClose={() => setAnalyticsQuestionSetId(null)}
      />
    </>
  )
}


