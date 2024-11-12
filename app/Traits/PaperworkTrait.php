<?php

namespace App\Traits;

trait PaperworkTrait
{
    private function calculatePaperworkPayout($paperwork, $parent = null)
    {
        if (! $paperwork->partner_outcome_at) {
            return 0;
        }


        // Get user fee band for this brand
        $brandUser = \App\Models\BrandUser::where('user_id', $paperwork->user_id)->where('brand_id', $paperwork->product->brand_id)->first();
        if ($parent && $parent->id !== $paperwork->user_id) {
            $brandParent = \App\Models\BrandUser::where('user_id', $parent->id)->where('brand_id', $paperwork->product->brand_id)->first();
            if (! $brandParent) {
                return 0;
            }
        }


        // Get product fee band for this product
        $feeband = \App\Models\Feeband::where('product_id', $paperwork->product_id)
            ->where(function ($query) use ($paperwork) {
                $query->where('start_date', '>=', $paperwork->partner_outcome_at)->orWhereNull('start_date');
            })
            ->where(function ($query) use ($paperwork) {
                $query->where('end_date', '>=', $paperwork->partner_outcome_at)->orWhereNull('end_date');
            })->orderBy('is_default', 'asc')->orderBy('start_date', 'asc')->first();

        $payout = 0;
        if ($brandUser && $feeband) {
            switch (strtolower($brandUser->pay_level)) {
                case 'management':
                    $productFee = $feeband->management_fee;
                    break;
                case 'top_partner':
                    $productFee = $feeband->top_partner_fee;
                    break;
                case 'top':
                    $productFee = $feeband->top_fee;
                    break;
                case 'partner':
                    $productFee = $feeband->partner_fee;
                    break;
                case 'smart':
                    $productFee = $feeband->smart_fee;
                    break;
                case 'collaborator':
                    $productFee = $feeband->collaborator_fee;
                    break;
                default:
                    $productFee = 0;
                    break;
            }

            if (isset($brandParent) && $brandParent) {
                switch (strtolower($brandParent->pay_level)) {
                    case 'management':
                        $parentFee = $feeband->management_fee;
                        break;
                    case 'top_partner':
                        $parentFee = $feeband->top_partner_fee;
                        break;
                    case 'top':
                        $parentFee = $feeband->top_fee;
                        break;
                    case 'partner':
                        $parentFee = $feeband->partner_fee;
                        break;
                    case 'smart':
                        $parentFee = $feeband->smart_fee;
                        break;
                    case 'collaborator':
                        $parentFee = $feeband->collaborator_fee;
                        break;
                    default:
                        $parentFee = 0;
                        break;
                }
                $productFee = $parentFee - $productFee;
                if ($productFee < 0) {
                    $productFee = 0;
                }
            }

            if ($feeband->fee_type === 'FISSO') {
                $payout = $productFee;
            } elseif ($feeband->fee_type === 'PERCENTUALE') {
                $payout = $productFee * $paperwork->product->price / 100;
            } elseif ($feeband->fee_type === 'MESE') {
                $payout = $productFee * $paperwork->product->price;
            } elseif ($feeband->fee_type === 'CONSUMO') {
                $payout = $productFee * $paperwork->annual_consumption;
            }

            if ($paperwork->product->discount_percent && $paperwork->appointment_id) {
                $payout -= $payout * $paperwork->product->discount_percent / 100;
            }

            if (isset($brandParent) && $brandParent) {
                $payout += $payout * $brandParent->bonus / 100;
            } else {
                if ($brandUser->bonus) {
                    $payout += $payout * $brandUser->bonus / 100;
                }
            }
        }

        if ($paperwork->partner_outcome === 'STORNO' && $payout > 0) {
            $payout = -$payout;
        }

        return $payout;
    }
}
