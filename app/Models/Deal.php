<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'type', // 'flight', 'hotel', etc.
        'price',
        'currency',
        'source_id', // ID from the external API
        'source', // Name of the external API
        'details', // JSON data with specific details
        'valid_until',
        'url', // Direct link to the deal
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
        'valid_until' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * The users who have bookmarked this deal.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')
            ->withTimestamps();
    }
} 