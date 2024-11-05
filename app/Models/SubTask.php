<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['product_id', 'project_id', 'task_id', 'user_id', 'name', 'estimated_hours', 'dead_line','extended_status','active_status','extended_hours', 'status', 'total_hours_worked', 'rating', 'command', 'assigned_user_id', 'remark', 'reopen_status', 'description', 'team_id', 'priority', 'created_by', 'updated_by'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class,'assigned_user_id');
    }
}

