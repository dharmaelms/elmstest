<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
