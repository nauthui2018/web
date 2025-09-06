import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/axios';
import { User, clearAuthData, saveUserData, getUserData, isAuthenticated as checkAuth } from '@/lib/auth';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  refreshSession: () => Promise<boolean>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();

  // Function to refresh session
  const refreshSession = async (): Promise<boolean> => {
    const refreshToken = localStorage.getItem('refresh_token');
    if (!refreshToken) {
      // Only clear auth data if there's no refresh token at all
      clearAuthData();
      setUser(null);
      setIsAuthenticated(false);
      return false;
    }

    try {
      const response = await api.post('/v1/auth/refresh', {
        refresh_token: refreshToken
      });

      if (response.data && response.data.success) {
        const { access_token, refresh_token: newRefreshToken } = response.data.data;
        const userData = response.data.data.user;
        
        saveUserData(userData, access_token, newRefreshToken);
        setUser(userData);
        setIsAuthenticated(true);
        return true;
      } else {
        // Only clear auth data if the refresh explicitly fails
        clearAuthData();
        setUser(null);
        setIsAuthenticated(false);
        return false;
      }
    } catch (error: any) {
      console.error('Session refresh failed:', error);
      
      // Only logout if the error indicates the refresh token is expired
      // Check if it's a 401 or specific error message indicating token expiration
      if (error.response?.status === 401 || 
          error.response?.data?.message?.includes('expired') ||
          error.response?.data?.message?.includes('invalid')) {
        clearAuthData();
        setUser(null);
        setIsAuthenticated(false);
        return false;
      }
      
      // For other errors (network issues, server errors, etc.), don't logout
      // Just return false to indicate refresh failed but keep current session
      return false;
    }
  };

  // Login function
  const login = async (email: string, password: string): Promise<void> => {
    try {
      const response = await api.post("/v1/auth/login", {
        email,
        password,
      });

      if (response.data && response.data.success) {
        const { access_token, refresh_token } = response.data.data;
        const userData = response.data.data.user;
        
        saveUserData(userData, access_token, refresh_token);
        setUser(userData);
        setIsAuthenticated(true);
      } else {
        throw new Error(response.data.message || "Login failed");
      }
    } catch (error: any) {
      throw new Error(error.response?.data?.message || "Login failed");
    }
  };

  // Logout function
  const logout = async (): Promise<void> => {
    try {
      const refreshToken = localStorage.getItem('refresh_token');
      if (refreshToken) {
        await api.post("/v1/auth/logout", {
          refresh_token: refreshToken
        });
      }
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      clearAuthData();
      setUser(null);
      setIsAuthenticated(false);
      navigate('/');
    }
  };

  // Initialize authentication state
  useEffect(() => {
    const initializeAuth = async () => {
      if (checkAuth()) {
        // Try to get user data from localStorage first
        const userData = getUserData();
        if (userData) {
          setUser(userData);
          setIsAuthenticated(true);
          setIsLoading(false);
          
          // Then try to refresh the session in the background
          try {
            await refreshSession();
          } catch (error) {
            console.error('Background session refresh failed:', error);
            // Don't logout on background refresh failure
          }
        } else {
          // If no user data in localStorage, try to refresh session
          const success = await refreshSession();
          if (!success) {
            clearAuthData();
            setUser(null);
            setIsAuthenticated(false);
          }
        }
      } else {
        clearAuthData();
        setUser(null);
        setIsAuthenticated(false);
      }
      
      setIsLoading(false);
    };

    initializeAuth();
  }, []);

  const value: AuthContextType = {
    user,
    isAuthenticated,
    isLoading,
    login,
    logout,
    refreshSession,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
