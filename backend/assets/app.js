import './styles/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './components/App';

// Get user data from the DOM if available
const appElement = document.getElementById('react-app');

if (appElement) {
    const userData = appElement.dataset.user ? JSON.parse(appElement.dataset.user) : null;
    const csrfToken = appElement.dataset.csrfToken || '';
    const loginError = appElement.dataset.loginError || '';
    const lastUsername = appElement.dataset.lastUsername || '';

    const root = createRoot(appElement);
    root.render(
        <React.StrictMode>
            <App
                initialUser={userData}
                csrfToken={csrfToken}
                loginError={loginError}
                lastUsername={lastUsername}
            />
        </React.StrictMode>
    );
}
