<?php

namespace App\Models;

use App\Models\ADMIN\SubSubparameter;
use Illuminate\Database\Eloquent\Model;

class SubSubParameterRating extends Model
{
    protected $table = 'sub_sub_parameter_ratings';

    protected $fillable = [
        'evaluation_id',
        'sub_subparameter_id',
        'rating_option_id',
        'score',
    ];

    public function evaluation()
    {
        return $this->belongsTo(AccreditationEvaluation::class, 'evaluation_id');
    }

    public function subSubParameter()
    {
        return $this->belongsTo(SubSubparameter::class, 'sub_subparameter_id');
    }

    public function ratingOption()
    {
        return $this->belongsTo(RatingOptions::class, 'rating_option_id');
    }
}