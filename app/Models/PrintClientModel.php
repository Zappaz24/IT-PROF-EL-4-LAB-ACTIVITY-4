<?php

namespace App\Models;

use CodeIgniter\Model;

class PrintClientModel extends Model
{
    protected $table         = 'campusprint_clients';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['client_name', 'student_number', 'contact_email', 'course_code', 'year_level', 'is_enrolled'];
}
