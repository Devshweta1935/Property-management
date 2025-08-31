<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'agent_id',
        'title',
        'description',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'price',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'property_type',
        'status',
        'features',
        'images',
        'is_featured',
        'sold_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'square_feet' => 'decimal:2',
            'features' => 'array',
            'images' => 'array',
            'is_featured' => 'boolean',
            'sold_at' => 'datetime',
        ];
    }

    /**
     * Get the agent that owns the property.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Scope a query to only include properties by the authenticated agent.
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            Log::info('Property model creating event', [
                'data' => $property->toArray()
            ]);
        });

        static::created(function ($property) {
            Log::info('Property model created event', [
                'property_id' => $property->id,
                'data' => $property->toArray()
            ]);
        });

        static::saving(function ($property) {
            Log::info('Property model saving event', [
                'property_id' => $property->id ?? 'new',
                'data' => $property->toArray()
            ]);
        });
    }
}
