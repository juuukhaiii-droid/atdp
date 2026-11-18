<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRestDay extends Model
{
    protected $fillable = ['employee_id', 'rest_date'];
    protected $casts = ['rest_date' => 'date'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
