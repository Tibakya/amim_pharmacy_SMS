<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product', 'category_id', 'supplier_id', 'cost_price', 'quantity', 'expiry_date', 'image', 'batch_number'
    ];

    // Supplier relationship
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Category relationship
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Product relationship
    public function purchaseProduct()
    {
        return $this->hasOne(Product::class);
    }

    // Optionally, you can define a relationship for batch_number if needed
    public function sales()
    {
        return $this->hasMany(Sale::class, 'batch_number', 'batch_number');
    }
}
