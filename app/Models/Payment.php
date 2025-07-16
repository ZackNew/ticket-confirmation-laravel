<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuids;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'image_link',
        'address',
        'number_of_tickets',
        'dup_flag',
        'dup_id',
        'tickets'
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_status' => 'string',
            'dup_flag' => 'boolean',
            'tickets' => 'array',
        ];
    }

    protected $guarded = [
        'payment_status'
    ];
}
