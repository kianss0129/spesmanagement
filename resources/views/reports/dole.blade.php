<!DOCTYPE html>
<html>
<head>
    <title>DOLE Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; padding: 5px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>DOLE / SPES Report</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>School ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $b)
            <tr>
                <td>{{ $b->id }}</td>
                <td>{{ $b->first_name }} {{ $b->last_name }}</td>
                <td>{{ $b->email }}</td>
                <td>{{ $b->phone }}</td>
                <td>{{ $b->school_id }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
