<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Data Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>API Data Table</h1>
    @if(!empty($results))
        @foreach ($results as $index => $result)
            <h2>Result {{ $index + 1 }}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result as $key => $value)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>
                                @if(is_array($value))
                                    {{ json_encode($value, JSON_PRETTY_PRINT) }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <p>No data available to display.</p>
    @endif
</body>
</html>
