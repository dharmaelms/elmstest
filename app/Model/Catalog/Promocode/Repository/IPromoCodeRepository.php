<?php
namespace App\Model\Catalog\Promocode\Repository;

interface IPromoCodeRepository
{

    public function getPromocode($p_code);

    public function updateRedeem($p_code);

    public function isUsedPromocode($p_code, $u_id);

    public function getPromoCodeList($program_slug);

    public function getPackagePromoCodeList($program_slug);


}
