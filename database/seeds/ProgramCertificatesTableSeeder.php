<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class ProgramCertificatesTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'program_certificates';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('id');
        });

        $html = <<<EOF
        <!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
<style>
.w3-theme {color:#fff !important; background-color:#009ca6 !important};
.w3-text-theme {color:#565658 !important}
/* @page { 
   size: 8.5in 11in; 
   size : landscape; }*/
 
h3{
  
  font-size: 36px;
  line-height: 1.8em;
  color:#565658 !important;

}
h1{
  font-size: 42px;
  text-transform: uppercase;
}

</style>
<body>

<div class="w3-container w3-border" style="margin: 0 auto;
    width: 80%; vertical-align: middle;">
<img src="{site_logo}" class="w3-padding-top" style="margin-bottom: 10px;">
<div class="w3-container w3-section w3-theme">
  <h1 class="w3-center"><b>Certificate of Completion</b></h1>
</div>
<div>
</div>
<h3 class="w3-container w3-center"> This is to certify that<br><b class="">{username}</b><br>has successfully completed the course<br><b>{program_title}</b><br>on<br><b>{generated_at}</b></h3>
<div class="w3-container w3-section w3-theme">
<h1>&nbsp;  </h1>
</div>
</div>
</body>
</html>
EOF;
        // General settings
        DB::collection($collection)->insert([
            'id' => 1,
            'name' => 'Default',
            'content' => $html,
            'is_default' => 1,
        ]);

    /* Adding 2nd certificate template starts here */
        $html_1 = <<<EOF
        <html>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
        <style type="text/css">
            .heading{
                padding: 1em 16px;
                background-color: #c0504d !important;
                color: #fff !important;
                outline: 2px solid #8c3836;
                margin-bottom: 16px !important;
                margin-top: 0px !important;
            }
          h3{
                font-size: 26px;
                line-height: 1.8em;
                color:#565658 !important;
            }
          h1{
                font-size: 27px;
                text-transform: uppercase;
          }
          .fltrht{
                float: right;
          }
          .fltlft{
                float: left;
          }
          .clear{
                clear: both;
          }
          .logo2{
                margin-top: 10px;
          }
          .logo1{
                margin-top: 3px;
          }
          .end-line{
                margin-top: 24px !important;
                background-color: #c0504d !important;
                color: #fff !important;
                outline: 2px solid #8c3836;
                margin-bottom: 16px !important;
          }
          .text-center{
                text-align: center;
          }
          #main-container-div{
                margin: 0 auto !important;
                width: 80%; 
                vertical-align: middle;
                padding: 0.01em 16px;
                border: 1px solid #ccc;
                height: 52em !important;
          }
          img{
                display: block;
          }
        </style>
        <body>
          <div id="main-container-div">
            <div><img src="{site_logo}" width="20%" class="fltlft logo1">
            <img src="{second_logo}" width="15%" class="fltrht logo2"></div>
            <div class="clear" style="height: 5px;"></div>
            <div class="heading">
              <h1 class="text-center"><b>Certificate of Completion</b></h1>
            </div>
            <h3 class="text-center"> This is to certify that<br><b class="">{username}</b><br>has successfully completed the course<br><b>{program_title}</b><br>on<br><b>{generated_at}</b></h3>
            <div style="width: 40%;text-align: center;">
                <img src="{signature_image}" width="40%">
                <h6>{signature_name}</h6>
            </div>
            <div class="end-line">
            <h6>&nbsp;  </h6>
            </div>
          </div>
        </body>
        </html>
EOF;
        // template 2 settings
        DB::collection($collection)->insert([
            'id' => 2,
            'name' => 'Certificate_template2',
            'content' => $html_1,
            'is_default' => 0,
        ]);
    /* Adding 2nd certificate template ends here */
    }
}
