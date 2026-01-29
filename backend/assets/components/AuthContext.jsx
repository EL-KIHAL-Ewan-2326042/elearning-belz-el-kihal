import React, { createContext, useContext, useState } from 'react';

const AuthContext = createContext(null);

export function AuthProvider({ children, initialUser }) {
    const [user, setUser] = useState(initialUser);

    const isAuthenticated = !!user;

    // Determine user type from roles
    const isTeacher = user?.roles?.includes('ROLE_TEACHER') || user?.roles?.includes('ROLE_ADMIN');
    const userType = isTeacher ? 'teacher' : 'student';

    const value = {
        user,
        isAuthenticated,
        userType,
        isTeacher,
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
