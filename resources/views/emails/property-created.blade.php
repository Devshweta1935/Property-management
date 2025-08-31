<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Created Successfully</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .property-details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .property-details h3 {
            margin-top: 0;
            color: #4CAF50;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Property Created Successfully!</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $property->agent->name }},</p>
        
        <p>Your property has been successfully created and is now live in our system. Here are the details:</p>
        
        <div class="property-details">
            <h3>{{ $property->title }}</h3>
            
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">{{ $property->address }}, {{ $property->city }}, {{ $property->state }} {{ $property->zip_code }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Price:</span>
                <span class="detail-value">${{ number_format($property->price, 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Property Type:</span>
                <span class="detail-value">{{ ucfirst($property->property_type) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Bedrooms:</span>
                <span class="detail-value">{{ $property->bedrooms ?? 'N/A' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Bathrooms:</span>
                <span class="detail-value">{{ $property->bathrooms ?? 'N/A' }}</span>
            </div>
            
            @if($property->square_feet)
            <div class="detail-row">
                <span class="detail-label">Square Feet:</span>
                <span class="detail-value">{{ number_format($property->square_feet) }} sq ft</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">{{ ucfirst($property->status) }}</span>
            </div>
            
            @if($property->features)
            <div class="detail-row">
                <span class="detail-label">Features:</span>
                <span class="detail-value">{{ implode(', ', $property->features) }}</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Created:</span>
                <span class="detail-value">{{ $property->created_at->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>
        
        <p>Your property is now visible to potential buyers and renters. You can manage it through your agent dashboard.</p>
        
        <p>If you have any questions or need to make changes, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>
        The Property Management Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} Property Management System. All rights reserved.</p>
    </div>
</body>
</html>
