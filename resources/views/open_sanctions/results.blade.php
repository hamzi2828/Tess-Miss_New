<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shareholder Details</title>
    <style>
        /* Styling the collapsible divs */
        body {
            font-family: 'Arial', sans-serif;
        }

        .collapsible {
            background-color: #f1f1f1;
            color: #444;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            text-align: left;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .active, .collapsible:hover {
            background-color: #ccc;
        }

        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #f9f9f9;
        }

        h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .nested-table {
            width: 95%;
            margin-left: 20px;
            margin-top: 10px;
        }
        .basic-details-header {
	background-color: #007BFF;
	color: #ffffff !important;
	padding: 10px 15px;
	border-radius: 5px;
	font-weight: bold;
	text-align: left;
	margin-bottom: 20px;
}
    </style>
</head>
<body>
    <p><strong>Name:</strong> {{ $name }}</p> 
    <p><strong>Birth Date:</strong> {{ $birthDate }}</p> 
    <p><strong>Nationality:</strong> {{ $nationality }}</p>

    @if(!empty($results))
        @php
        $pep_check = false;
        @endphp
        @foreach($results as $index => $result)
            <h2>Result {{ $index + 1 }}</h2>

            <!-- Collapsible div for each sub-array -->
            <button class="collapsible basic-details-header">Show Details</button>
            <div class="content">
                <table>
                    <thead>
                        <tr>
                            <th>Response</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($result as $key => $value)
                        
                            <tr>
                                <td style="vertical-align: top;"><strong>{{ $key }}</strong></td>
                                <td>
                                    @if(is_array($value)) <!-- Check if value is an array -->
                                        
                                            @foreach($value as $index => $subValue)
                                                
                                                    @if(is_array($subValue)) <!-- If subValue is an array, display in a nested table -->
                                                        <table class="nested-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ $index }}</th>
                                                                    <th>Value</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($subValue as $subKey => $nestedValue)
                                                                
                                                                @if($nestedValue=='role.pep')
                                                        @php
                                                            $pep_check = true; // Set your condition here
                                                        @endphp
                                                                @endif
                                                                    <tr>
                                                                        <td>{{ $subKey }}</td> <!-- Replaced with actual sub-array key -->
                                                                        <td>{{ is_string($nestedValue) ? $nestedValue : $nestedValue }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        {{ is_string($subValue) ? htmlspecialchars($subValue, ENT_QUOTES, 'UTF-8') : $subValue }}
                                                        @if($subValue=='role.pep')
                                                        @php
                                                            $pep_check = true; // Set your condition here
                                                        @endphp
                                                                @endif
                                                    @endif
                                                
                                            @endforeach
                                            
                                        
                                    @else
                                        {{ is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : (($key=='score')? '': $value) }}
                                        
                                        
                                        @if(($pep_check) && $key=='score')
                                            <b>{{'PEP Score: '}}</b>
                                           @php
                                            $pep_check = false; // Set your condition here
                                        @endphp 
                                        @endif
                                        @if($key=='score')
                                            {{$value*100;}}%
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <p>No results found.</p>
    @endif

    <script>
        // JavaScript to handle the collapsible functionality
        var coll = document.getElementsByClassName("collapsible");
        for (var i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>

</body>
</html>
