<?php

namespace App\Helps;


use chillerlan\QRCode\QRCode;
use Carbon\Carbon;


/**
 * Build Card from Data
 *
 * 
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PcvInsuredCardBuilder
{
    const MASTER_DENTAL = 20000000;
    const MASTER_DENTAL_LS1 = 5000000;
    const MASTER_DENTAL_LS2 = 10000000;

    private $data;
    private $lang_date;
    /**
     * @throws
     */
    public function __construct($member, $lang)
    {
        $cardDetail = $this->getCardBenefit($member, $lang);
        $this->data = $cardDetail !== false
                ? $this->getInsuranceCard($cardDetail['detail']) : null;
        $this->lang_date = $lang;
    }

    /**
     * @throws
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * @throws
     */
    private function getCardBenefit($member, $lang)
    {
        $benSchedule = json_decode($member['ben_schedule'], true);
        $planDescs = $member['plan_desc'];
        $mbrNoString = substr($member['mbr_no'], 0, 7) . '-' . substr($member['mbr_no'], -2);
        $pocyNoString = explode('.', $member['pocy_ref_no']);
        $pocyNoString = $pocyNoString[0];

        if (strpos($benSchedule['detail']['copay'], '100%') !== false)
        {
            $benSchedule['detail']['copay'] = __('frontend.no');
        }

        $benSchedule['detail']['dedAmt'] = !isset($benSchedule['detail']['dedAmt'])
                ? __('frontend.no')
                : __('frontend.p_deductible');

        # Out
        $benSchedule['detail']['outpatient'] = $benSchedule['tmp']['HAS_OP'] == 'Y'
                ? __('frontend.AsCharged')
                : __('frontend.no');

        $benSchedule['detail']['dental'] = $benSchedule['tmp']['HAS_DT'] == 'Y'
                ? __('frontend.yes')
                : __('frontend.no');
        if($benSchedule['tmp']['PRODUCT_TYPE']=='HF'){
            // Health first plan
            $benSchedule['detail']['plan_type'] = $this->getPlanTypeHF($benSchedule['tmp']['PLAN_DESC']);
            $benSchedule['detail']['outpatient'] = $benSchedule['tmp']['HAS_OP'] == 'Y' ? $this->getOutPatientHF($benSchedule['tmp']['PLAN_DESC']) : __('frontend.no');
            $benSchedule['detail']['dental'] = $benSchedule['tmp']['HAS_DT'] == 'Y' ? $this->getDentalHF($benSchedule['tmp']['PLAN_DESC']) : __('frontend.no');
            $benSchedule['detail']['product'] = 'healthfirst';
        } elseif (
            strpos($benSchedule['tmp']['PLAN_DESC'], 'M1') !== false ||
            strpos($benSchedule['tmp']['PLAN_DESC'], 'M2') !== false ||
            strpos($benSchedule['tmp']['PLAN_DESC'], 'M3') !== false
        )
        { // Master plan
            $benSchedule['detail']['plan_type'] = $this->getPlanTypeM($benSchedule['tmp']['PLAN_DESC']);
            $dentalM = $this->getDentalM($planDescs);
            if ($benSchedule['tmp']['HAS_DT'] == 'Y')
            {
                $dentalM = $dentalM + self::MASTER_DENTAL;
                $benSchedule['detail']['dental'] = number_format($dentalM) . ' ' . __('frontend.p_dental_m');
            }
            elseif ($dentalM)
            {
                $benSchedule['detail']['dental'] = number_format($dentalM) . ' ' . __('frontend.p_dental_m');
            }

            $benSchedule['detail']['checkup'] = $this->getCheckupM($planDescs);
            $benSchedule['detail']['product'] = 'master';
        }
        else
        { // Foundation plan
            $benSchedule['detail']['plan_type'] = $this->getPlanTypeF($benSchedule['tmp']['PLAN_DESC']);
            if ($benSchedule['tmp']['HAS_OP'] == 'Y')
            {
                $benSchedule['detail']['outpatient'] = $this->getOutPatientF($benSchedule['tmp']['PLAN_DESC']);
            }
            if ($benSchedule['tmp']['HAS_DT'] == 'Y')
            {
                $benSchedule['detail']['dental'] = $this->getDentalF($benSchedule['tmp']['PLAN_DESC']);
            }
            $benSchedule['detail']['checkup'] = $this->getMedicalCheckupF($benSchedule['tmp']['PLAN_DESC']);
            $benSchedule['detail']['product'] = 'foundation';
        }
        unset($benSchedule['detail']['amtperday'], $benSchedule['detail']['amtpervis']);

        $benSchedule['detail']['lang'] = $lang;
        $benSchedule['detail']['name'] = $lang === 'en'
			? "{$member['mbr_first_name']} {$member['mbr_last_name']}"
			: "{$member['mbr_last_name']} {$member['mbr_first_name']}";
        $benSchedule['detail']['pocy_no'] = $member['pocy_no'];
        $benSchedule['detail']['pocy_no_s'] = $pocyNoString;
        $benSchedule['detail']['mbr_no'] = $member['mbr_no'];
        $benSchedule['detail']['mbr_no_s'] = $mbrNoString;
        $benSchedule['detail']['eff_date'] = $this->lang_date == 'en' ? ( Carbon::createFromFormat("Y-m-d",$member['memb_eff_date'])->format('d-F-Y') ) : ( Carbon::createFromFormat("Y-m-d",$member['memb_eff_date'])->format('d-m-Y') ) ;
        $benSchedule['detail']['exp_date'] = $this->lang_date == 'en' ? ( Carbon::createFromFormat("Y-m-d",$member['memb_exp_date'])->format('d-F-Y') ) : ( Carbon::createFromFormat("Y-m-d",$member['memb_exp_date'])->format('d-m-Y') ) ;
        $benSchedule['detail']['dob'] = date('d/m/Y', strtotime($member['dob']));

        if ($member['wait_period'] === 'No')
        {
            $benSchedule['detail']['waiting'] = __('frontend.no');
            if ($member['reinst_date'] !== null)
            {
                $benSchedule['detail']['waiting'] = __('frontend.p10_waiting');
            }
        }
        elseif ($member['wait_period'] === 'Yes')
        {
            $benSchedule['detail']['waiting'] = __('frontend.p30_waiting');
        }

        $benSchedule['detail']['exclusion'] = $member['memb_rstr'] !== null
                ? __('frontend.yes')
                : __('frontend.no');

        return $benSchedule;
    }

    /**
     * @throws
     */
    private function getInsuranceCard($detail)
    {
        global $ds;
        
        $font = resource_path('fonts/arial.ttf');
        if ($detail['product'] == 'master')
        {
            $imgUrl = resource_path("images/".$detail["lang"]."/master_fore.gif");
            $imgMackUrl = resource_path("images/".$detail["lang"]."/master_back_text.gif");
        }
        elseif($detail['product'] == 'healthfirst')
        {
            $imgUrl = resource_path("images/".$detail["lang"]."/healthfirst_fore.gif");
            $imgMackUrl = resource_path("images/".$detail["lang"]."/healthfirst_back.gif");
        } else
        {
            $imgUrl = resource_path("images/".$detail["lang"]."/foundation_fore.gif");
            $imgMackUrl = resource_path("images/".$detail["lang"]."/foundation_back_text.gif");
        }

        $imgName = $detail['pocy_no'] . $detail['mbr_no'];

        $img = imagecreatefromgif($imgUrl);

        $black = imagecolorclosest($img, 0, 0, 0);
        
        $color = imagecolorallocate($img, 255 , 255, 255);
        $color = imagecolorclosest($img, 0 , 155, 222);
        
        $baseLine = 16;
        $tab = 25;
        $labelStartX = 80;
        $labelFontSize = 15;
        $fontSize = 18;
        
        if($detail['product'] == 'healthfirst'){
            $black = imagecolorclosest($img, 255, 255, 255);
            $color = imagecolorclosest($img, 3 , 243, 255);
            $tab = 30;
        }
        # Name
        $startX = 182;
        $startY = 280;
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['name']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_name')
        );

        # Policy No
        $startX += 2 * $tab;
        $startY += 2 * $baseLine;
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['pocy_no_s']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_policy')
        );

        # Member No
        $startX += 2 * $tab;
        $startY += 2 * $baseLine;
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['mbr_no_s']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_member_no')
        );

        # Effective From
        $startX -= 2*$tab;
        $startY += 2*$baseLine;
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['eff_date']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_valid_from')
        );
        
        if($detail['product'] == 'healthfirst'){
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX + 13 * $tab,
                $startY, $color, $font, __('frontend.p_valid_to')
            );
            imagettftext(
                $img, $fontSize, 0, $startX + 9 * $tab, $startY,
                $black, $font, $detail['exp_date']
            );

        } else {
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX + 17 * $tab,
                $startY, $color, $font, __('frontend.p_valid_to')
            );
            imagettftext(
                $img, $fontSize, 0, $startX + 13 * $tab, $startY,
                $black, $font, $detail['exp_date']
            );
        }

        # image line
        imageline(
            $img, 60, $startY + $baseLine,
            $startX + 630, $startY + $baseLine, $color
        );
        imageline(
            $img, 60, $startY + $baseLine + 1,  $startX + 630,
            $startY + $baseLine + 1, $color
        );

        if($detail['product'] == 'healthfirst'){
            # Plan
            $startY += 3.3 * $baseLine;
            imagettftext(
                $img, $fontSize, 0, $startX, $startY,
                $black, $font, $detail['plan_type']
            );
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX, $startY,
                $color, $font, __('frontend.p_plan_type_hf')
            );
            $startY += 2 * $baseLine;
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX, $startY,
                $color, $font, __('frontend.p_outpatient_hf')
            );
            imagettftext(
                $img, $fontSize, 0, $startX, $startY,
                $black, $font, $detail['outpatient']
            );
        } else {
            # Plan
            $startY += 3.3 * $baseLine;
            imagettftext(
                $img, $fontSize, 0, $startX, $startY,
                $black, $font, $detail['plan_type']
            );
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX, $startY,
                $color, $font, __('frontend.p_plan_type')
            );
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX + 12 * $tab, $startY,
                $color, $font, __('frontend.p_outpatient')
            );
            imagettftext(
                $img, $fontSize, 0, $startX + 10 * $tab, $startY,
                $black, $font, $detail['outpatient']
            );
        }

        #Co-payment
        $startY += 2 * $baseLine;
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['copay']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_co_pay')
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX + 10 * $tab, $startY,
            $color, $font, __('frontend.p_dental')
        );
        imagettftext(
            $img, $fontSize, 0, $startX + 8 * $tab, $startY,
            $black, $font, $detail['dental']
        );

        if($detail['product'] != 'healthfirst'){
            # Deductible
            $startY += 2 * $baseLine;
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX, $startY,
                $color, $font, __('frontend.p_l_deductible')
            );
            imagettftext(
                $img, $fontSize, 0, $startX, $startY,
                $black, $font, $detail['dedAmt']
            );
            imagettftext(
                $img, $labelFontSize, 0, $labelStartX + 10 * $tab, $startY,
                $color, $font, __('frontend.p_l_medical_checkup')
            );
            imagettftext($img, $fontSize, 0, $startX+11*$tab, $startY, $black, $font, $detail['checkup']);
        }

        # Image line
        imageline(
            $img, 60, $startY + 0.5 * $baseLine, $startX + 630,
            $startY + 0.5 * $baseLine, $color
        );
        imageline(
            $img, 60, $startY + 0.5 * $baseLine + 1 , $startX + 630,
            $startY + 0.5 * $baseLine + 1 , $color
        );

        # Waiting Period
        $startY += 2 * $baseLine;
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX, $startY,
            $color, $font, __('frontend.p_waiting_period')
        );
        imagettftext(
            $img, $fontSize, 0, $startX, $startY,
            $black, $font, $detail['waiting']
        );
        imagettftext(
            $img, $labelFontSize, 0, $labelStartX + 10 * $tab, $startY,
            $color, $font, __('frontend.p_l_exclusion')
        );
        if($detail['product'] == 'healthfirst'){
            imagettftext(
                $img, $fontSize, 0, $startX + 8 * $tab, $startY,
                $black, $font, $detail['exclusion']
            );

        } else {
            imagettftext(
                $img, $fontSize, 0, $startX + 11 * $tab, $startY,
                $black, $font, $detail['exclusion']
            );
        }

        ob_start();
        imagepng($img);
        $imagedata = ob_get_clean();
        $base64File = base64_encode($imagedata);

        $benSchedule['fore'] = $base64File;
        $benSchedule['back'] = base64_encode(file_get_contents($imgMackUrl));
        $qrcodeData = [
            'company' => 'pcv',
            'mbr_no' => $detail['mbr_no'],
            'dob' => $detail['dob']
        ];

        $qrcode = new QRCode();
        $benSchedule['qr_code'] =  $qrcode->render(json_encode($qrcodeData));

        return $benSchedule;
    }

    /**
     * 
     */
    private function getPlanTypeHF($planDesc){
        $planStr = '';
        if (strpos($planDesc, 'HF-PRM') !== false)
        {
            $planStr = __('frontend.s_hf_prm');
        }
        elseif (strpos($planDesc, 'HF-STD') !== false)
        {
            $planStr = __('frontend.s_hf_std');
        }
        elseif (strpos($planDesc, 'HF-EXE') !== false)
        {
            $planStr = __('frontend.s_hf_exe');
        }
        return $planStr;
    }

    /**
     * @throws
     */
    private function getOutPatientHF($planDesc)
    {
        $planTypes = explode(', ', $planDesc);
        if (count($planTypes) < 2)
        {
            return __('frontend.no');
        }
        if (strpos($planTypes[1], '(EXE') !== false)
        {
            return __('frontend.p_out_exe_hf');
        }
        if (strpos($planTypes[1], '(STD') !== false)
        {
            return __('frontend.p_out_std_hf');
        }
        if (strpos($planTypes[1], '(PRM') !== false)
        {
            return __('frontend.p_out_prm_hf');
        }
        return __('frontend.no');
    }

    /**
     * @throws
     */
    private function getDentalHF($planDesc)
    {
        $planTypes = explode(', ', $planDesc);
        foreach($planTypes as $key => $value)
        {
            if (strpos($value, 'DT (STD)') !== false)
            {
                return __('frontend.s_hf_dt_std');
            }
            elseif (strpos($value, 'DT (EXE)') !== false)
            {
                return __('frontend.s_hf_dt_exe');
            } 
            elseif (strpos($value, 'DT (PRM)') !== false)
            {
                return __('frontend.s_hf_dt_prm');
            }
        }
        return __('frontend.yes');
    }

    /**
     * @throws
     */
    private function getPlanTypeM($planDesc)
    {
        #get plan type Master
        $planStr = '';
        if (strpos($planDesc, 'M1') !== false)
        {
            if (strpos($planDesc, '5000M') !== false)
            {
                $planStr = 'M1+';
            }
            else
            {
                $planStr = 'M1';
            }
        }
        elseif (strpos($planDesc, 'M2') !== false)
        {
            $planStr = 'M2';
        }
        elseif (strpos($planDesc, 'M3') !== false)
        {
            $planStr = 'M3';
        }
        if (strpos($planDesc, 'TAL') !== false)
        {
            $planStr .=', TAL';
        }
        return $planStr;
    }

    /**
     * @throws
     */
    private function getDentalM($planDesc)
    {
        $planDesc = $this->strToHex($planDesc);
        $planDesc = str_replace('00', '', $planDesc);
        $planDesc = $this->hexToStr($planDesc);

        if (strpos($planDesc, 'LIFESTYLE BEN 1') !== false)
        {
            return self::MASTER_DENTAL_LS1;
        }
        elseif (strpos($planDesc, 'LIFESTYLE BEN 2') !== false)
        {
            return self::MASTER_DENTAL_LS2;
        }
        else
        {
            return 0;
        }
    }

    /**
     * input dd-MON-YYYY
     * out put dd- thang - YYYY
     */
    private function getCheckupM($planDesc)
    {
        $planDesc = $this->strToHex($planDesc);
        $planDesc = str_replace('00', '', $planDesc);
        $planDesc = $this->hexToStr($planDesc);

        if (strpos($planDesc, utf8_encode('LIFESTYLE BEN 1')) !== false)
        {
            return __('frontend.p_checkup1');
        }
        elseif (strpos($planDesc, 'LIFESTYLE BEN 2') !== false)
        {
            return __('frontend.p_checkup2');
        }
        else
        {
            return __('frontend.no');
        }

        return $planDesc;
    }

    /**
     * @throws
     */
    private function getPlanTypeF($planDesc)
    {
        #get plan type Foundation
        $planTypes = explode(', ', $planDesc);
        return $this->getPlanBenefitStr($planTypes[0]);
    }

    /**
     * @throws
     */
    private function getOutPatientF($planDesc)
    {
        $planTypes = explode(', ', $planDesc);
        if (count($planTypes) < 2)
        {
            return $planDesc;
        }
        if (strpos($planTypes[1], '(EXE') !== false)
        {
            return __('frontend.p_out_exe');
        }
        if (strpos($planTypes[1], '(STD') !== false)
        {
            return __('frontend.p_out_std');
        }
        if (strpos($planTypes[1], '(PRM') !== false)
        {
            return __('frontend.p_out_prm');
        }
        return __('frontend.no');
    }

    /**
     * @throws
     */
    private function getDentalF($planDesc)
    {
        $planTypes = explode(', ', $planDesc);
        foreach($planTypes as $key => $value)
        {
            if (strpos($value, 'DT1') !== false)
            {
                return __('frontend.plan_dt1');
            }
            elseif (strpos($value, 'DT2') !== false)
            {
                return __('frontend.plan_dt2');
            }
        }
        return __('frontend.yes');
    }

    /**
     * @throws
     */
    private function getMedicalCheckupF($planDesc)
    {
        $planTypes = explode(', ', $planDesc);
        if (strpos($planTypes[0], '(EXE') !== false)
        {
            return __('frontend.p_checkup_f_exe');
        }
        if (strpos($planTypes[0], '(STD') !== false)
        {
            return __('frontend.p_checkup_f_std');
        }
        if (strpos($planTypes[0], '(PRM') !== false)
        {
            return __('frontend.p_checkup_f_prm');
        }
    }

    /**
     * @throws
     */
    private function strToHex($string)
    {
        $hex = '';
        for ($i = 0, $c = strlen($string); $i < $c; $i++)
        {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    /**
     * @throws
     */
    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex)-1; $i += 2)
        {
            $string .= chr(hexdec($hex[$i] . $hex[$i+1]));
        }
        return $string;
    }

    /**
     * @throws
     */
    private function getPlanBenefitStr($benCode)
    {
        if (strpos($benCode, '(EXE') !== false)
        {
            return __('frontend.plan_exe');
        }
        if (strpos($benCode, '(STD') !== false)
        {
            return __('frontend.plan_std');
        }
        if (strpos($benCode, '(PRM') !== false)
        {
            return __('frontend.plan_prm');
        }
    }
}
