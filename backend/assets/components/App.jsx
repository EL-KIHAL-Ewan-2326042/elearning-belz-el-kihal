import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './AuthContext';
import Navbar from './Navbar';
import HomePage from './HomePage';
import LoginPage from './LoginPage';

function App({ initialUser, csrfToken, loginError, lastUsername }) {
    // Determine current page based on URL
    const isLoginPage = window.location.pathname === '/login';

    return (
        <AuthProvider initialUser={initialUser}>
            <BrowserRouter>
                <div className="min-h-screen bg-light">
                    {!isLoginPage && <Navbar />}
                    <Routes>
                        <Route path="/" element={<HomePage />} />
                        <Route path="/home" element={<Navigate to="/" replace />} />
                        <Route
                            path="/login"
                            element={
                                <LoginPage
                                    csrfToken={csrfToken}
                                    loginError={loginError}
                                    lastUsername={lastUsername}
                                />
                            }
                        />
                    </Routes>
                </div>
            </BrowserRouter>
        </AuthProvider>
    );
}

export default App;
