<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrintClientTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'client_name' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'student_number' => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => false],
            'contact_email' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'course_code' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'year_level' => ['type' => 'INT', 'constraint' => 11, 'null' => false],
            'is_enrolled' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('student_number');
        $this->forge->createTable('campusprint_clients');
    }

    public function down()
    {
        $this->forge->dropTable('campusprint_clients');
    }
}
