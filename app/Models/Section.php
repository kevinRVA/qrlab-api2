<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'section_code', 'schedule'];
}