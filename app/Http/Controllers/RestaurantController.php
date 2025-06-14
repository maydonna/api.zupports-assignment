<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        // Validate the request parameters
        $query = $request->validate([
            'keyword' => 'nullable|string|max:255',
            'nextPageToken' => 'nullable|string',
        ]);

        // Declare a cache key based on the query parameters
        $cacheKey = 'restaurants:' . md5(json_encode($query));

        // Check if the results are cached
        if (cache()->has($cacheKey)) {
            return response()->json(cache()->get($cacheKey));
        }

        // If not cached, proceed to make the API request to Google Maps
        // Ensure the Google Maps API base URL is configured
        $baseUrl = config('services.google-map.place_base_url');
        if (!$baseUrl) {
            return response()->json([
                'status' => 'error',
                'message' => 'Google Maps API base URL is not configured.',
            ], 500);
        }

        // Prepare the parameters for the API request
        // 1. textQuery is used to search for places based on a text query
        // 2. includedType is used to filter the results to only include restaurants
        // 3. pageSize is used to limit the number of results returned
        // 4. If the keyword is not provided, default to 'Bang Sue'
        $requestBody = [
            'textQuery' => 'restaurants in '.($query['keyword'] ?? 'Bang Sue'), // add 'restaurants in ' prefix to the keyword to make it more specific type for search result
            'includedType' => 'restaurant', // default type to restaurant
            'pageSize' => 10, // limit the number of results to 10
        ];

        // If nextPageToken is provided, include it in the request body for pagination
        if($request->has('nextPageToken') && !empty($request->input('nextPageToken'))) {
            $requestBody['pageToken'] = $request->input('nextPageToken');
        }

        // Make the API request
        // X-Goog-FieldMask is used to specify which fields to include in the response
        // nextPageToken is used for pagination
        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => config('services.google-map.api_key'),
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.shortFormattedAddress,places.location,places.priceRange,places.primaryTypeDisplayName,places.rating,places.userRatingCount,places.websiteUri,places.internationalPhoneNumber,places.googleMapsUri,nextPageToken',
            ])->post($baseUrl . '/places:searchText', $requestBody);

        // Check if the response is successful
        if ($response->successful()) {
            // Parse the response and return the results
            $data = $response->json();
            $responseData = [
                'status' => 'success',
                'data' => $this->handleResponse(collect($data['places'] ?? [])),
                'total_results' => count($data['places'] ?? []),
                'nextPageToken' => $data['nextPageToken'] ?? null,
            ];

            // Cache the results for future requests
            // The cache duration is set to 30 minutes
            cache()->put($cacheKey, $responseData, now()->addMinutes(30));

            // Return the response in JSON format
            // The handleResponse method is used to format the response data
            return response()->json($responseData);
        } else {
            // Handle errors from the API
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data from Google Maps API.',
                'error' => $response->body(),
            ], $response->status());
        }
    }

    // This method processes the API response and formats the data
    // It maps the places data to a more readable format
    protected function handleResponse($places)
    {
        return $places->map(function ($place) {
            return [
                'id' => $place['id'],
                'name' => $place['displayName']['text'] ?? '',
                'address' => $place['shortFormattedAddress'] ?? '',
                'location' => $place['location'] ?? null,
                'price_range' => $this->getPriceRange($place['priceRange'] ?? []),
                'cuisine' => $place['primaryTypeDisplayName']['text'] ?? '',
                'rating' => $place['rating'] ?? null,
                'rating_count' => $place['userRatingCount'] ?? 0,
                'website_uri' => $place['websiteUri'] ?? '',
                'phone' => $place['internationalPhoneNumber'] ?? '',
                'google_maps_uri' => $place['googleMapsUri'] ?? '',
            ];
        });
    }

    // This method formats the price range from the API response
    // It checks if the start and end prices are set, and formats them accordingly
    protected function getPriceRange($priceRange): string|null
    {
        $priceRangeText = '';

        if(isset($priceRange['startPrice']['units'])) {
            $priceRangeText .= $priceRange['startPrice']['units'];
        }

        if(isset($priceRange['endPrice']['units'])) {
            if (!empty($priceRangeText)) {
                $priceRangeText .= ' - ';
            }

            $priceRangeText .= $priceRange['endPrice']['units'];
        }

        return $priceRangeText ? $priceRangeText.' '.$priceRange['startPrice']['currencyCode'] : null;
    }
}
