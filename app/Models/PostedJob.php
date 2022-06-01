<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostedJob extends Model {

	use HasFactory;

	protected $fillable = [
		'job_id',
        'job_web',
        'title',
        'company_name',
        'location',
        'time_posted',
	];

}