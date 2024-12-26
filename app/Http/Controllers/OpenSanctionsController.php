<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpenSanctionsController extends Controller
{
    public function fetchData(Request $request)
    {
        $shareholder_id = $request->input('shareholder_id', '');
        $shareholder = DB::table('merchant_shareholders')->where('id', $shareholder_id)->first();
        
        
        $country = DB::table('countries')->where('id', $shareholder->country_id)->first();
        
        
        // Get name, birthDate, nationality from the URL query parameters
        $name = $shareholder->first_name.' '.$shareholder->middle_name.' '.$shareholder->last_name;
        $birthDate = $shareholder->dob;
        $nationality = $country->country_name;
        $pep_check ='no';

        // Set the API URL and retrieve the API key from the environment variable
        $api_url = "https://api.opensanctions.org/match/default?algorithm=best";
        $api_key = "ddb3cfd0f8f4541962ee37f046ec96cd";

        // Define the query examples using the input values
        $example_1 = [
            "weights" => [
                "name_literal_match" => [0.0],
                "name_soundex_match" => [1.0],
            ],
            "schema" => "Person",
            "properties" => [
                "name" => [$name],
                "birthDate" => [$birthDate],
                "nationality" => [$nationality],
            ]
        ];

        // Create a batch query
        $batch = [
            "queries" => [
                "q1" => $example_1,
            ],
        ];

        // Configure the scoring parameters
        $params = http_build_query([
            "algorithm" => "best",
            "fuzzy" => "false",
        ]);

        // Initialize cURL session
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$api_url&$params",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: ApiKey $api_key",
                "Content-Type: application/json",
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($batch),
        ]);

        // Execute the request
        $response = curl_exec($ch);
        $decodedText = html_entity_decode($response);
        $myArray = json_decode($decodedText, true);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return response()->json(['error' => curl_error($ch)]);
        }

        curl_close($ch);

        // Extract results
        $results = $myArray['responses']['q1']['results'] ?? [];

        // Return the data to the view
        return view('open_sanctions.results', compact('results', 'name', 'birthDate', 'nationality'));
    }
    public function convertUnicodeEscape($string) {
    // Decode Unicode escape sequences (e.g., \uXXXX to actual characters)
    return json_decode('"' . $string . '"');
}
}
?>