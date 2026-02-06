import { createContext, useContext, useState, useEffect } from 'react';
import { login as apiLogin, getCurrentUser } from '../api/auth';

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

    /**
     * Connexion avec email/password
     * Retourne les données utilisateur complètes
     */
    const login = async (email, password) => {
        const data = await apiLogin(email, password);
        localStorage.setItem('token', data.token);

        // Decode JWT to get user info
        const payload = JSON.parse(atob(data.token.split('.')[1]));
        const userData = {
            id: payload.id || payload.sub,
            email: payload.username || payload.email,
            roles: payload.roles || [],
        };

        // Récupérer les infos complètes de l'utilisateur
        try {
            const fullUserData = await getCurrentUser();
            const completeUserData = { ...userData, ...fullUserData };
            localStorage.setItem('user', JSON.stringify(completeUserData));
            setUser(completeUserData);
            return completeUserData;
        } catch (e) {
            // Fallback si /api/me échoue
            localStorage.setItem('user', JSON.stringify(userData));
            setUser(userData);
            return userData;
        }
    };

    /**
     * Connexion directe avec token (après inscription)
     */
    const loginWithToken = (token, userData) => {
        localStorage.setItem('token', token);
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

    /**
     * Détermine l'URL de redirection après connexion/inscription
     */
    const getRedirectUrl = (userData = user) => {
        if (!userData) return '/login';
        
        const roles = userData.roles || [];
        if (roles.includes('ROLE_TEACHER')) {
            return '/courses'; // Panel professeur
        }
        return '/courses'; // Panel étudiant (même route mais affichage différent)
    };

    const value = {
        user,
        login,
        loginWithToken,
        logout,
        isAuthenticated,
        isStudent,
        isTeacher,
        getRedirectUrl,
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
