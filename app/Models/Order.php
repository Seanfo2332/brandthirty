<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_name',
        'customer_email',
        'phone',
        'company_name',
        'website_url',
        'plan',
        'strategy',
        'distribution_reach',
        'total_amount',
        'status',
    ];
}
