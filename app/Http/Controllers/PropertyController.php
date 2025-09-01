<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\PropertyResource;
use App\Mail\PropertyCreatedMail;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PropertyController extends Controller
{

    /**
     * Display a listing of the authenticated agent's properties.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $properties = Property::byAgent($request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            if ($properties->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No properties found',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => $properties->map(function($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'description' => $property->description,
                        'address' => $property->address,
                        'city' => $property->city,
                        'state' => $property->state,
                        'zip_code' => $property->zip_code,
                        'country' => $property->country,
                        'price' => $property->price,
                        'bedrooms' => $property->bedrooms,
                        'bathrooms' => $property->bathrooms,
                        'square_feet' => $property->square_feet,
                        'property_type' => $property->property_type,
                        'status' => $property->status,
                        'features' => $property->features,
                        'images' => $property->images,
                        'is_featured' => $property->is_featured,
                        'created_at' => $property->created_at,
                        'updated_at' => $property->updated_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve properties',
                'error' => 'An error occurred while fetching properties',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Store a newly created property.
     */
    public function store(PropertyRequest $request): JsonResponse
    {
        try {
            // Log the incoming request data
            \Log::info('Property creation request received', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'request_data' => $request->validated()
            ]);

            // Log the data being sent to Property::create
            $propertyData = [
                'agent_id' => $request->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'country' => $request->country ?? 'USA',
                'price' => $request->price,
                'bedrooms' => $request->bedrooms,
                'bathrooms' => $request->bathrooms,
                'square_feet' => $request->square_feet,
                'property_type' => $request->property_type,
                'status' => $request->status ?? 'available',
                'features' => $request->features,
                'images' => $request->images,
                'is_featured' => $request->is_featured ?? false,
            ];

            \Log::info('Attempting to create property with data', $propertyData);

            $property = Property::create($propertyData);

            \Log::info('Property created successfully', ['property_id' => $property->id]);

            // Send email notification directly
            \Log::info('Attempting to send email notification directly', ['email' => $request->user()->email]);
            try {
                Mail::to($request->user()->email)->send(new PropertyCreatedMail($property));
                \Log::info('Email notification sent successfully');
            } catch (\Exception $e) {
                \Log::error('Email sending failed', [
                    'error' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'property_id' => $property->id,
                    'user_email' => $request->user()->email
                ]);
                // Don't fail the entire request if email sending fails, but log the error
            }

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'status_code' => 201,
                'data' => new PropertyResource($property),
            ], 201);
        } catch (\Exception $e) {
            // Log the detailed error information
            \Log::error('Property creation failed', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id ?? 'unknown',
                'request_data' => $request->validated() ?? []
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create property',
                'error' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ],
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Request $request, Property $property): JsonResponse
    {
        try {
            // Ensure the property belongs to the authenticated agent
            if ($property->agent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                    'error' => 'The requested property does not exist or you do not have access to it',
                    'status_code' => 404,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => new PropertyResource($property),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve property',
                'error' => 'An error occurred while fetching the property',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Update the specified property.
     */
    public function update(PropertyRequest $request, Property $property): JsonResponse
    {
        try {
            // Ensure the property belongs to the authenticated agent
            if ($property->agent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                    'error' => 'The requested property does not exist or you do not have access to it',
                    'status_code' => 404,
                ], 404);
            }

            $property->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'status_code' => 200,
                'data' => new PropertyResource($property),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property',
                'error' => 'An error occurred while updating the property',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Request $request, Property $property): JsonResponse
    {
        try {
            // Ensure the property belongs to the authenticated agent
            if ($property->agent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found',
                    'error' => 'The requested property does not exist or you do not have access to it',
                    'status_code' => 404,
                ], 404);
            }

            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully',
                'status_code' => 200,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property',
                'error' => 'An error occurred while deleting the property',
                'status_code' => 500,
            ], 500);
        }
    }

    /**
     * Test email sending functionality
     */
    public function testEmail(Request $request): JsonResponse
    {
        try {
            \Log::info('Test email endpoint called', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email
            ]);

            // Get a sample property for testing
            $property = Property::where('agent_id', $request->user()->id)->first();
            
            if (!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'No properties found for testing email',
                    'status_code' => 404,
                ], 404);
            }

            // Test email sending directly
            try {
                Mail::to($request->user()->email)->send(new PropertyCreatedMail($property));
                
                \Log::info('Test email sent successfully', [
                    'user_email' => $request->user()->email,
                    'property_id' => $property->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test email sent successfully',
                    'status_code' => 200,
                    'data' => [
                        'email_sent_to' => $request->user()->email,
                        'property_used_for_test' => $property->title,
                        'email_status' => 'Email sent directly (no queue)',
                        'mail_config' => [
                            'host' => config('mail.mailers.smtp.host'),
                            'port' => config('mail.mailers.smtp.port'),
                            'encryption' => config('mail.mailers.smtp.encryption'),
                            'username' => config('mail.mailers.smtp.username')
                        ]
                    ]
                ]);

            } catch (\Exception $e) {
                \Log::error('Test email failed', [
                    'error' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine(),
                    'mail_config' => [
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'encryption' => config('mail.mailers.smtp.encryption'),
                        'username' => config('mail.mailers.smtp.username')
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Test email sending failed',
                    'error' => $e->getMessage(),
                    'error_details' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'mail_config' => [
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'encryption' => config('mail.mailers.smtp.encryption'),
                        'username' => config('mail.mailers.smtp.username')
                    ],
                    'status_code' => 500,
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Test email endpoint failed', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test email endpoint failed',
                'error' => $e->getMessage(),
                'status_code' => 500,
            ], 500);
        }
    }


    /**
     * Get properties for the authenticated agent.
     */
    public function myProperties(Request $request): JsonResponse
    {
        try {
            $properties = Property::byAgent($request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($properties->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No properties found for this agent',
                    'status_code' => 200,
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => $properties->map(function($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'description' => $property->description,
                        'address' => $property->address,
                        'city' => $property->city,
                        'state' => $property->state,
                        'zip_code' => $property->zip_code,
                        'country' => $property->country,
                        'price' => $property->price,
                        'bedrooms' => $property->bedrooms,
                        'bathrooms' => $property->bathrooms,
                        'square_feet' => $property->square_feet,
                        'property_type' => $property->property_type,
                        'status' => $property->status,
                        'features' => $property->features,
                        'images' => $property->images,
                        'is_featured' => $property->is_featured,
                        'created_at' => $property->created_at,
                        'updated_at' => $property->updated_at,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve agent properties',
                'error' => 'An error occurred while fetching agent properties',
                'status_code' => 500,
            ], 500);
        }
    }
}
