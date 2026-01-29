import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';

import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Courses from './pages/Courses';
import CourseDetail from './pages/CourseDetail';
import Quiz from './pages/Quiz';
import MyResults from './pages/MyResults';

function PrivateRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen bg-light">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            </div>
        );
    }

    return isAuthenticated ? children : <Navigate to="/login" />;
}

function AppRoutes() {
    return (
        <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />

            <Route path="/courses" element={
                <PrivateRoute><Courses /></PrivateRoute>
            } />

            <Route path="/course/:id" element={
                <PrivateRoute><CourseDetail /></PrivateRoute>
            } />

            <Route path="/quiz/:id" element={
                <PrivateRoute><Quiz /></PrivateRoute>
            } />

            <Route path="/results" element={
                <PrivateRoute><MyResults /></PrivateRoute>
            } />

            <Route path="*" element={<Navigate to="/" />} />
        </Routes>
    );
}

function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <AppRoutes />
            </BrowserRouter>
        </AuthProvider>
    );
}

export default App;
