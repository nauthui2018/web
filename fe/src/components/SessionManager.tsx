import { useEffect } from 'react';
import { useAuth } from '@/contexts/AuthContext';

export const SessionManager: React.FC = () => {
  const { isAuthenticated, refreshSession } = useAuth();

  useEffect(() => {
    if (!isAuthenticated) return;

    // Set up periodic session refresh (every 10 minutes)
    const refreshInterval = setInterval(async () => {
      try {
        await refreshSession();
      } catch (error) {
        console.error('Session refresh failed:', error);
        // Don't force logout on refresh failure, let the user continue
      }
    }, 10 * 60 * 1000); // 10 minutes

    // Only refresh session when user returns to the page if it's been more than 5 minutes
    let lastRefreshTime = Date.now();
    const handleVisibilityChange = async () => {
      if (!document.hidden && isAuthenticated) {
        const timeSinceLastRefresh = Date.now() - lastRefreshTime;
        // Only refresh if it's been more than 5 minutes since last refresh
        if (timeSinceLastRefresh > 5 * 60 * 1000) {
          try {
            await refreshSession();
            lastRefreshTime = Date.now();
          } catch (error) {
            console.error('Session refresh on visibility change failed:', error);
            // Don't force logout on refresh failure
          }
        }
      }
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      clearInterval(refreshInterval);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, [isAuthenticated, refreshSession]);

  // This component doesn't render anything
  return null;
};
