import React, { useState, useEffect } from 'react';

const Statistics = () => {
    const [statistics, setStatistics] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetch('/api/customer-call-statistics')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                setStatistics(data);
                setLoading(false);
            })
            .catch(error => {
                setError(error.message);
                setLoading(false);
            });
    }, []);

    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Calls data</h3>
                        </div>
                        <div className="card-body">
                            {loading ? (
                                <div className="text-center">
                                    <div className="spinner-border" role="status">
                                        <span className="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            ) : error ? (
                                <div className="alert alert-danger" role="alert">
                                    Error: {error}
                                </div>
                            ) : statistics.length === 0 ? (
                                <div className="alert alert-info" role="alert">
                                    No statistics available.
                                </div>
                            ) : (
                                <table className="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>Number of calls within same continent</th>
                                            <th>Total Duration of calls within same continent</th>
                                            <th>Total number of all calls</th>
                                            <th>Total duration of all calls</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {statistics.map((row, index) => (
                                            <tr key={index}>
                                                <td>{row.customerId}</td>
                                                <td>{row.callsWithinContinent}</td>
                                                <td>{row.durationWithinContinent}</td>
                                                <td>{row.totalCalls}</td>
                                                <td>{row.totalDuration}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Statistics;
