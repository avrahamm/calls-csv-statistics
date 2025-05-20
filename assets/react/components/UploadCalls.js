import React from 'react';

const UploadCalls = () => {
    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Upload calls file</h3>
                        </div>
                        <div className="card-body">
                            <form action="/" method="post" encType="multipart/form-data">
                                <div className="form-group mb-3">
                                    <label htmlFor="callsFile">Select calls file to upload:</label>
                                    <input 
                                        type="file" 
                                        className="form-control mt-2" 
                                        id="callsFile" 
                                        name="callsFile" 
                                    />
                                </div>
                                <button type="submit" className="btn btn-primary">Upload File</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UploadCalls;