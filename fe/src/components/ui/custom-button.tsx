import type React from "react"

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  children: React.ReactNode
  variant?: "primary" | "secondary" | "success" | "danger" | "outline" | "ghost"
  size?: "sm" | "md" | "lg"
  className?: string
  disabled?: boolean
}

const Button: React.FC<ButtonProps> = ({
  children,
  variant = "primary",
  size = "md",
  className = "",
  disabled = false,
  ...props
}) => {
  const baseClasses =
    "inline-flex items-center justify-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"

  const variants = {
    primary: "bg-custom-slate-900 text-white hover:bg-custom-slate-800 focus:ring-custom-slate-700",
    secondary: "bg-custom-peru-700 text-white hover:bg-custom-peru-800 focus:ring-custom-peru-600",
    success: "bg-green-600 text-white hover:bg-green-700 focus:ring-green-500",
    danger: "bg-red-600 text-white hover:bg-red-700 focus:ring-red-500",
    outline:
      "border border-custom-slate-900 text-custom-slate-900 hover:bg-custom-slate-50 focus:ring-custom-slate-700",
    ghost: "text-custom-slate-900 hover:bg-custom-slate-100 focus:ring-custom-slate-700",
  } as const

  const sizes = {
    sm: "px-3 py-1.5 text-sm",
    md: "px-4 py-2 text-base",
    lg: "px-6 py-3 text-lg",
  } as const

  const disabledClasses = disabled ? "opacity-50 cursor-not-allowed" : ""

  const buttonClasses = `${baseClasses} ${variants[variant]} ${sizes[size]} ${disabledClasses} ${className}`

  return (
    <button className={buttonClasses} disabled={disabled} {...props}>
      {children}
    </button>
  )
}

export default Button
