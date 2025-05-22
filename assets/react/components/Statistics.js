import React, { useState, useEffect } from 'react';

const Statistics = () => {
    const [statistics, setStatistics] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // Initial data fetch
    useEffect(() => {
        fetchStatistics();
    }, []);

    // Polling for updates every 60 seconds
    useEffect(() => {
        const intervalId = setInterval(() => {
            checkForUpdates();
        }, 60000); // 60 seconds

        return () => clearInterval(intervalId);
    }, []);

    // Fetch all statistics
    const fetchStatistics = async () => {
        try {
            const response = await fetch('/api/customer-call-statistics');
            if (!response.ok) {
                throw new Error('Failed to fetch statistics');
            }

            const data = await response.json();
            setStatistics(data);
            setLoading(false);
        } catch (error) {
            setError(error.message);
            setLoading(false);
        }
    };

    // Check for updates
    const checkForUpdates = async () => {
        try {
            // Create a map of customer IDs to last updated timestamps
            const customerUpdates = {};
            statistics.forEach(stat => {
                customerUpdates[stat.customerId] = stat.lastUpdated;
            });

            // If statistics is empty, fetch all statistics instead of checking for updates
            if (statistics.length === 0) {
                fetchStatistics();
                return;
            }

            const response = await fetch('/api/customer-call-statistics/check-updates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ customerUpdates }),
            });

            if (!response.ok) {
                throw new Error('Failed to check for updates');
            }

            const data = await response.json();
            const { updatedStatistics, allCustomerIds } = data;

            // Update the statistics state with the updated data
            setStatistics(prevStats => {
                // Create a new array to hold the updated statistics
                let newStatistics = [];

                // First, add all updated statistics
                if (updatedStatistics && updatedStatistics.length > 0) {
                    // Create a map of customer IDs to updated statistics for quick lookup
                    const updatedStatsMap = {};
                    updatedStatistics.forEach(stat => {
                        updatedStatsMap[stat.customerId] = stat;
                    });

                    // Add all existing statistics that are still in the database
                    // and update those that have new data
                    prevStats.forEach(stat => {
                        const customerId = stat.customerId;

                        // If this customer ID is in the updated stats, use the updated data
                        if (updatedStatsMap[customerId]) {
                            newStatistics.push(updatedStatsMap[customerId]);
                            delete updatedStatsMap[customerId]; // Remove from map to avoid duplicates
                        } 
                        // If this customer ID is still in the database but not updated, keep it
                        else if (allCustomerIds.includes(customerId)) {
                            newStatistics.push(stat);
                        }
                        // If this customer ID is not in the database anymore, it will be excluded
                    });

                    // Add any new statistics that weren't in the previous state
                    Object.values(updatedStatsMap).forEach(stat => {
                        newStatistics.push(stat);
                    });
                } else {
                    // If there are no updated statistics, just filter out removed customer IDs
                    newStatistics = prevStats.filter(stat => 
                        allCustomerIds.includes(stat.customerId)
                    );
                }

                return newStatistics;
            });
        } catch (error) {
            console.error('Error checking for updates:', error);
        }
    };

    if (loading) {
        return <div className="container mt-5">Loading...</div>;
    }

    if (error) {
        return <div className="container mt-5">Error: {error}</div>;
    }

    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Calls data</h3>
                            <small>Auto-refreshes every 60 seconds</small>
                        </div>
                        <div className="card-body">
                            {statistics.length === 0 ? (
                                <p>No statistics available.</p>
                            ) : (
                                <table className="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>Number of calls within same continent</th>
                                            <th>Total Duration of calls within same continent</th>
                                            <th>Total number of all calls</th>
                                            <th>Total duration of all calls</th>
                                            <th>Last Updated</th>
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
                                                <td>{row.lastUpdated}</td>
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
