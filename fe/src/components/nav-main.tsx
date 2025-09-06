"use client"

import { PlayIcon, type LucideIcon } from "lucide-react"
import { useNavigate } from "react-router-dom"

import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"



export function NavMain({
  items,
  onNavigate,
}: {
  items: {
    title: string
    url: string
    icon?: LucideIcon
  }[]
  onNavigate?: (url: string) => void
}) {
  const navigate = useNavigate()

  const handleNavClick = (url: string) => {
    if (onNavigate) {
      onNavigate(url)
    }
  }

  const handleTakeQuiz = () => {
    navigate("/quiz")
  }

  return (
    <SidebarGroup>
      <SidebarGroupContent className="flex flex-col gap-2">
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              tooltip="Take Quiz"
              className="min-w-8 bg-primary text-primary-foreground duration-200 ease-linear hover:bg-secondary hover:text-secondary-foreground active:bg-primary/90 active:text-primary-foreground"
              onClick={handleTakeQuiz}
            >
              <PlayIcon />
              <span>Take Quiz</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
        <SidebarMenu>
          {items.map((item) => (
            <SidebarMenuItem key={item.title}>
              <SidebarMenuButton tooltip={item.title} onClick={() => handleNavClick(item.url)}>
                {item.icon && <item.icon />}
                <span>{item.title}</span>
              </SidebarMenuButton>
            </SidebarMenuItem>
          ))}
        </SidebarMenu>
      </SidebarGroupContent>
    </SidebarGroup>
  )
}
