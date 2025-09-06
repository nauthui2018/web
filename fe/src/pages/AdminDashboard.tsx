import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  Users, 
  BarChart3, 
  FolderOpen, 
  BookOpen, 
  Plus,
  Search, 
  Filter,
  MoreVertical,
  Edit, 
  Trash2,
  Eye,
  Download,
  Calendar,
  TrendingUp,
  UserCheck,
  UserX,
  Activity,
  Loader2,
  X
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { useAuth } from '@/contexts/AuthContext';
import api from '@/lib/axios';
import { toast } from 'sonner';

interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: string;
  role_display: string;
  is_teacher: boolean;
  is_active: boolean;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

interface AddUserData {
  name: string;
  email: string;
  password: string;
  phone: string;
  role: 'admin' | 'user';
  is_active: boolean;
}

interface Quiz {
  id: number;
  title: string;
  description: string;
  duration_minutes: number;
  is_active: boolean;
  is_public: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  passing_score: number | null;
  show_correct_answer: boolean;
  difficulty_level: string;
  category: {
    id: number;
    name: string;
    description: string;
    is_active: boolean;
    created_at: string | null;
    updated_at: string | null;
    created_by: number;
    deleted_at: string | null;
  };
  creator: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    is_teacher: boolean;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role_display: string;
    deleted_at: string | null;
  } | null;
  can_edit: boolean;
  can_delete: boolean;
  deleted_at: string | null;
  can_attempt: boolean;
}

interface Category {
  id: number;
  name: string;
  description: string;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
  creator: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    is_teacher: boolean;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role_display: string;
    deleted_at: string | null;
  };
  created_by: number;
  deleted_at: string | null;
}

interface AddCategoryData {
  name: string;
  description: string;
  is_active: boolean;
}

interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_next: boolean;
  has_previous: boolean;
}

interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: {
    items: T[];
    pagination: Pagination;
  };
  code: number;
}

const AdminDashboard: React.FC = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [activeTab, setActiveTab] = useState('overview');
  const [searchTerm, setSearchTerm] = useState('');
  const [quizSearchTerm, setQuizSearchTerm] = useState('');
  const [loading, setLoading] = useState(false);
  const [showAddUserModal, setShowAddUserModal] = useState(false);
  const [addingUser, setAddingUser] = useState(false);
  const [showUserDetailModal, setShowUserDetailModal] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [userToDelete, setUserToDelete] = useState<User | null>(null);
  const [deletingUser, setDeletingUser] = useState(false);
  const [showAddCategoryModal, setShowAddCategoryModal] = useState(false);
  const [addingCategory, setAddingCategory] = useState(false);
  const [showEditCategoryModal, setShowEditCategoryModal] = useState(false);
  const [editingCategory, setEditingCategory] = useState(false);
  const [showDeleteCategoryModal, setShowDeleteCategoryModal] = useState(false);
  const [categoryToDelete, setCategoryToDelete] = useState<Category | null>(null);
  const [deletingCategory, setDeletingCategory] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);

  // Real data from API
  const [users, setUsers] = useState<User[]>([]);
  const [usersPagination, setUsersPagination] = useState<Pagination | null>(null);
  const [filteredUsers, setFilteredUsers] = useState<User[]>([]);
  const [categories, setCategories] = useState<Category[]>([]);
  const [categoriesPagination, setCategoriesPagination] = useState<Pagination | null>(null);
  const [filteredCategories, setFilteredCategories] = useState<Category[]>([]);

  // Add user form data
  const [addUserData, setAddUserData] = useState<AddUserData>({
    name: '',
    email: '',
    password: '',
    phone: '',
    role: 'user',
    is_active: true
  });

  // Add category form data
  const [addCategoryData, setAddCategoryData] = useState<AddCategoryData>({
    name: '',
    description: '',
    is_active: true
  });

  // Edit category form data
  const [editCategoryData, setEditCategoryData] = useState<AddCategoryData>({
    name: '',
    description: '',
    is_active: true
  });

  // Real data from API
  const [quizzes, setQuizzes] = useState<Quiz[]>([]);
  const [quizzesPagination, setQuizzesPagination] = useState<Pagination | null>(null);
  const [filteredQuizzes, setFilteredQuizzes] = useState<Quiz[]>([]);

  // Fetch users from API
  const fetchUsers = async () => {
    setLoading(true);
    try {
      const response = await api.get<ApiResponse<User>>('/v1/admin/users');
      if (response.data.success) {
        setUsers(response.data.data.items);
        setUsersPagination(response.data.data.pagination);
        setFilteredUsers(response.data.data.items);
      } else {
        toast.error('Failed to fetch users');
      }
    } catch (error) {
      console.error('Error fetching users:', error);
      toast.error('Error fetching users');
    } finally {
      setLoading(false);
    }
  };

  // Add new user
  const handleAddUser = async () => {
    setAddingUser(true);
    try {
      const response = await api.post('/v1/admin/users', addUserData);
      if (response.data.success) {
        toast.success('User added successfully');
        setShowAddUserModal(false);
        resetAddUserForm();
        fetchUsers(); // Refresh the list
      } else {
        toast.error(response.data.message || 'Failed to add user');
      }
    } catch (error: any) {
      console.error('Error adding user:', error);
      const errorMessage = error.response?.data?.message || 'Error adding user';
      toast.error(errorMessage);
    } finally {
      setAddingUser(false);
    }
  };

  // Reset add user form
  const resetAddUserForm = () => {
    setAddUserData({
      name: '',
      email: '',
      password: '',
      phone: '',
      role: 'user',
      is_active: true
    });
  };

  // Filter users based on search term
  useEffect(() => {
    if (searchTerm.trim() === '') {
      setFilteredUsers(users);
    } else {
      const filtered = users.filter(user => 
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        user.role_display.toLowerCase().includes(searchTerm.toLowerCase())
      );
      setFilteredUsers(filtered);
    }
  }, [searchTerm, users]);

  // Load users on component mount
  useEffect(() => {
    fetchUsers();
  }, []);

  // Fetch categories from API
  const fetchCategories = async () => {
    try {
      const response = await api.get<ApiResponse<Category>>('/v1/admin/categories');
      if (response.data.success) {
        setCategories(response.data.data.items);
        setCategoriesPagination(response.data.data.pagination);
        setFilteredCategories(response.data.data.items);
      } else {
        toast.error('Failed to fetch categories');
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
      toast.error('Error fetching categories');
    }
  };

  // Add new category
  const handleAddCategory = async () => {
    setAddingCategory(true);
    try {
      const response = await api.post('/v1/admin/categories', addCategoryData);
      if (response.data.success) {
        toast.success('Category added successfully');
        setShowAddCategoryModal(false);
        resetAddCategoryForm();
        fetchCategories(); // Refresh the list
      } else {
        toast.error(response.data.message || 'Failed to add category');
      }
    } catch (error: any) {
      console.error('Error adding category:', error);
      const errorMessage = error.response?.data?.message || 'Error adding category';
      toast.error(errorMessage);
    } finally {
      setAddingCategory(false);
    }
  };

  // Update category
  const handleUpdateCategory = async () => {
    if (!selectedCategory) return;
    
    setEditingCategory(true);
    try {
      const response = await api.put(`/v1/admin/categories/${selectedCategory.id}`, editCategoryData);
      if (response.data.success) {
        toast.success('Category updated successfully');
        setShowEditCategoryModal(false);
        fetchCategories(); // Refresh the list
      } else {
        toast.error(response.data.message || 'Failed to update category');
      }
    } catch (error: any) {
      console.error('Error updating category:', error);
      const errorMessage = error.response?.data?.message || 'Error updating category';
      toast.error(errorMessage);
    } finally {
      setEditingCategory(false);
    }
  };

  // Reset add category form
  const resetAddCategoryForm = () => {
    setAddCategoryData({
      name: '',
      description: '',
      is_active: true
    });
  };

  // Filter categories based on search term
  useEffect(() => {
    if (searchTerm.trim() === '') {
      setFilteredCategories(categories);
    } else {
      const filtered = categories.filter(category => 
        category.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        category.description.toLowerCase().includes(searchTerm.toLowerCase())
      );
      setFilteredCategories(filtered);
    }
  }, [searchTerm, categories]);

  // Fetch quizzes from API
  const fetchQuizzes = async () => {
    try {
      const response = await api.get<ApiResponse<Quiz>>('/v1/admin/tests');
      if (response.data.success) {
        setQuizzes(response.data.data.items);
        setQuizzesPagination(response.data.data.pagination);
        setFilteredQuizzes(response.data.data.items);
      } else {
        toast.error('Failed to fetch quizzes');
      }
    } catch (error) {
      console.error('Error fetching quizzes:', error);
      toast.error('Error fetching quizzes');
    }
  };

  // Filter quizzes based on search term
  useEffect(() => {
    if (quizSearchTerm.trim() === '') {
      setFilteredQuizzes(quizzes);
    } else {
      const filtered = quizzes.filter(quiz => 
        quiz.title.toLowerCase().includes(quizSearchTerm.toLowerCase()) ||
        quiz.description.toLowerCase().includes(quizSearchTerm.toLowerCase()) ||
        quiz.category.name.toLowerCase().includes(quizSearchTerm.toLowerCase()) ||
        quiz.creator?.name.toLowerCase().includes(quizSearchTerm.toLowerCase())
      );
      setFilteredQuizzes(filtered);
    }
  }, [quizSearchTerm, quizzes]);

  // Load categories on component mount
  useEffect(() => {
    fetchCategories();
    fetchQuizzes();
  }, []);

  // Analytics data
  const analyticsData = {
    totalUsers: users.length,
    activeUsers: users.filter(u => u.is_active).length,
    adminUsers: users.filter(u => u.role === 'admin').length,
    totalQuizzes: quizzes.length,
    activeQuizzes: quizzes.filter(q => q.is_active).length,
    totalCategories: categories.length,
    totalCompletions: quizzes.length // Since we don't have completion count in the new API
  };

  const handleUserAction = async (userId: number, action: string) => {
    console.log(`User ${action}:`, userId);
    
    switch (action) {
      case 'view':
        try {
          const response = await api.get(`/v1/admin/users/${userId}`);
          if (response.data.success) {
            const userData = response.data.data;
            setSelectedUser(userData);
            setShowUserDetailModal(true);
          } else {
            toast.error('Failed to fetch user details');
          }
        } catch (error: any) {
          console.error('Error fetching user details:', error);
          const errorMessage = error.response?.data?.message || 'Error fetching user details';
          toast.error(errorMessage);
        }
        break;
      case 'delete':
        // Show confirmation dialog and delete user
        setUserToDelete(users.find(u => u.id === userId) || null);
        setShowDeleteModal(true);
        break;
    }
  };

  const handleQuizAction = async (quizId: number, action: string) => {
    console.log(`Quiz ${action}:`, quizId);
    
    if (action === 'delete') {
      // Show confirmation dialog and delete quiz
      const quizToDelete = quizzes.find(q => q.id === quizId);
      if (quizToDelete) {
        if (confirm(`Are you sure you want to delete "${quizToDelete.title}"? This action cannot be undone.`)) {
          try {
            const response = await api.delete(`/v1/admin/tests/${quizId}`);
            if (response.data.success) {
              toast.success('Quiz deleted successfully');
              fetchQuizzes(); // Refresh the list
            } else {
              toast.error(response.data.message || 'Failed to delete quiz');
            }
          } catch (error: any) {
            console.error('Error deleting quiz:', error);
            const errorMessage = error.response?.data?.message || 'Failed to delete quiz';
            toast.error(errorMessage);
          }
        }
      }
    }
  };

  const handleCategoryAction = (categoryId: number, action: string) => {
    console.log(`Category ${action}:`, categoryId);
    
    switch (action) {
      case 'edit':
        const categoryToEdit = categories.find(c => c.id === categoryId);
        if (categoryToEdit) {
          setSelectedCategory(categoryToEdit);
          setEditCategoryData({
            name: categoryToEdit.name,
            description: categoryToEdit.description,
            is_active: categoryToEdit.is_active
          });
          setShowEditCategoryModal(true);
        }
        break;
      case 'delete':
        const categoryToDelete = categories.find(c => c.id === categoryId);
        if (categoryToDelete) {
          setCategoryToDelete(categoryToDelete);
          setShowDeleteCategoryModal(true);
        }
        break;
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
              <p className="text-gray-600">Manage your platform and users</p>
            </div>
            <div className="flex items-center space-x-4">
              <Button variant="outline" onClick={() => navigate('/')}>
                Back to Home
              </Button>
            </div>
          </div>
        </div>
      </header>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Analytics Overview */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Users</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{analyticsData.totalUsers}</div>
              <p className="text-xs text-muted-foreground">
                {analyticsData.activeUsers} active users
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Admins</CardTitle>
              <UserCheck className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{analyticsData.adminUsers}</div>
              <p className="text-xs text-muted-foreground">
                Platform administrators
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Quizzes</CardTitle>
              <BookOpen className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{analyticsData.totalQuizzes}</div>
              <p className="text-xs text-muted-foreground">
                {analyticsData.activeQuizzes} active quizzes
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Main Content Tabs */}
        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="grid w-full grid-cols-4">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="users">Users</TabsTrigger>
            <TabsTrigger value="quizzes">Quizzes</TabsTrigger>
            <TabsTrigger value="categories">Categories</TabsTrigger>
          </TabsList>

          {/* Overview Tab */}
          <TabsContent value="overview" className="space-y-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle>Recent Users</CardTitle>
                  <CardDescription>Latest registered users</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {users.slice(0, 5).map((user) => (
                      <div key={user.id} className="flex items-center space-x-4">
                        <Avatar className="h-8 w-8">
                          <AvatarFallback>{user.name.charAt(0)}</AvatarFallback>
                        </Avatar>
                        <div className="flex-1 space-y-1">
                          <p className="text-sm font-medium leading-none">{user.name}</p>
                          <p className="text-sm text-muted-foreground">{user.email}</p>
                        </div>
                        <Badge variant={user.is_active ? "default" : "secondary"}>
                          {user.role_display}
                        </Badge>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Recent Quizzes</CardTitle>
                  <CardDescription>Latest created quizzes</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {quizzes.slice(0, 5).map((quiz) => (
                      <div key={quiz.id} className="flex items-center space-x-4">
                        <div className="flex-1 space-y-1">
                          <p className="text-sm font-medium leading-none">{quiz.title}</p>
                          <p className="text-sm text-muted-foreground">{quiz.category.name}</p>
                        </div>
                        <Badge variant={quiz.is_active ? "default" : "secondary"}>
                          {quiz.duration_minutes} min
                        </Badge>
        </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Users Tab */}
          <TabsContent value="users" className="space-y-6">
            <Card>
              <CardHeader>
                <div className="flex justify-between items-center">
                  <div>
                    <CardTitle>User Management</CardTitle>
                    <CardDescription>Manage all platform users</CardDescription>
                  </div>
                  <Button onClick={() => setShowAddUserModal(true)}>
                    <Plus className="h-4 w-4 mr-2" />
                    Add User
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="flex items-center space-x-2 mb-4">
                  <Search className="h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search users by name, email, or role..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="max-w-sm"
                  />
                  <Button variant="outline" size="sm">
                    <Filter className="h-4 w-4 mr-2" />
                    Filter
                  </Button>
                  {loading && (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  )}
      </div>

                {loading ? (
                  <div className="flex items-center justify-center py-8">
                    <Loader2 className="h-8 w-8 animate-spin mr-2" />
                    <span>Loading users...</span>
                  </div>
                ) : (
                  <div className="space-y-4">
                    {filteredUsers.map((user) => (
                      <div key={user.id} className="flex items-center justify-between p-4 border rounded-lg">
                        <div className="flex items-center space-x-4">
                          <Avatar>
                            <AvatarFallback>{user.name.charAt(0)}</AvatarFallback>
                          </Avatar>
                          <div>
                            <p className="font-medium">{user.name}</p>
                            <p className="text-sm text-muted-foreground">{user.email}</p>
                            {user.phone && (
                              <p className="text-xs text-muted-foreground">{user.phone}</p>
                            )}
                            <p className="text-xs text-muted-foreground">
                              Joined: {formatDate(user.created_at)}
                            </p>
                </div>
              </div>
                <div className="flex items-center space-x-2">
                          <Badge variant={user.is_active ? "default" : "secondary"}>
                            {user.role_display}
                          </Badge>
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <MoreVertical className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem onClick={() => handleUserAction(user.id, 'view')}>
                                <Eye className="h-4 w-4 mr-2" />
                                View
                              </DropdownMenuItem>
                              <DropdownMenuItem onClick={() => handleUserAction(user.id, 'delete')}>
                                <Trash2 className="h-4 w-4 mr-2" />
                                Delete
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </div>
                      </div>
                    ))}
                    
                    {filteredUsers.length === 0 && !loading && (
                      <div className="text-center py-8 text-muted-foreground">
                        {searchTerm ? 'No users found matching your search.' : 'No users found.'}
                      </div>
                    )}
                  </div>
                )}

                {/* Pagination info */}
                {usersPagination && (
                  <div className="mt-4 text-sm text-muted-foreground">
                    Showing {usersPagination.current_page} of {usersPagination.total_pages} pages 
                    ({usersPagination.total} total users)
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Quizzes Tab */}
          <TabsContent value="quizzes" className="space-y-6">
            <Card>
              <CardHeader>
                <div className="flex justify-between items-center">
                  <div>
                    <CardTitle>Quiz Management</CardTitle>
                    <CardDescription>Manage all platform quizzes</CardDescription>
                  </div>
                  <Button>
                    <Plus className="h-4 w-4 mr-2" />
                    Add Quiz
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="flex items-center space-x-2 mb-4">
                  <Search className="h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search quizzes..."
                    value={quizSearchTerm}
                    onChange={(e) => setQuizSearchTerm(e.target.value)}
                    className="max-w-sm"
                  />
                  <Button variant="outline" size="sm">
                    <Filter className="h-4 w-4 mr-2" />
                    Filter
                  </Button>
                  </div>

                <div className="space-y-4">
                  {filteredQuizzes.map((quiz) => (
                    <div key={quiz.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center space-x-4">
                          <div>
                            <p className="font-medium">{quiz.title}</p>
                            <p className="text-sm text-muted-foreground">
                              {quiz.category.name} • {quiz.creator?.name || 'Unknown'} • {quiz.difficulty_level}
                            </p>
                </div>
              </div>
            </div>
                      <div className="flex items-center space-x-2">
                        <Badge variant={quiz.is_active ? "default" : "secondary"}>
                          {quiz.duration_minutes} min
                        </Badge>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => handleQuizAction(quiz.id, 'delete')}>
                              <Trash2 className="h-4 w-4 mr-2" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </div>
                  ))}
                </div>

                {/* Pagination info */}
                {quizzesPagination && (
                  <div className="mt-4 text-sm text-muted-foreground">
                    Showing {quizzesPagination.current_page} of {quizzesPagination.total_pages} pages 
                    ({quizzesPagination.total} total quizzes)
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Categories Tab */}
          <TabsContent value="categories" className="space-y-6">
            <Card>
              <CardHeader>
                <div className="flex justify-between items-center">
                  <div>
                    <CardTitle>Category Management</CardTitle>
                    <CardDescription>Manage quiz categories</CardDescription>
          </div>
                  <Button onClick={() => setShowAddCategoryModal(true)}>
                    <Plus className="h-4 w-4 mr-2" />
                    Add Category
                  </Button>
            </div>
              </CardHeader>
              <CardContent>
                <div className="flex items-center space-x-2 mb-4">
                  <Search className="h-4 w-4 text-muted-foreground" />
                  <Input
                    placeholder="Search categories..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="max-w-sm"
                  />
                  <Button variant="outline" size="sm">
                    <Filter className="h-4 w-4 mr-2" />
                    Filter
                  </Button>
                          </div>

                <div className="space-y-4">
                  {filteredCategories.map((category) => (
                    <div key={category.id} className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex-1">
                        <div className="flex items-center space-x-4">
                          <div>
                            <p className="font-medium">{category.name}</p>
                            <p className="text-sm text-muted-foreground">
                              {category.description} • Created by {category.creator.name}
                            </p>
              </div>
            </div>
          </div>
                      <div className="flex items-center space-x-2">
                        <Badge variant={category.is_active ? "default" : "secondary"}>
                          {category.is_active ? "Active" : "Inactive"}
                        </Badge>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm">
                              <MoreVertical className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => handleCategoryAction(category.id, 'edit')}>
                              <Edit className="h-4 w-4 mr-2" />
                              Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => handleCategoryAction(category.id, 'delete')}>
                              <Trash2 className="h-4 w-4 mr-2" />
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
        </div>
      </div>
                  ))}
                </div>

                {/* Pagination info */}
                {categoriesPagination && (
                  <div className="mt-4 text-sm text-muted-foreground">
                    Showing {categoriesPagination.current_page} of {categoriesPagination.total_pages} pages 
                    ({categoriesPagination.total} total categories)
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>

      {/* Add User Modal */}
      <Dialog open={showAddUserModal} onOpenChange={setShowAddUserModal}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Add New User</DialogTitle>
            <DialogDescription>
              Create a new user account with the specified role and permissions.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="grid gap-2">
              <Label htmlFor="name">Full Name</Label>
              <Input
                id="name"
                placeholder="Enter full name"
                value={addUserData.name}
                onChange={(e) => setAddUserData({ ...addUserData, name: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="email">Email</Label>
              <Input
                id="email"
                type="email"
                placeholder="Enter email address"
                value={addUserData.email}
                onChange={(e) => setAddUserData({ ...addUserData, email: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                placeholder="Enter password"
                value={addUserData.password}
                onChange={(e) => setAddUserData({ ...addUserData, password: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="phone">Phone Number</Label>
              <Input
                id="phone"
                placeholder="Enter phone number"
                value={addUserData.phone}
                onChange={(e) => setAddUserData({ ...addUserData, phone: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="role">Role</Label>
              <Select
                value={addUserData.role}
                onValueChange={(value: 'admin' | 'user') => setAddUserData({ ...addUserData, role: value })}
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="user">User</SelectItem>
                  <SelectItem value="admin">Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="flex items-center space-x-2">
              <Switch
                id="is_active"
                checked={addUserData.is_active}
                onCheckedChange={(checked) => setAddUserData({ ...addUserData, is_active: checked })}
              />
              <Label htmlFor="is_active">Active Account</Label>
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setShowAddUserModal(false);
                resetAddUserForm();
              }}
            >
              Cancel
            </Button>
            <Button
              onClick={handleAddUser}
              disabled={addingUser || !addUserData.name || !addUserData.email || !addUserData.password}
            >
              {addingUser ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Adding...
                </>
              ) : (
                'Add User'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* User Detail Modal */}
      <Dialog open={showUserDetailModal} onOpenChange={setShowUserDetailModal}>
        <DialogContent className="sm:max-w-[500px]">
          <DialogHeader>
            <DialogTitle>User Details</DialogTitle>
            <DialogDescription>
              Detailed information about the selected user.
            </DialogDescription>
          </DialogHeader>
          {selectedUser && (
            <div className="space-y-4">
              <div className="flex items-center space-x-4">
                <Avatar className="h-16 w-16">
                  <AvatarFallback className="text-lg">{selectedUser.name.charAt(0)}</AvatarFallback>
                </Avatar>
                <div>
                  <h3 className="text-xl font-semibold">{selectedUser.name}</h3>
                  <p className="text-muted-foreground">{selectedUser.email}</p>
                </div>
              </div>
              
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Role</Label>
                  <p className="text-sm text-muted-foreground capitalize">{selectedUser.role_display}</p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Status</Label>
                  <Badge variant={selectedUser.is_active ? "default" : "secondary"}>
                    {selectedUser.is_active ? "Active" : "Inactive"}
                  </Badge>
                </div>
                <div>
                  <Label className="text-sm font-medium">Phone</Label>
                  <p className="text-sm text-muted-foreground">
                    {selectedUser.phone || "Not provided"}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Email Verified</Label>
                  <p className="text-sm text-muted-foreground">
                    {selectedUser.email_verified_at ? "Yes" : "No"}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">Created</Label>
                  <p className="text-sm text-muted-foreground">
                    {formatDate(selectedUser.created_at)}
                  </p>
                </div>
              </div>
              
              {selectedUser.email_verified_at && (
                <div>
                  <Label className="text-sm font-medium">Email Verified At</Label>
                  <p className="text-sm text-muted-foreground">
                    {formatDate(selectedUser.email_verified_at)}
                  </p>
                </div>
              )}
            </div>
          )}
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setShowUserDetailModal(false)}
            >
              Close
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete User Modal */}
      <Dialog open={showDeleteModal} onOpenChange={setShowDeleteModal}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this user? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <p className="text-sm text-muted-foreground">
              User: {userToDelete?.name} (ID: {userToDelete?.id})
            </p>
            <p className="text-sm text-muted-foreground">
              Role: {userToDelete?.role_display}
            </p>
            <p className="text-sm text-muted-foreground">
              Status: {userToDelete?.is_active ? "Active" : "Inactive"}
            </p>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowDeleteModal(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              onClick={async () => {
                setDeletingUser(true);
                try {
                  const response = await api.delete(`/v1/admin/users/${userToDelete?.id}`);
                  if (response.data.success) {
                    toast.success('User deleted successfully');
                    setShowDeleteModal(false);
                    fetchUsers(); // Refresh the list
                  } else {
                    toast.error(response.data.message || 'Failed to delete user');
                  }
                } catch (error: any) {
                  console.error('Error deleting user:', error);
                  const errorMessage = error.response?.data?.message || 'Failed to delete user';
                  toast.error(errorMessage);
                } finally {
                  setDeletingUser(false);
                }
              }}
              disabled={deletingUser}
            >
              {deletingUser ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Deleting...
                </>
              ) : (
                'Delete User'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Add Category Modal */}
      <Dialog open={showAddCategoryModal} onOpenChange={setShowAddCategoryModal}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Add New Category</DialogTitle>
            <DialogDescription>
              Create a new quiz category.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="grid gap-2">
              <Label htmlFor="category-name">Category Name</Label>
              <Input
                id="category-name"
                placeholder="Enter category name"
                value={addCategoryData.name}
                onChange={(e) => setAddCategoryData({ ...addCategoryData, name: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="category-description">Description</Label>
              <Input
                id="category-description"
                placeholder="Enter category description"
                value={addCategoryData.description}
                onChange={(e) => setAddCategoryData({ ...addCategoryData, description: e.target.value })}
              />
            </div>
            <div className="flex items-center space-x-2">
              <Switch
                id="category-active"
                checked={addCategoryData.is_active}
                onCheckedChange={(checked) => setAddCategoryData({ ...addCategoryData, is_active: checked })}
              />
              <Label htmlFor="category-active">Active Category</Label>
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                setShowAddCategoryModal(false);
                resetAddCategoryForm();
              }}
            >
              Cancel
            </Button>
            <Button
              onClick={handleAddCategory}
              disabled={addingCategory || !addCategoryData.name || !addCategoryData.description}
            >
              {addingCategory ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Adding...
                </>
              ) : (
                'Add Category'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Edit Category Modal */}
      <Dialog open={showEditCategoryModal} onOpenChange={setShowEditCategoryModal}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Edit Category</DialogTitle>
            <DialogDescription>
              Update the category information.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="grid gap-2">
              <Label htmlFor="edit-category-name">Category Name</Label>
              <Input
                id="edit-category-name"
                placeholder="Enter category name"
                value={editCategoryData.name}
                onChange={(e) => setEditCategoryData({ ...editCategoryData, name: e.target.value })}
              />
            </div>
            <div className="grid gap-2">
              <Label htmlFor="edit-category-description">Description</Label>
              <Input
                id="edit-category-description"
                placeholder="Enter category description"
                value={editCategoryData.description}
                onChange={(e) => setEditCategoryData({ ...editCategoryData, description: e.target.value })}
              />
            </div>
            <div className="flex items-center space-x-2">
              <Switch
                id="edit-category-active"
                checked={editCategoryData.is_active}
                onCheckedChange={(checked) => setEditCategoryData({ ...editCategoryData, is_active: checked })}
              />
              <Label htmlFor="edit-category-active">Active Category</Label>
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setShowEditCategoryModal(false)}
            >
              Cancel
            </Button>
            <Button
              onClick={handleUpdateCategory}
              disabled={editingCategory || !editCategoryData.name || !editCategoryData.description}
            >
              {editingCategory ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Updating...
                </>
              ) : (
                'Update Category'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete Category Modal */}
      <Dialog open={showDeleteCategoryModal} onOpenChange={setShowDeleteCategoryModal}>
        <DialogContent className="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Confirm Deletion</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this category? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <p className="text-sm text-muted-foreground">
              Category: {categoryToDelete?.name} (ID: {categoryToDelete?.id})
            </p>
            <p className="text-sm text-muted-foreground">
              Description: {categoryToDelete?.description}
            </p>
            <p className="text-sm text-muted-foreground">
              Status: {categoryToDelete?.is_active ? "Active" : "Inactive"}
            </p>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setShowDeleteCategoryModal(false)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              onClick={async () => {
                setDeletingCategory(true);
                try {
                  const response = await api.delete(`/v1/admin/categories/${categoryToDelete?.id}`);
                  if (response.data.success) {
                    toast.success('Category deleted successfully');
                    setShowDeleteCategoryModal(false);
                    fetchCategories(); // Refresh the list
                  } else {
                    toast.error(response.data.message || 'Failed to delete category');
                  }
                } catch (error: any) {
                  console.error('Error deleting category:', error);
                  const errorMessage = error.response?.data?.message || 'Failed to delete category';
                  toast.error(errorMessage);
                } finally {
                  setDeletingCategory(false);
                }
              }}
              disabled={deletingCategory}
            >
              {deletingCategory ? (
                <>
                  <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                  Deleting...
                </>
              ) : (
                'Delete Category'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default AdminDashboard; 