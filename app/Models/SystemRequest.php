<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'department_id',
        'requested_by',
        'reported_at',
        'target_due_date',
        'completed_at',
        'effort_hours',
        'developer_name',
        'developer_id',
        'comments',
        'created_by',
    ];

    protected $casts = [
        'reported_at' => 'date',
        'target_due_date' => 'date',
        'completed_at' => 'date',
        'effort_hours' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
