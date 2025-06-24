<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Individual cooking steps for each recipe
 * 
 * Keeps track of the order and what to do at each step.
 * Simple but essential for following recipes properly.
 */
class RecipeStep extends Model
{
    use HasFactory;

    /**
     * Fields we can mass assign - everything except ID and timestamps
     */
    protected $fillable = [
        'recipe_id',    
        'step_number',  
        'instruction'   
    ];

    /**
     * Belongs to a recipe - another many-to-one relationship
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}