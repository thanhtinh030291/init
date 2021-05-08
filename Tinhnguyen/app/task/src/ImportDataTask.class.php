<?php

namespace Lza\App\Task;


use Exception;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseTask;
use Lza\LazyAdmin\Utility\Data\DatabasePool;
use Lza\LazyAdmin\Utility\Data\Setting;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\PHPMailHandler;

/**
 * Import Data from HBS
 *
 * @var session
 * @var sql
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ImportDataTask implements BaseTask
{
    const MEMBER_QUERY = "import_%s_member";
    const CLAIM_QUERY = "import_%s_claim";

    private $company = '';
    private $hasExtraCompanies = [
        'pcv'
    ];

    /**
     * @throws
     */
    public function __construct($company)
    {
        try
        {
            $this->mainDb = DatabasePool::getConnection();
            $this->company = $company;

            if (strtolower($company) === 'pcv')
            {
                $dbInfos = json_decode(DATABASES, true);
                $dbInfo = $dbInfos['hbs_pcv']['database_info'];
                $this->conn = oci_connect(
                    $dbInfo['user'],
                    $dbInfo['pass'],
                    $dbInfo['host'] . '/' . $dbInfo['name']
                );
                $this->cursor = oci_new_cursor($this->conn);
            }
        }
        catch (Exception $e)
        {
            $this->sendMail(
                $company . ' Card Validation Error',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->println(
                $company . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString()
            );
        }
    }

    /**
     * @throws
     */
    public function execute($echo = false)
    {
        try
        {
            $hbs = DatabasePool::getConnection('hbs_' . strtolower($this->company));
        }
        catch (Exception $e)
        {
            $this->sendMail(
                $this->company . ' Card Validation Error',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->println("Cannot connect to {$this->company} HBS!", $echo);
            return;
        }

        $memberModel = ModelPool::getModel('HbsMember', 'main');
        $member2Model = ModelPool::getModel('HbsMember2', 'main');
        $claimLineModel = ModelPool::getModel('HbsClaimLine', 'main');
        $claimLine2Model = ModelPool::getModel('HbsClaimLine2', 'main');

        $now = date('Y-m-d H:i:s');
        $file = sprintf(self::MEMBER_QUERY, strtolower($this->company));
        $memberQuery = $this->sql->$file;
        $this->println("Synchronize {$this->company} Members at {$now}...", $echo);

        $count = $this->getData(
            $hbs, $member2Model, $memberQuery, 'mbr_no', $echo, [$this, 'getExtra']
        );
        if ($count === false)
        {
            return;
        }

        $this->updateData($memberModel, $member2Model, $echo);
        $this->println("Synchronized {$count} {$this->company} Members...", $echo);

        $now = date('Y-m-d H:i:s');
        $file = sprintf(self::CLAIM_QUERY, strtolower($this->company));
        $claimLineQuery = $this->sql->$file;
        $this->println("Synchronize {$this->company} Claim Lines at {$now}...", $echo);

        $count = $this->getData($hbs, $claimLine2Model, $claimLineQuery, 'cl_no', $echo);
        if ($count === false)
        {
            return;
        }

        $this->updateData($claimLineModel, $claimLine2Model, $echo);
        $this->println("Synchronized {$count} {$this->company} Claim Lines...", $echo);

        $now = date('Y-m-d H:i:s');
        $this->mainDb->exec("
            update lzasetting
            set lzasetting.value = '{$now}'
            where lzasetting.id = 'update_time'
        ");
    }

    /**
     * @throws
     */
    protected function getData($hbs, $model, $sql, $displayField, $echo, $callBack = null)
    {
        $info = $model->getTable();
        $items = $this->getDataFromHbs($hbs, $sql, $echo);
        if ($items === false)
        {
            return false;
        }

        $benefits = [];

        $count = 0;
        $this->mainDb->exec("delete from {$info['id']} where company = '" . strtolower($this->company) . "'");

        $no = 0;
        while ($item = $items->fetch(\PDO::FETCH_ASSOC))
        {
            $count++;
            $item['company'] = strtolower($this->company);
            if ($callBack !== null)
            {
                $item = $callBack($item, $benefits);
            }

            $no++;
            if (!$this->importItem($no, $item, $item[$displayField], $model, $echo))
            {
                $this->sendMail('Card Validation Error', "Cannot synchronize to {$info['id']}!");
                $this->println("Card Validation Error: ", $echo);
                $this->println("Cannot synchronize to {$info['id']}!", $echo);
                return false;
            }

            if ($this->company === 'pcv')
            {
                $model_user = ModelPool::getModel('mobile_user');
                $user = $model_user->where(['mbr_no'=>$item['mbr_no']])->fetch();
                if (
                    $user != false &&
                    (
                        $user['tel'] != $item['tel'] ||
                        $user['email'] != $item['email'] ||
                        $user['address'] != $item['address'] ||
                        $user['is_policy_holder'] != $item['is_policy_holder']
                    )
                )
                {
                    $user->update([
                        'tel' => $item['tel'],
                        'email' => $item['email'],
                        'address' => $item['address'],
                        'is_policy_holder' => $item['is_policy_holder']
                    ]);
                }
            }
        }

        return $count;
    }

    /**
     * @throws
     */
    private function getDataFromHbs($db, $sql, $echo)
    {
        $items = $db->query($sql);
        if (!$items)
        {
            $this->sendMail($this->company . ' Card Validation Error', "No {$this->company} Data!");
            $this->println("Cannot connect to {$this->company} HBS!", $echo);
            return false;
        }
        return $items;
    }

    /**
     * @throws
     */
    private function importItem($no, $item, $name, $model, $echo)
    {
        $item['crt_by'] = 'taskrunner';

        $item2 = $item;
        unset($item2['plan_excls']);
        unset($item2['plan_excls_vi']);
        unset($item2['memb_rstr']);
        unset($item2['memb_rstr_vi']);
        unset($item2['mepl_incls']);
        unset($item2['mepl_incls_vi']);
        unset($item2['mepl_excls']);
        unset($item2['mepl_excls_vi']);

        $member = $model->where($item2)->fetch();
        if ($member !== false)
        {
            $changed = [];

            if (strpos($member['plan_excls'], $item['plan_excls']) !== false) {
                $changed['plan_excls'] = $member['plan_excls'] . ";;;" . $item['plan_excls'];
            }

            if (strpos($member['plan_excls_vi'], $item['plan_excls_vi']) !== false) {
                $changed['plan_excls_vi'] = $member['plan_excls_vi'] . ";;;" . $item['plan_excls_vi'];
            }

            if (strpos($member['memb_rstr'], $item['memb_rstr']) !== false) {
                $changed['memb_rstr'] = $member['memb_rstr'] . ";;;" . $item['memb_rstr'];
            }

            if (strpos($member['memb_rstr_vi'], $item['memb_rstr_vi']) !== false) {
                $changed['memb_rstr_vi'] = $member['memb_rstr_vi'] . ";;;" . $item['memb_rstr_vi'];
            }

            if (strpos($member['mepl_incls'], $item['mepl_incls']) !== false) {
                $changed['mepl_incls'] = $member['mepl_incls'] . ";;;" . $item['mepl_incls'];
            }

            if (strpos($member['mepl_incls_vi'], $item['mepl_incls_vi']) !== false) {
                $changed['mepl_incls_vi'] = $member['mepl_incls_vi'] . ";;;" . $item['mepl_incls_vi'];
            }

            if (strpos($member['mepl_excls'], $item['mepl_excls']) !== false) {
                $changed['mepl_excls'] = $member['mepl_excls'] . ";;;" . $item['mepl_excls'];
            }

            if (strpos($member['mepl_excls_vi'], $item['mepl_excls_vi']) !== false) {
                $changed['mepl_excls_vi'] = $member['mepl_excls_vi'] . ";;;" . $item['mepl_excls_vi'];
            }

            if (count($changed) > 0)
            {
                $result = $member->update($changed);
                if ($result)
                {
                    $this->println("{$no} - Update {$name} OK", $echo);
                    return true;
                }
                else
                {
                    $this->println("{$no} - Update {$name} Failed", $echo);
                    $this->println("Data: " . $this->encryptor->jsonEncode($changed), $echo);
                    return false;
                }
            }
            return true;
        }
        else
        {
            $result = $model->insert($item);
            if ($result)
            {
                $this->println("{$no} - Import {$name} OK", $echo);
                return true;
            }
            else
            {
                $this->println("{$no} - Import {$name} Failed", $echo);
                $this->println("Data: " . $this->encryptor->jsonEncode($item), $echo);
                return false;
            }
        }
    }

    /**
     * @throws
     */
    protected function updateData($model, $model2, $echo)
    {
        $info = $model->getTable();
        $info2 = $model2->getTable();

        $this->mainDb->exec("
            delete from {$info['id']}
            where company = '" . strtolower($this->company) . "'
        ");
        $this->mainDb->exec("
            insert into {$info['id']}
            select * from {$info2['id']}
            where company = '" . strtolower($this->company) . "'
        ");
    }

    /**
     * @throws
     */
    private function println($message, $echo = false)
    {
        if ($echo)
        {
            println($message);
        }
    }

    /**
     * @throws
     */
    private function sendMail($subject, $message)
    {
        $setting = Setting::getInstance();
        $email = $setting->email;
        PHPMailHandler::getInstance()->add(
            'system',
            $email,
            $email,
            SUPPORT_EMAIL,
            SUPPORT_EMAIL,
            $subject,
            $message
        );
    }

    private function getExtra($item, &$benefits)
    {
        if (!in_array(strtolower($this->company), $this->hasExtraCompanies))
        {
            return $item;
        }

        $sql = "BEGIN PKG_RP.sp_benefit_schedule(:mplOid, :cur); END;";

        $item['benefit_en'] = '';
        $item['benefit_vi'] = '';

        if (!isset($benefits[$item['mbr_no']]))
        {
            $benefits[$item['mbr_no']] = [];
        }

        $hasBenefit = false;
        if (!isset($benefits[$item['mbr_no']][$item['memb_eff_date']]))
        {
            $benefits[$item['mbr_no']][$item['memb_eff_date']] = [
                'benefit_en' => '',
                'benefit_vi' => ''
            ];
        }
        elseif ($benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_en'] !== '')
        {
            $item['benefit_en'] = $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_en'];
            $item['benefit_vi'] = $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_vi'];
            $hasBenefit = true;
        }

        if ($hasBenefit)
        {
            return $item;
        }

        try
        {
            $stid = oci_parse($this->conn, $sql);
            oci_bind_by_name($stid, ":mplOid", $item['mepl_oid']);
            oci_bind_by_name($stid, ":cur", $this->cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($this->cursor);
            $benSchedule = [];
            while (($row = oci_fetch_array($this->cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false)
            {
                $benSchedule["tmp"] = $row;
            }
        }
        catch (Exception $e)
        {
            return $item;
        }

        $item['ben_schedule'] = null;
        if (!empty($benSchedule))
        {
            $benDetails = $this->sql->query(
                $this->sql->pcvBenefitDetail,
                [$benSchedule['tmp']['PLAN_OID']],
                'hbs_pcv'
            );

            if (!empty($benDetails))
            {
                $benSchedule['detail'] = $benDetails[0];
            }
            else
            {
                $benSchedule['detail']['copay'] = null;
                $benSchedule['detail']['amtperday'] = null;
                $benSchedule['detail']['amtpervis'] = null;
            }

            $item['ben_schedule'] = json_encode($benSchedule);
        }

        $langs = ['en', 'vi'];
        if (!$hasBenefit && $item['ben_schedule'] !== null)
        {
            try
            {
                foreach ($langs as $lang)
                {
                    $this->session->lzalanguage = $lang === 'vi' ? '_vi' : '';
                    $builder = DIContainer::resolve(PcvBenefitBuilder::class, $item, $lang);
                    $benefit = $builder->get();
                    $item['benefit_' . $lang] = $benefit === null ? null : json_encode($benefit);
                    $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_' . $lang] = $item['benefit_' . $lang];
                }
            }
            catch (Exception $e)
            {
                // Ignore
            }
        }

        return $item;
    }
}
