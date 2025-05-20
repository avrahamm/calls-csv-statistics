import React from 'react';

const Statistics = () => {
    // Dummy data for the table
    const dummyData = [
        {
            customerId: 'CUST001',
            callsWithinContinent: 25,
            durationWithinContinent: 1250,
            totalCalls: 42,
            totalDuration: 2100
        },
        {
            customerId: 'CUST002',
            callsWithinContinent: 18,
            durationWithinContinent: 900,
            totalCalls: 30,
            totalDuration: 1500
        },
        {
            customerId: 'CUST003',
            callsWithinContinent: 35,
            durationWithinContinent: 1750,
            totalCalls: 50,
            totalDuration: 2500
        }
    ];

    return (
        <div className="container mt-5">
            <div className="row">
                <div className="col-md-12">
                    <div className="card">
                        <div className="card-header">
                            <h3>Calls data</h3>
                        </div>
                        <div className="card-body">
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
                                    {dummyData.map((row, index) => (
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Statistics;