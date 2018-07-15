<?php

use App\Model\States;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertCountryStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            States::where("country_code", "IN")->firstOrFail();
        } catch (ModelNotFoundException $e) {
            DB::collection("states")->insert(
                [
                    "country_code" => "IN",
                    "states" => [
                        "Andaman and Nicobar Islands",
                        "Andhra Pradesh",
                        "Arunachal Pradesh",
                        "Assam",
                        "Bihar",
                        "Chandigarh",
                        "Chhattisgarh",
                        "Dadra and Nagar Haveli",
                        "Daman and Diu",
                        "Delhi",
                        "Goa",
                        "Gujarat",
                        "Haryana",
                        "Himachal Pradesh",
                        "Jammu and Kashmir",
                        "Jharkhand",
                        "Karnataka",
                        "Kerala",
                        "Lakshadweep",
                        "Madhya Pradesh",
                        "Maharashtra",
                        "Manipur",
                        "Meghalaya",
                        "Mizoram",
                        "Nagaland",
                        "Odisha",
                        "Pondicherry",
                        "Punjab",
                        "Rajasthan",
                        "Sikkim",
                        "Tamil Nadu",
                        "Telangana",
                        "Tripura",
                        "Uttar Pradesh",
                        "Uttarakhand",
                        "West Bengal"
                    ]
                ]
            );
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
