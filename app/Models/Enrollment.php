<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = ['section_id', 'student_id'];
}