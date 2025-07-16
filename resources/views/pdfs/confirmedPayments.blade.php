<!DOCTYPE html>
<html>

<head>
    <title>Confirmed Payment List</title>
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
    <h1>Confirmed Payment List</h1>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Phone</th>
                <th>Tickets</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->full_name }}</td>
                    <td>{{ $payment->phone_number }}</td>
                    <td>
                        @if(is_array($payment->tickets))
                            {{ implode(', ', $payment->tickets) }}
                        @else
                            {{ $payment->tickets }}
                        @endif
                    </td>
                    <td>{{ $payment->address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>