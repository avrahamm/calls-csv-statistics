import React, { useState } from 'react';

/**
 * UploadCalls component for handling CSV file uploads
 * 
 * This component provides a simple form for uploading CSV files.
 * It handles file selection, validation, and submission to the server.
 * 
 * Testing:
 * - Unit tests can mock the fetch API and test the component's state changes
 * - Integration tests can use a test server to verify the full upload flow
 * - E2E tests can simulate user interactions with the form
 */
const UploadCalls = () => {
    // State for managing the file upload process
    const [file, setFile] = useState(null);
    const [isUploading, setIsUploading] = useState(false);
    const [message, setMessage] = useState(null);
    const [isSuccess, setIsSuccess] = useState(false);

    /**
     * Handle file selection
     * @param {Event} e - The change event from the file input
     */
    const handleFileChange = (e) => {
        setFile(e.target.files[0]);
        setMessage(null);
        setIsSuccess(false);
    };

    /**
     * Handle form submission
     * @param {Event} e - The submit event from the form
     */
    const handleSubmit = async (e) => {
        e.preventDefault();

        // Validate file selection
        if (!file) {
            setMessage('Please select a file to upload');
            setIsSuccess(false);
            return;
        }

        // Validate file type
        if (!file.name.endsWith('.csv')) {
            setMessage('Only CSV files are allowed');
            setIsSuccess(false);
            return;
        }

        // Update UI to show upload in progress
        setIsUploading(true);
        setMessage('Uploading...');

        // Prepare form data for submission
        const formData = new FormData();
        formData.append('callsFile', file, file.name);

        try {
            // Send the file to the server
            const response = await fetch('/api/upload-calls', {
                method: 'POST',
                body: formData
            });

            // Parse the response
            const data = await response.json();

            // Handle successful response
            if (response.ok) {
                setMessage(data.message);
                setIsSuccess(true);
                // Reset the form
                setFile(null);
                document.getElementById('callsFile').value = '';
            } else {
                // Handle error response
                setMessage(data.message || 'Upload failed');
                setIsSuccess(false);
            }
        } catch (error) {
            // Handle network or other errors
            setMessage('Error uploading file: ' + (error.message || 'Unknown error'));
            setIsSuccess(false);
        } finally {
            // Always update UI when upload completes
            setIsUploading(false);
        }
    };

    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Upload calls file</h3>
                        </div>
                        <div className="card-body">
                            {/* Display messages to the user */}
                            {message && (
                                <div className={`alert ${isSuccess ? 'alert-success' : 'alert-danger'}`} role="alert">
                                    {message}
                                </div>
                            )}
                            {/* File upload form */}
                            <form onSubmit={handleSubmit} data-testid="upload-form">
                                <div className="form-group mb-3">
                                    <label htmlFor="callsFile">Select calls file to upload:</label>
                                    <input 
                                        type="file" 
                                        className="form-control mt-2" 
                                        id="callsFile" 
                                        name="callsFile"
                                        onChange={handleFileChange}
                                        accept=".csv"
                                        required
                                        data-testid="file-input"
                                    />
                                </div>
                                <button 
                                    className="btn btn-primary"
                                    disabled={isUploading || !file}
                                    data-testid="upload-button"
                                    type="submit"
                                >
                                    {isUploading ? 'Uploading...' : 'Upload File'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UploadCalls;
