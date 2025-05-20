import React from 'react';
import { createRoot } from 'react-dom/client';
import { 
    Routes,
    Route, 
    Link,
    createBrowserRouter,
    RouterProvider
} from 'react-router-dom';
import Home from './components/Home.js';
import Statistics from './components/Statistics.js';
import UploadCalls from './components/UploadCalls.js';

// Layout component that includes the navigation
const Layout = () => {
    return (
        <div className="container mt-4">
            <nav className="navbar navbar-expand-lg navbar-light bg-light mb-4">
                <div className="container-fluid">
                    <span className="navbar-brand">Calls System</span>
                    <div className="collapse navbar-collapse">
                        <ul className="navbar-nav me-auto mb-2 mb-lg-0">
                            <li className="nav-item">
                                <Link className="nav-link" to="/app">Home</Link>
                            </li>
                            <li className="nav-item">
                                <Link className="nav-link" to="/statistics">Statistics</Link>
                            </li>
                            <li className="nav-item">
                                <Link className="nav-link" to="/upload_calls">Upload calls file</Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <Routes>
                <Route path="/app" element={<Home />} />
                <Route path="/statistics" element={<Statistics />} />
                <Route path="/upload_calls" element={<UploadCalls />} />
                <Route path="*" element={<Home />} />
            </Routes>
        </div>
    );
};

// Create a router with future flags enabled
const router = createBrowserRouter(
    [
        {
            path: '/*',
            element: <Layout />
        }
    ],
    {
        // Enable future flags
        future: {
            v7_startTransition: true,
            v7_relativeSplatPath: true
        }
    }
);

// App component that uses RouterProvider
const App = () => {
    return <RouterProvider router={router} />;
};

const container = document.getElementById('root');
const root = createRoot(container);
root.render(<App />);
