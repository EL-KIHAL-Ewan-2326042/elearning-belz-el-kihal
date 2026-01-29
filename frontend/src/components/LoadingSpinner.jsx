import React from 'react';

export default function LoadingSpinner({ fullScreen = true, message = 'Chargement...' }) {
    if (fullScreen) {
        return (
            <div className="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex flex-col items-center justify-center">
                <div className="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary mb-4"></div>
                <p className="text-gray-600 font-medium animate-pulse">{message}</p>
            </div>
        );
    }

    return (
        <div className="flex flex-col items-center justify-center p-8">
            <div className="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-primary mb-4"></div>
            <p className="text-gray-600 font-medium">{message}</p>
        </div>
    );
}
