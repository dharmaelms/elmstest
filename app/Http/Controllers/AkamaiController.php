<?php

namespace App\Http\Controllers;

use App\Model\Dam;
use App\Traits\AkamaiTokenTrait;
use Auth;
use Exception;

class AkamaiController extends Controller
{
    use AkamaiTokenTrait;

    final public function anyRegenerateAkamaiToken($key = null)
    {

        $res = [];
        $token = null;

        try {
            if (!Auth::check()) {
                $res['status'] = 401;
                $res['authorized'] = false;
                $res['error'] = true;
                $res['msg'] = 'Unauthorized';
            } else {
                $asset = [];
                $asset = Dam::getDAMSAssetsUsingID($key);
                $res['status'] = 200;
                $res['authorized'] = true;

                if (empty($asset) || !$key || is_null($key)) {
                    $res['error'] = true;
                    $res['msg'] = trans('dams.missing_asset');
                } else {
                    $asset = $asset[0];
                    //getToken method is in AkamaiTokenTrait
                    $token = $this->getToken($asset);
                    $res['error'] = false;
                    $res['token'] = $token;
                    $res['asset'] = $asset;
                }
            }
        } catch (Exception $e) {
            $res['error'] = true;
            $res['msg'] = "Exception::" . $e->getMessage();
            $res['status'] = 201;
        } finally {
            return response()->json($res)
                ->header('X-Frame-Options', 'deny')
                ->header('X-XSS-Protection', '1');
        }
    }
}
