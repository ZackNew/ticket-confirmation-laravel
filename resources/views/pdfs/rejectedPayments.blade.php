<!DOCTYPE html>
<html>

<head>
    <title>Rejected Payment List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Rejected Payment List</h1>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Number of tickets</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->full_name }}</td>
                    <td>{{ $payment->phone_number }}</td>
                    <td>{{ $payment->number_of_tickets }}</td>
                    <td>{{ $payment->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>