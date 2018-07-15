<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Model\User;
use App\Model\CustomFields\Entity\CustomFields;
use Illuminate\Support\Facades\DB;

class UpdateCustomFieldAttributesInUsersCollection extends Migration
{
    /**
     * @var MongoDB
     */
    private $mongodb;

    public function __construct()
    {
        $this->mongodb = DB::getMongoDB();
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $users = User::orderBy('uid', 'desc')->get()->toArray();
        $custom_fields = CustomFields::where('program_type', '=', 'user')
            ->where('status', '=', 'ACTIVE')->get()->toArray();
        
        $customField = [];
        foreach ($custom_fields as $user) {
            $customField[array_get($user, 'fieldlabel')] = null;
        }
        
        foreach ($users as $key => $record) {
            $userlist = array_intersect_key($record, $customField);
            $custom_values = array_values($userlist);
            foreach ($userlist as $column => $value) {
                if (array_key_exists($column, $record)) {
                    $column_field_name = CustomFields::where('program_type', '=', 'user')
                    ->where('status', '=', 'ACTIVE')
                    ->where('fieldlabel', '=', $column)
                    ->pluck('fieldname')->toArray();
                    $column_field_name = array_first($column_field_name);
                    if ($column != $column_field_name) {
                        $this->mongodb->selectCollection("users")->update(
                            ["uid" => $record['uid']], 
                            ["\$rename" => [$column => $column_field_name]],
                            ["multiple" => true]
                        );
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
