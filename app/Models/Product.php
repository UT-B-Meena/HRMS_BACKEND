<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name','status','created_by', 'updated_by'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function subTasks()
    {
        return $this->hasMany(SubTask::class, 'product_id');
    }

    const STATUS = [
        0 => 'Get to Start',
        1 => 'In Progress',
        2 => 'Hold',
        3 => 'Completed'
    ];
}
