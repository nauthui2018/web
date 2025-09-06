import * as React from "react"
import {
  ArrowUpCircleIcon,
  BarChartIcon,
  CameraIcon,
  ClipboardListIcon,
  DatabaseIcon,
  FileCodeIcon,
  FileIcon,
  FileTextIcon,
  FolderIcon,
  HelpCircleIcon,
  LayoutDashboardIcon,
  ListIcon,
  PlusIcon,
  SearchIcon,
  SettingsIcon,
  UsersIcon,
} from "lucide-react"
import { useNavigate } from "react-router-dom"

import { NavDocuments } from "@/components/nav-documents"
import { NavMain } from "@/components/nav-main"
import { NavSecondary } from "@/components/nav-secondary"
import { NavUser } from "@/components/nav-user"
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"

const data = {
  navMain: [
    {
      title: "Dashboard",
      url: "/dashboard",
      icon: LayoutDashboardIcon,
    },
    {
      title: "Question Sets",
      url: "/question-sets",
      icon: ListIcon,
    },
    {
      title: "Create Question Set",
      url: "/create-question-set",
      icon: PlusIcon,
    },
    {
      title: "Analytics",
      url: "/analytics",
      icon: BarChartIcon,
    }
  ]
}

export function AppSidebar({
  onNavigate,
  ...props
}: React.ComponentProps<typeof Sidebar> & {
  onNavigate?: (url: string) => void
}) {
  const navigate = useNavigate()

  const handleLogoClick = () => {
    navigate('/')
  }

  return (
    <Sidebar collapsible="offcanvas" {...props}>
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              asChild
              className="data-[slot=sidebar-menu-button]:!p-1.5"
            >
              <div 
                className="flex items-center space-x-2 cursor-pointer"
                onClick={handleLogoClick}
              >
                <img
                  src="/logo.png"
                  alt="Company Logo"
                  className="w-8 h-8 object-contain cursor-pointer"
                />
                <span className="text-lg font-semibold">Companion</span>
              </div>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={data.navMain} onNavigate={onNavigate}/>
      </SidebarContent>
    </Sidebar>
  )
}
