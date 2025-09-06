"use client"

import React, {useState, useEffect} from "react"
import {Link, useNavigate} from "react-router-dom"
import {BookOpen, Users, Trophy, Zap, ArrowRight, Star, CheckCircle, Play, PlusCircle, ChevronDown, BarChart3} from "lucide-react"
import {Button} from "@/components/ui/button"
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/components/ui/card"
import {Badge} from "@/components/ui/badge"
import {DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger} from "@/components/ui/dropdown-menu"
import {Avatar, AvatarFallback, AvatarImage} from "@/components/ui/avatar";
import CustomButton from "@/components/ui/custom-button";
import { useAuth } from "@/contexts/AuthContext";

const HomePage: React.FC = () => {
  const navigate = useNavigate()
  const { user, isAuthenticated, logout } = useAuth()

  const handleDashboardClick = () => {
    navigate("/dashboard")
  }

  const handleTestClick = () => {
    navigate("/quiz")
  }

  const handleLoginClick = () => {
    navigate("/login")
  }

  const handleSignUpClick = () => {
    navigate("/signup")
  }

  const handleLogout = async () => {
    await logout();
  };

  const handleAdminClick = () => {
    navigate("/admin");
  };

  const features = [
    {
      icon: <BookOpen className="w-8 h-8 text-blue-600"/>,
      title: "Rich Question Types",
      description: "Create multiple choice, true/false, and short answer questions with detailed explanations.",
    },
    {
      icon: <Users className="w-8 h-8 text-green-600"/>,
      title: "Track Progress",
      description: "Monitor quiz performance with detailed analytics and progress tracking.",
    },
    {
      icon: <Trophy className="w-8 h-8 text-yellow-600"/>,
      title: "Gamified Learning",
      description: "Engage learners with scoring, badges, and competitive elements.",
    },
    {
      icon: <Zap className="w-8 h-8 text-purple-600"/>,
      title: "Instant Results",
      description: "Get immediate feedback with detailed explanations and performance insights.",
    },
  ]

  const stats = [
    {label: "Active Quizzes", value: "500+", icon: <BookOpen className="w-5 h-5"/>},
    {label: "Happy Users", value: "10K+", icon: <Users className="w-5 h-5"/>},
    {label: "Questions Created", value: "25K+", icon: <CheckCircle className="w-5 h-5"/>},
    {label: "Success Rate", value: "95%", icon: <Trophy className="w-5 h-5"/>},
  ]

  const testimonials = [
    {
      name: "Sarah Johnson",
      role: "Educator",
      content:
          "This platform has transformed how I create and manage quizzes for my students. The interface is intuitive and the analytics are incredibly helpful.",
      rating: 5,
    },
    {
      name: "Mike Chen",
      role: "Training Manager",
      content:
          "We use this for employee training assessments. The variety of question types and detailed reporting make it perfect for our needs.",
      rating: 5,
    },
    {
      name: "Emily Davis",
      role: "Student",
      content:
          "Taking quizzes here is actually enjoyable! The immediate feedback and explanations help me learn better.",
      rating: 5,
    },
  ]

  return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
        {/* Header */}

        <header className="w-full px-6 py-4 bg-white/80 backdrop-blur-sm shadow-sm border-b border-white/20">
          <div className="max-w-7xl mx-auto flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div 
                className="w-10 h-10 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity"
                onClick={() => navigate('/')}
              >
                <img src="/logo.png" alt="Company Logo" className="w-24 h-24 object-contain"/>
              </div>
              <span 
                className="text-xl font-bold text-primary cursor-pointer hover:opacity-80 transition-opacity"
                onClick={() => navigate('/')}
              >
              Companion
            </span>
            </div>
            {isAuthenticated && user ? (
                <div className="flex items-center gap-4">
                  {user?.role === 'admin' && (
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={handleAdminClick}
                      className="bg-purple-50 text-purple-600 border-purple-200 hover:bg-purple-100"
                    >
                      <BarChart3 className="w-4 h-4 mr-2"/>
                      Admin
                    </Button>
                  )}
                  <div className="flex items-center gap-3">
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" className="relative h-8 w-8 rounded-full">
                          <Avatar className="h-8 w-8">
                            <AvatarImage src="/avatars/01.png" alt={user.name} />
                            <AvatarFallback>{user.name.charAt(0).toUpperCase()}</AvatarFallback>
                          </Avatar>
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent className="w-56" align="end" forceMount>
                        <div className="flex items-center justify-start gap-2 p-2">
                          <div className="flex flex-col space-y-1 leading-none">
                            <p className="font-medium">{user.name}</p>
                            <p className="w-[200px] truncate text-sm text-muted-foreground">
                              {user.email}
                            </p>
                            <p className="text-xs text-muted-foreground capitalize">
                              {user.role_display}
                            </p>
                          </div>
                        </div>
                        <DropdownMenuItem onClick={handleDashboardClick}>
                          Dashboard
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleTestClick}>
                          Take Quiz
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleLogout}>
                          Log out
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                    <div className="text-left">
                      <p className="text-sm font-medium text-gray-900">{user.name}</p>
                      <p className="text-xs text-gray-500 capitalize">{user.role_display}</p>
                    </div>
                  </div>
                </div>
            ) : (
                <div className="flex items-center gap-3">
                  <Button variant="ghost" onClick={handleLoginClick}>
                    Login
                  </Button>
                  <Button
                      onClick={handleSignUpClick}
                      className="bg-primary hover:from-blue-700 hover:to-indigo-700"
                  >
                    Sign Up
                  </Button>
                </div>
            )}
          </div>
        </header>

        {/* Hero Section */}
        <section className="relative px-6 py-20 overflow-hidden">
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              {/* Hero Content */}
              <div className="space-y-8">
                <div className="space-y-4">
                  <Badge className="bg-blue-100 text-blue-700 hover:bg-blue-100">
                    🚀 New: Advanced Analytics Dashboard
                  </Badge>
                  <h1 className="text-5xl lg:text-6xl font-bold leading-tight">
                    Create Amazing{" "}
                    <span
                        className="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent">
                    Quizzes
                  </span>{" "}
                    in Minutes
                  </h1>
                  <p className="text-xl text-gray-600 leading-relaxed">
                    Build engaging quizzes, track performance, and enhance learning experiences with our powerful,
                    intuitive platform designed for educators and learners.
                  </p>
                </div>

                {/* CTA Buttons */}
                <div className="flex flex-col sm:flex-row gap-4">
                  {isAuthenticated ? (
                    <>
                      <Button
                          size="lg"
                          onClick={handleDashboardClick}
                          className="bg-primary text-lg px-8 py-6 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                      >
                        <PlusCircle className="w-5 h-5 mr-2"/>
                        Go to Dashboard
                        <ArrowRight className="w-5 h-5 ml-2"/>
                      </Button>
                      <Button
                          variant="outline"
                          size="lg"
                          onClick={handleTestClick}
                          className="bg-secondary text-lg px-8 py-6 border-2 hover:bg-blue-50 transition-all duration-200"
                      >
                        <Play className="w-5 h-5 mr-2"/>
                        Take a Quiz
                      </Button>
                    </>
                  ) : (
                    <>
                      <Button
                          size="lg"
                          onClick={handleSignUpClick}
                          className="bg-primary text-lg px-8 py-6 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                      >
                        <PlusCircle className="w-5 h-5 mr-2"/>
                        Create Your Quiz
                        <ArrowRight className="w-5 h-5 ml-2"/>
                      </Button>
                      <Button
                          variant="outline"
                          size="lg"
                          onClick={handleTestClick}
                          className="bg-secondary text-lg px-8 py-6 border-2 hover:bg-blue-50 transition-all duration-200"
                      >
                        <Play className="w-5 h-5 mr-2"/>
                        Explore Quizzes
                      </Button>
                    </>
                  )}
                </div>

                {/* Stats */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 pt-8">
                  {stats.map((stat, index) => (
                      <div key={index} className="text-center">
                        <div className="flex items-center justify-center gap-2 text-2xl font-bold text-gray-900 mb-1">
                          {stat.icon}
                          {stat.value}
                        </div>
                        <div className="text-sm text-gray-600">{stat.label}</div>
                      </div>
                  ))}
                </div>
              </div>

              {/* Hero Visual */}
              <div className="relative">
                <div className="relative z-10">
                  <Card className="shadow-2xl border-0 bg-white/90 backdrop-blur-sm">
                    <CardHeader>
                      <div className="flex items-center gap-2 mb-2">
                        <div className="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div className="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                      </div>
                      <CardTitle className="text-lg">React Fundamentals Quiz</CardTitle>
                      <CardDescription>Test your knowledge of React basics</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <div className="space-y-3">
                        <div className="text-sm font-medium text-gray-900">What is JSX in React?</div>
                        <div className="space-y-2">
                          {[
                            "A JavaScript library",
                            "A syntax extension for JavaScript ✓",
                            "A CSS framework",
                            "A database query language",
                          ].map((option, index) => (
                              <div
                                  key={index}
                                  className={`p-3 rounded-lg border transition-colors ${
                                      index === 1 ? "bg-green-50 border-green-200 text-green-800" : "bg-gray-50 border-gray-200"
                                  }`}
                              >
                                {option}
                              </div>
                          ))}
                        </div>
                      </div>
                      <div className="flex items-center justify-between pt-4 border-t">
                        <div className="text-sm text-gray-600">Question 1 of 15</div>
                        <div className="text-sm text-green-600 font-medium">Correct! 🎉</div>
                      </div>
                    </CardContent>
                  </Card>
                </div>

                {/* Background decorations */}
                <div
                    className="absolute -top-4 -right-4 w-72 h-72 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-full opacity-20 blur-3xl"></div>
                <div
                    className="absolute -bottom-8 -left-8 w-64 h-64 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full opacity-20 blur-3xl"></div>
              </div>
            </div>
          </div>
        </section>

        {/* Features Section */}
        <section className="px-6 py-20 bg-white/50 backdrop-blur-sm">
          <div className="max-w-7xl mx-auto">
            <div className="text-center mb-16">
              <h2 className="text-4xl font-bold text-gray-900 mb-4">Everything You Need to Create Amazing Quizzes</h2>
              <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                Our platform provides all the tools you need to create, manage, and analyze quizzes that engage and
                educate your audience.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {features.map((feature, index) => (
                  <Card
                      key={index}
                      className="border-0 shadow-lg hover:shadow-xl transition-shadow duration-300 bg-white/80 backdrop-blur-sm"
                  >
                    <CardHeader className="text-center">
                      <div className="flex justify-center mb-4">
                        <div className="p-3 bg-gray-50 rounded-full">{feature.icon}</div>
                      </div>
                      <CardTitle className="text-lg">{feature.title}</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <CardDescription className="text-center">{feature.description}</CardDescription>
                    </CardContent>
                  </Card>
              ))}
            </div>
          </div>
        </section>

        {/* Testimonials Section */}
        <section className="px-6 py-20">
          <div className="max-w-7xl mx-auto">
            <div className="text-center mb-16">
              <h2 className="text-4xl font-bold text-gray-900 mb-4">Loved by Educators and Learners</h2>
              <p className="text-xl text-gray-600">See what our users have to say about their experience</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {testimonials.map((testimonial, index) => (
                  <Card key={index} className="border-0 shadow-lg bg-white/80 backdrop-blur-sm">
                    <CardHeader>
                      <div className="flex items-center gap-1 mb-2">
                        {[...Array(testimonial.rating)].map((_, i) => (
                            <Star key={i} className="w-4 h-4 fill-yellow-400 text-yellow-400"/>
                        ))}
                      </div>
                      <CardDescription className="text-base italic">"{testimonial.content}"</CardDescription>
                    </CardHeader>
                    <CardContent>
                      <div>
                        <div className="font-semibold text-gray-900">{testimonial.name}</div>
                        <div className="text-sm text-gray-600">{testimonial.role}</div>
                      </div>
                    </CardContent>
                  </Card>
              ))}
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="px-6 py-20 bg-primary">
          <div className="max-w-4xl mx-auto text-center">
            <h2 className="text-4xl font-bold text-white mb-6">Ready to Transform Your Quizzes?</h2>
            <p className="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
              Join thousands of educators and trainers who are already creating amazing learning experiences with our
              platform.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              {isAuthenticated ? (
                <Button
                    size="lg"
                    onClick={handleDashboardClick}
                    className="bg-white text-blue-600 hover:bg-gray-50 text-lg px-8 py-6 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                >
                  <PlusCircle className="w-5 h-5 mr-2"/>
                  Go to Dashboard
                </Button>
              ) : (
                <>
                  <Button
                      size="lg"
                      onClick={handleSignUpClick}
                      className="bg-white text-blue-600 hover:bg-gray-50 text-lg px-8 py-6 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                  >
                    <PlusCircle className="w-5 h-5 mr-2"/>
                    Start Creating Now
                  </Button>
                  <Button
                      variant="outline"
                      size="lg"
                      onClick={handleTestClick}
                      className="border-white text-white hover:bg-white/10 text-lg px-8 py-6 transition-all duration-200 bg-transparent"
                  >
                    <Play className="w-5 h-5 mr-2"/>
                    Try Sample Quizzes
                  </Button>
                </>
              )}
            </div>
          </div>
        </section>

        {/* Footer */}
        <footer className="px-6 py-12 bg-white/80 backdrop-blur-sm border-t border-white/20">
          <div className="max-w-7xl mx-auto">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
              <div className="md:col-span-2">
                <div className="flex items-center gap-3 mb-4">
                  <div className="w-8 h-8 rounded-lg flex items-center justify-center">
                    <img src="/logo.png" alt="Company Logo" className="w-24 h-24 object-contain"/>
                  </div>
                  <span className="text-lg font-bold bg-primary bg-clip-text text-transparent">
                  Companion
                </span>
                </div>
                <p className="text-gray-600 mb-4 max-w-md">
                  Empowering educators and learners with powerful quiz creation and management tools. Create, share, and
                  analyze quizzes with ease.
                </p>
                <div className="flex items-center gap-4">
                  <Button variant="ghost" size="sm">
                    Privacy Policy
                  </Button>
                  <Button variant="ghost" size="sm">
                    Terms of Service
                  </Button>
                </div>
              </div>

              <div>
                <h3 className="font-semibold text-gray-900 mb-4">Platform</h3>
                <div className="space-y-2">
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto" onClick={handleDashboardClick}>
                    Create Quiz
                  </Button>
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto" onClick={handleTestClick}>
                    Browse Quizzes
                  </Button>
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto">
                    Analytics
                  </Button>
                </div>
              </div>

              <div>
                <h3 className="font-semibold text-gray-900 mb-4">Support</h3>
                <div className="space-y-2">
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto">
                    Help Center
                  </Button>
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto">
                    Contact Us
                  </Button>
                  <Button variant="ghost" size="sm" className="justify-start p-0 h-auto">
                    Community
                  </Button>
                </div>
              </div>
            </div>

            <div className="border-t border-gray-200 mt-8 pt-8 text-center text-gray-500">
              <p>&copy; 2025 Companion. All rights reserved. Built with ❤️ for educators and learners.</p>
            </div>
          </div>
        </footer>
      </div>
  )
}

export default HomePage
