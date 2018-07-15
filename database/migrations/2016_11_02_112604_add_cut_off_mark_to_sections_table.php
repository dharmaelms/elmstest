<?php

use App\Model\Section;
use Illuminate\Database\Migrations\Migration;

class AddCutOffMarkToSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sections = Section::where('cut_off', 'exists', true)->get();
        if ($sections->count() > 0) {
            $sections->each(function ($section) {
                Section::where('section_id', (int)$section->section_id)->update(['cut_off_mark' => $section->cut_off]);
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
        $sections = Section::where('cut_off_mark', 'exists', true)->get();
        if ($sections->count() > 0) {
            $sections->each(function ($section) {
                Section::where('section_id', (int)$section->section_id)->unset(['cut_off_mark']);
            });
        }
    }
}
