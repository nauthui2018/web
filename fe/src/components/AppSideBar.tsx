import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from "@/components/ui/sidebar"
import { SearchForm } from "./search-form"
import { LayoutDashboard,
        BookOpen,
        Users, 
        Settings
 } from "lucide-react"
import { Link } from "react-router-dom"

export function AppSidebar() {
    const navMenu = [
        {
            title: "Dashboard",
            url: "/",
            icon: LayoutDashboard
        },
        {
            title: "Question Sets",
            url: "#",
            icon: BookOpen
        },
        {
            title: "Users",
            url: "#",
            icon: Users
        },
        {
            title: "Settings",
            url: "#",
            icon: Settings
        }
    ]
  return (
    <Sidebar>
      <SidebarHeader>
        <SidebarMenuButton size="lg" className="py-0">
            <Link to="/" className="flex flex-row items-end w-full h-full gap-2 p-2">
                <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                  <img src="src/assets/temp-logo.jpeg" alt="companion-logo" />
                </div>
                <div className="">
                  <span className="font-medium">Companion</span>
                </div>
            </Link>
        </SidebarMenuButton>
        <SearchForm />
      </SidebarHeader>
      <SidebarContent>
        <SidebarMenu>
            {navMenu.map((item) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild>
                  <a href={item.url} className="font-medium">
                    {item.title}
                  </a>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}
        </SidebarMenu>
      </SidebarContent>
      <SidebarFooter />
    </Sidebar>
  )
}