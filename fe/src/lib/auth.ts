// Authentication utility functions

export interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: string;
  is_teacher: boolean;
  is_active: boolean;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  role_display: string;
}

// Function to clear all authentication data
export const clearAuthData = () => {
  localStorage.removeItem("name");
  localStorage.removeItem("email");
  localStorage.removeItem("access_token");
  localStorage.removeItem("refresh_token");
  localStorage.removeItem("role_display");
  localStorage.removeItem("user_id");
  localStorage.removeItem("is_teacher");
  localStorage.removeItem("is_active");
};

// Function to save user data to localStorage
export const saveUserData = (userData: User, accessToken: string, refreshToken: string) => {
  localStorage.setItem("name", userData.name);
  localStorage.setItem("email", userData.email);
  localStorage.setItem("access_token", accessToken);
  localStorage.setItem("refresh_token", refreshToken);
  localStorage.setItem("role_display", userData.role_display);
  localStorage.setItem("user_id", userData.id.toString());
  localStorage.setItem("is_teacher", userData.is_teacher.toString());
  localStorage.setItem("is_active", userData.is_active.toString());
};

// Function to get user data from localStorage
export const getUserData = (): User | null => {
  const name = localStorage.getItem("name");
  const email = localStorage.getItem("email");
  const roleDisplay = localStorage.getItem("role_display");
  const userId = localStorage.getItem("user_id");
  const isTeacher = localStorage.getItem("is_teacher");
  const isActive = localStorage.getItem("is_active");

  if (!name || !email || !roleDisplay || !userId) {
    return null;
  }

  return {
    id: parseInt(userId),
    name,
    email,
    phone: "", // Not stored in localStorage
    role: roleDisplay, // Using role_display as role
    is_teacher: isTeacher === "true",
    is_active: isActive === "true",
    email_verified_at: null, // Not stored in localStorage
    created_at: "", // Not stored in localStorage
    updated_at: "", // Not stored in localStorage
    role_display: roleDisplay,
  };
};

// Function to check if user is authenticated
export const isAuthenticated = (): boolean => {
  const accessToken = localStorage.getItem('access_token');
  const refreshToken = localStorage.getItem('refresh_token');
  return !!(accessToken && refreshToken);
};

// Function to get access token
export const getAccessToken = (): string | null => {
  return localStorage.getItem('access_token');
};

// Function to get refresh token
export const getRefreshToken = (): string | null => {
  return localStorage.getItem('refresh_token');
};
