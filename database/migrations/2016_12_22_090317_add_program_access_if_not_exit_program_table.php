<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\Program;


class AddProgramAccessIfNotExitProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $records = Program::where('program_access', 'exists', false)->get();
      
          if ($records->count() > 0) {
                $records->each(function ($program) {

                    if($program->program_sellability == 'yes'){
                        Program::where('program_id','=', (int)$program->program_id)
                        ->update(['program_access' => 'restricted_access']);

                    }

                
                });
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
