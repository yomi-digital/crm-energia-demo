<?php

namespace Database\Seeders\Alfacom;

use App\Models\User;

trait FeebandSeeder
{
    private function feebands($mysqli)
    {
        $users = User::all();
        $totUsers = count($users);
        dump('Total users: ' . $totUsers);
        $countI = 0;
        foreach ($users as $user) {
            $countI++;
            dump('Processing brand_user ' . $countI . ' of ' . $totUsers);
            $config = json_decode($user->commercial_profile, true);
            if (! $config) {
                continue;
            }

            // Need to run this query "select brand_id from products where id in (select product_id from paperworks where user_id = :userId) group by brand_id"
            $brandIds = \DB::select("select brand_id from products where id in (select product_id from paperworks where user_id = :userId) group by brand_id", ['userId' => $user->id]);
            // Convert to array
            $brandIds = array_map(function ($item) {
                return $item->brand_id;
            }, $brandIds);
            if ($brandIds) {
                foreach ($config as $brandId => $feeTypeId) {
                    if (in_array($brandId, $brandIds)) {
                        $payLevel = $this->getPayLevel($feeTypeId);
                        if (! $payLevel) {
                            continue;
                        }
                        $assoc = [
                            'user_id' => $user->id,
                            'brand_id' => $brandId,
                            'pay_level' => $payLevel,
                        ];
                        \App\Models\BrandUser::create($assoc);
                    }
                }
            }
        }
    }

    private function getPayLevel($feeTypeId)
    {
        switch ($feeTypeId) {
            case "STRUTTURA_TOP":
                return 'TOP_PARTNER';
            case "STRUTTURA":
                return 'TOP';
            case "RETEVENDITA":
                return 'PARTNER';
            case "AGENTE":
                return 'SMART';
            case "PROCACCIATORE":
                return 'COLLABORATORE';
            default:
                return null;
        }
    }
}
