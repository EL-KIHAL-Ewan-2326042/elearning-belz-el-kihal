import { createContext, useContext, useState, useEffect } from 'react';
import { login as apiLogin } from '../api/auth';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const token = localStorage.getItem('token');
        const savedUser = localStorage.getItem('user');

        if (token && savedUser) {
            try {
                setUser(JSON.parse(savedUser));
            } catch (e) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
            }
        }
        setLoading(false);
    }, []);

    const login = async (email, password) => {
        const data = await apiLogin(email, password);
        localStorage.setItem('token', data.token);

        // Decode JWT to get user info (basic decode)
        const payload = JSON.parse(atob(data.token.split('.')[1]));
        const userData = {
            id: payload.id || payload.sub,
            email: payload.username || payload.email,
            roles: payload.roles || [],
        };

        localStorage.setItem('user', JSON.stringify(userData));
        setUser(userData);
        return userData;
    };

    const logout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setUser(null);
    };

    const isAuthenticated = !!user;
    const isStudent = user?.roles?.includes('ROLE_STUDENT');
    const isTeacher = user?.roles?.includes('ROLE_TEACHER');

    const value = {
        user,
        login,
        logout,
        isAuthenticated,
        isStudent,
        isTeacher,
        loading,
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}

export default AuthContext;
