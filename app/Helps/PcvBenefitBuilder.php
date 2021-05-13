<?php

namespace App\Helps;


use Exception;
use Illuminate\Support\Facades\DB;
use App\Helps\PcvBenefitBuilder;
use App\Models\HBS_PCV_MR_MEMBER;
use App\Models\HBS_PCV_MR_MEMBER_PLAN;
use App\Models\HBS_PCV_MR_MEMBER_PLAN_RESTRICTION;
/**
 * Convert Data To Benefit
 *
 * 
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class PcvBenefitBuilder
{
    private $data;

    /**
     * @throws
     */
    public function __construct($member, $lang)
    {
        $benSchedule = json_decode($member['ben_schedule'], true);
        $meplOid = $member['mepl_oid'];
        $url_file = resource_path('sql/pcv_benefit_temp.sql');
        $sql =  file_get_contents($url_file);
        
        if ($lang === 'vi')
        {
            $benTemp = null;
            $params = [
                $benSchedule['detail']['copay'],
                number_format($benSchedule['detail']['amtperday'], 0, '.', ',') . ' đ',
                number_format($benSchedule['detail']['amtpervis']) . ' đ',
                $benSchedule['detail']['copay'],
                number_format($benSchedule['detail']['amtperday'], 0, '.', ',').' đ',
                number_format($benSchedule['detail']['amtpervis']) . ' đ',
                $benSchedule['tmp']['TEMP_ID2']
            ];
            
            $benTempVi = DB::connection('hbs_pcv')->select($sql, $params);
            $benTempVi = json_decode(json_encode($benTempVi), true);
            
            
            if (count($benTempVi))
            {
                $benTempVi = $this->benefitMaternity($benTempVi, $lang);
                $benTempVi[count($benTempVi) - 1] = $this->benTempLast($benTempVi, $lang);
                $benSchedule['vi'] = $benTempVi;
            }
            else
            {
                $params = [
                    $benSchedule['detail']['copay'],
                    number_format($benSchedule['detail']['amtperday'], 0, '.', ',') . ' đ',
                    number_format($benSchedule['detail']['amtpervis']) . ' đ',
                    $benSchedule['detail']['copay'],
                    number_format($benSchedule['detail']['amtperday'], 0, '.', ',') . ' đ',
                    number_format($benSchedule['detail']['amtpervis']) . ' đ',
                    $benSchedule['tmp']['TEMP_ID1']
                ];
                $benTemp =  DB::connection('hbs_pcv')->select($sql, $params);
                $benTemp = json_decode(json_encode($benTemp), true);
                $benTemp = $this->benefitMaternity($benTemp, $lang);
                $benTemp[count($benTemp) - 1] = $this->benTempLast($benTemp, $lang);
                $benSchedule['vi'] = $benTemp;
            }
        }
        else
        {
            $benTempVi = null;
            $params = [
                $benSchedule['detail']['copay'],
                'VND '. number_format($benSchedule['detail']['amtperday'], 0, '.', ','),
                'VND '. number_format($benSchedule['detail']['amtpervis']),
                $benSchedule['detail']['copay'],
                'VND '. number_format($benSchedule['detail']['amtperday'], 0, '.', ','),
                'VND '. number_format($benSchedule['detail']['amtpervis']),
                $benSchedule['tmp']['TEMP_ID1']
            ];
            $benTemp =  DB::connection('hbs_pcv')->select($sql, $params);
           
            $benTemp = json_decode(json_encode($benTemp), true);
            $benTemp = $this->benefitMaternity($benTemp, $lang);
            $benTemp[count($benTemp) - 1] = $this->benTempLast($benTemp, $lang);
            $benSchedule['en'] = $benTemp;
           
        }

        if (empty($benSchedule[$lang]))
        {
            $this->data = null;
            return;
        }
        
        $count = count($benSchedule[$lang]);
        if (!empty($benSchedule['tmp']['TAL']))
        {
            for ($i = 1; $i < $count; $i++)
            {
                unset($benSchedule[$lang][$i]['AREA_COVER']);
            }
        }
        else
        {
            for ($i = 0; $i < $count; $i++)
            {
                unset($benSchedule[$lang][$i]['AREA_COVER']);
            }
        }

        $headD = [];
        $head = [];
        $title = 'heading';
        $titleH = $title;
        $i = 0;

        foreach ($benSchedule[$lang] as $key => $row)
        {
            if ( (!empty($row['heading']) && strpos($row['heading'], '** ') !== false))
            {
                $title = $this->convertBenhead($row['heading'], $lang);
                if (!empty($head))
                {
                    $headD[$titleH] = $head;
                }
                $head = [];
                $head[] = $row;
            }
            else
            {
                $titleH = $title;
                $head[] = $row;
            }
            $i++;
        }
        if (!empty($head))
        {
            $headD[$titleH] = $head;
            unset($head);
        }

        $benSchedule = $this->finalTmp($benSchedule['tmp'], $headD, $meplOid, $lang);

        $res = $this->getMemberPlanRestriction($meplOid, $lang);

        if (!empty($res))
        {
            $benSchedule['Restriction'] = $res;
        }

        $this->data = $benSchedule;
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
    private function benTempLast($benefits = null, $lang = 'en')
    {
        $dtSplit = '--------------------------------';
        $lmSplit = '----------------------';

        if ($lang == 'vi')
        {
            $dtSplit = '-----------';
            $lmSplit = '----------------------';
        }
        
        $benSchedule = end($benefits);
        
        if (!is_array($benSchedule))
        {
            throw new Exception('Not an array');
        }


        $detail = explode($dtSplit, trim($benSchedule['detail']));
        $detail2 = explode("\r\n", $detail[count($detail)-1]);
        if(strpos($benSchedule['detail'], $dtSplit)){
            $detail2[0] = $detail[0];
        }
        $detail = $detail2;

        $limit = explode($lmSplit, trim($benSchedule['limit']));
        $limit2 = explode("\r\n", $limit[count($limit) - 1]);

        if(strpos($benSchedule['limit'], $dtSplit)){
            $limit2[0] = $limit[0];
        }
        $limit = $limit2;
        $combine = null;
        if (count($detail) == count($limit))
        {
            $combine = array_combine($detail, $limit);
        }
        $benSchedule['detail'] = $combine;
        $benSchedule['limit'] = null;
        return $benSchedule;
    }

    /**
     * @throws
     */
    public function benefitMaternity($benefits, $lang='en'){
        if($lang == 'en'){
            $search_str = 'Maternity Benefit';
        } else {
            $search_str = 'Quyền lợi thai sản ';
        }
        $maternity_id = array_search($search_str, array_column($benefits, 'heading'));

        if(!$maternity_id){
            return $benefits;
        }
        $maternity = $benefits[$maternity_id];
        $details = explode("\r\n", $maternity['detail']);
        $limits = explode("\r\n", $maternity['limit']);

        unset($limits[0], $limits[1]);
        do {
            $limits[] = "";
        } while (count($limits) < count($details));

        $combine = null;
        if(count($details) == count($limits)){
            $combine = array_combine($details, $limits);
        }
        if(!is_null($combine)){
            $benefits[$maternity_id]['detail'] = $combine;
            $benefits[$maternity_id]['limit'] = null;
        }
        return $benefits;
    }

    /**
     * @throws
     */
    private function convertBenhead($strBen = null, $lang = 'en')
    {
        $str = '';
        switch (trim($strBen, '** '))
        {
            case 'QUYỀN LỢI NỘI TRÚ':
                $str = 'HAS_IP';
                break;
            case 'INPATIENT BENEFITS':
                $str = 'HAS_IP';
                break;
            case strpos(trim($strBen, '** '),'MAIN BENEFITS') !== false:
                $str = 'HAS_IP';
                break;
            case strpos(trim($strBen, '** '),'QUYỀN LỢI CHÍNH') !== false:
                $str = 'HAS_IP';
                break;
            case 'QUYỀN LỢI Y TẾ KHẨN CẤP':
                $str = 'HAS_ER';
                break;
            case 'EMERGENCY BENEFITS':
                $str = 'HAS_ER';
                break;
            case 'QUYỀN LỢI NGOẠI TRÚ':
                $str = 'HAS_OP';
                break;
            case strpos(trim($strBen, '** '),'QUYỀN LỢI NGOẠI TRÚ') !== false:
                $str = 'HAS_OP';
                break;
            case 'OUTPATIENT BENEFITS (STANDARD PLAN)':
                $str = 'HAS_OP';
                break;
            case strpos(trim($strBen, '** '),'OUTPATIENT BENEFITS') !== false:
                $str = 'HAS_OP';
                break;
            case 'OUTPATIENT BENEFITS':
                $str = 'HAS_OP';
                break;
            case strpos(trim($strBen, '** '),'ADDITIONAL MEDICAL BENEFIT') !== false:
                $str = 'HAS_OP';
                break;
            case strpos(trim($strBen, '** '),'QUYỀN LỢI Y TẾ BỔ SUNG') !== false:
                $str = 'HAS_OP';
                break;
            case 'QUYỀN LỢI DU LỊCH':
                $str = 'HAS_TV';
                break;
            case 'TRAVEL BENEFITS':
                $str = 'HAS_TV';
                break;
            case 'QUYỀN LỢI TAI NẠN CÁ NHÂN':
                $str = 'HAS_PA';
                break;
            case 'PERSONAL ACCIDENT BENEFITS':
                $str = 'HAS_PA';
                break;
            case strpos(trim($strBen, '** '),'PERSONAL ACCIDENT BENEFIT') !== false:
                $str = 'HAS_PA';
                break;
            case strpos(trim($strBen, '** '),'DENTAL BENEFIT') !== false:
                $str = 'HAS_DT';
                break;
            case strpos(trim($strBen, '** '),'QUYỀN LỢI NHA KHOA') !== false:
                $str = 'HAS_DT';
                break;
            default:
                $str = '';
                break;
        }
        return $str;
    }

    /**
     * @throws
     */
    private function finalTmp($benTmp, $benDetail, $meplOid, $lang = 'en')
    {
        foreach ($benTmp as $key => $value)
        {
            if ($value == "N")
            {
                unset($benDetail[$key]);
            }
            if ($key == 'HAS_PA' && $value == 'Y' && empty($benDetail[$key]))
            {
                $benDetail['HAS_PA'][] = $this->getMemberPlanPa($meplOid, $lang);
            }
        }
        return $benDetail;
    }

    /**
     * @throws
     */
    private function getMemberPlanRestriction($meplOid, $lang = 'en')
    {
        $mers = HBS_PCV_MR_MEMBER_PLAN_RESTRICTION::select('rstr_desc','rstr_desc_vn')
        ->where('mepl_oid', $meplOid)
        ->where('scma_oid_restriction_code','RESTRICTION_EXCL')->orderBy('mers_oid')->get()->toArray();
        

        $keyLang = 'rstr_desc';
        $result = [];
        $noLang = 0;

        if ($lang == 'vi')
        {
            $keyLang = 'rstr_desc_vn';
            $result[]['heading'] ="Loại trừ/ ghi chú";
        }
        else
        {
            $result[]['heading'] ='RESTRICTION/ NOTE';
        }
        foreach ($mers as $key => $value)
        {
            if (!$noLang && isset($value[$keyLang]))
            {
                $noLang = 1;
            }
            $result[]['detail'] = $value[$keyLang];
        }

        if (!$noLang)
        {
            return [];
        }

        return $result;
    }

    /**
     * @throws
     */
    private function getMemberPlanPa($meplOid, $lang = 'en')
    {
        $benSchedule = [];
        $benPa = HBS_PCV_MR_MEMBER_PLAN::select('sum_insured')->where('mepl_oid',$meplOid)->get()->toArray();
        $benSchedule['heading'] = "** PERSONAL ACCIDENT BENEFITS **";
        $benSchedule['detail'] = null;
        $benSchedule['limit'] = $this->currencyFormat($benPa[0]['sum_insured'], $lang);

        if ($lang == 'vi')
        {
            $benSchedule['heading'] = "** QUYỀN LỢI TAI NẠN CÁ NHÂN **";
        }

        return $benSchedule;
    }

    /**
     * @throws
     */
    private function currencyFormat($money, $lang = 'en')
    {
        $str = 'VND '. number_format($money, 0, '.', ',');
        if ($lang == 'vi')
        {
            $str = number_format($money, 0, '.', ','). ' đ';
        }
        return $str;
    }
}