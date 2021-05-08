<?php

namespace Lza\App\Client\Modules\Api\V10\Claim;


use Exception;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseController;
use Lza\LazyAdmin\Utility\Tool\HttpRequestHandler;

/**
 * Controller for Claim API
 *
 * @var httpRequestHandler
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimController extends BaseController
{
    const STATUS_INFO_REQUEST = 16;
    const STATUS_INFO_SUBMITTED_ID = 'c0b850b9-4ff7-11eb-ba33-000d3a821253';
    const STATUS_READY_FOR_PROCESS = 18;

    /**
     * Add OTP Request to database
     *
     * @throws
     */
    public function addOneTimePasswordRequest($callback, $member)
    {
        try
        {
            $this->sql->start();

            $otp = rand(100000, 999999);
            $expire = date('Y-m-d H:i:s', strtotime('+3 minutes'));

            $model = ModelPool::getModel('MobileClaimOtp');
            $model->insert([
                'mbr_no' => $member['mbr_no'],
                'otp' => $otp,
                'expire' => $expire
            ]);

            $callback->onSuccess([
                'tel' => $member['tel'],
                'otp' => $otp,
                'expire' => $expire
            ]);

            $this->sql->commit();
            return true;
        }
        catch(Exception $e)
        {
            $this->sql->rollback();
            return false;
        }
    }

    /**
     * Add Claim to database
     *
     * @throws
     */
    public function addClaim(
        $callback, $member, $bankAccount, $note, $docs, $payType, $presAmt, $reason,
        $symtomTime, $occurTime, $bodyPart, $detail, $dependentMbrNo = null, $fullname = null
    )
    {
        try
        {
            $this->sql->start();

            $files = [];
            foreach ($docs as $doc)
            {
                $files[] = [
                    'name' => $doc['filename'],
                    'content' => $doc['contents']
                ];
            }

            $model = ModelPool::getModel('MobileClaim');

            $claim = $model->insert([
                'crt_by' => $member['mbr_no'],
                'mantis_id' => 0,
                'mobile_user_id' => $member['id'],
                'pay_type' => $payType,
                'pres_amt' => $presAmt,
                'mobile_user_bank_account_id' => $bankAccount['id'],
                'reason' => $reason,
                'symtom_time' => $symtomTime,
                'occur_time' => $occurTime,
                'body_part' => $bodyPart,
                'incident_detail' => $detail,
                'note' => $note,
                'dependent_memb_no' => $dependentMbrNo,
                'fullname' => $fullname
            ]);

            if ($claim === false)
            {
                return false;
            }

            $model = ModelPool::getModel('MobileClaimFile');
            foreach ($docs as $doc)
            {
                $model->insert([
                    'crt_by' => $member['mbr_no'],
                    'mobile_claim_id' => $claim['id'],
                    'filename' => $doc['filename'],
                    'filetype' => $doc['filetype'],
                    'filesize' => $doc['filesize'],
                    'checksum' => md5($doc['contents']),
                    'contents' => base64_decode($doc['contents'], true)
                ]);
            }

            $headers = [
                'Authorization' => PCV_ETALK_API_TOKEN,
                'Content-Type' => 'application/json'
            ];

            if(empty($note)){
                $note = ' ';
            }

            $data = [
                'pocy_no' => $member['pocy_no'],
                'mbr_no' => $dependentMbrNo ?? $member['mbr_no'],
                'fullname' => $fullname ?? $member['fullname'],
                'note' => $note,
                'files' => $files,
                'pay_type' => $payType,
                'pres_amt' => $presAmt,
                'bank_name' => $bankAccount['bank_name'],
                'bank_address' => $bankAccount['bank_address'],
                'bank_acc_no' => $bankAccount['bank_acc_no'],
                'bank_acc_name' => $bankAccount['bank_acc_name'],
                'reason' => $reason,
                'symtom_time' => $symtomTime,
                'occur_time' => $occurTime,
                'body_part' => $bodyPart,
                'incident_detail' => $detail,
            ];

            $extra = [
                'id' => $claim['id']
            ];

            $result = $this->httpRequestHandler->request([
                'user' => $member['mbr_no'],
                'base_url' => PCV_ETALK_API_URL,
                'url' => PCV_ETALK_API_URL . '/mobile-claim/add',
                'method' => HttpRequestHandler::METHOD_POST,
                'headers' => $headers,
                'data' => $data,
                'callback' => self::class . '::onClaimAddedToMantis',
                'extra' => $extra
            ]);

            if (!$result)
            {
                $this->sql->rollback();
                return false;
            }

            $this->sql->commit();
            return true;
        }
        catch(Exception $e)
        {
            $this->sql->rollback();
            return false;
        }
    }

    /**
     * Event when Claim is sent to PCV Etalk
     *
     * @throws
     */
    public static function onClaimAddedToMantis($status, $json, $extra)
    {
        $issue = json_decode($json, true);
        $model = ModelPool::getModel('MobileClaim');
        return false !== $model->where('id', $extra['id'])->update([
            'upd_by' => 'system',
            'mantis_id' => $issue['id'],
            'extra' => $json
        ]);
    }

    /**
     * Update Claim Status to database
     *
     * @throws
     */
    public function updateClaimStatus($callback, $member, $claim, $status, $notes)
    {
        try
        {
            $this->sql->start();

            $issue = json_decode($claim['extra'], true);
            $issue['notes'] = $notes;
            $issue['status'] = [
                'id' => $status['id'],
                'code' => $status['code'],
                'name' => $status['label'],
                'name_en' => $status['name'],
                'name_vi' => $status['name_vi']
            ];
            $id = $claim->update([
                'mobile_claim_status_id' => $status['id'],
                'extra' => json_encode($issue)
            ]);
            if ($id === false)
            {
                return false;
            }

            if ($status['id'] === self::STATUS_READY_FOR_PROCESS)
            {
                $bankAccount = $claim->mobile_user_bank_account;
                $sql = "
                    SELECT
                        bank_name,
                        bank_address,
                        bank_acc_no,
                        bank_acc_name
                    FROM mantis_plugin_mobileclaim_detail_table
                    WHERE bug_id = ?
                ";

                $rows = $this->sql->query($sql, [$claim['mantis_id']], 'mantis_pcv');
                if (count($rows) == 0)
                {
                    $this->sql->commit();
                    return $callback->onClaimStatusUpdatedSuccess([
                        'username' => $member['mbr_no'],
                        'user_id' => $member['id'],
                        'claim_id' => $claim['id'],
                        'status' => $status['name' . $member['language']],
                        'status_id' => $status['code']
                    ]);
                }

                $row = $rows[0];
                if (
                    $bankAccount !== null && $bankAccount !== false &&
                    (
                        $row['bank_name'] !== $bankAccount['bank_name'] ||
                        $row['bank_address'] !== $bankAccount['bank_address'] ||
                        $row['bank_acc_no'] !== $bankAccount['bank_acc_no'] ||
                        $row['bank_acc_name'] !== $bankAccount['bank_acc_name']
                    )
                )
                {
                    $headers = [
                        'Authorization' => PCV_ETALK_API_TOKEN,
                        'Content-Type' => 'application/json'
                    ];

                    $data = [
                        'id' => $claim['mantis_id'],
                        'bank_name' => $bankAccount['bank_name'],
                        'bank_address' => $bankAccount['bank_address'],
                        'bank_acc_no' => $bankAccount['bank_acc_no'],
                        'bank_acc_name' => $bankAccount['bank_acc_name']
                    ];

                    $result = $this->httpRequestHandler->request([
                        'user' => $member['mbr_no'],
                        'base_url' => PCV_ETALK_API_URL,
                        'url' => PCV_ETALK_API_URL . '/mobile-claim/update-bank-account',
                        'method' => HttpRequestHandler::METHOD_POST,
                        'headers' => $headers,
                        'data' => $data
                    ]);

                    if (!$result)
                    {
                        $this->sql->rollback();
                        return false;
                    }
                }
            }

            $result = $callback->onClaimStatusUpdatedSuccess([
                'username' => $member['mbr_no'],
                'user_id' => $member['id'],
                'language' => $member['language'],
                'claim_id' => $claim['id'],
                'status' => $status['name' . $member['language']],
                'status_id' => $status['code']
            ]);

            $this->sql->commit();
            return $result;
        }
        catch (Exception $e)
        {
            $this->sql->rollback();
            return false;
        }
    }

    /**
     * Add Claim Note to database
     *
     * @throws
     */
    public function addClaimNote($callback, $member, $claim, $note, $docs)
    {
        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('MobileClaimFile');
            $filename = [];
            foreach ($docs as $doc)
            {
                $model->insert([
                    'crt_by' => $member['mbr_no'],
                    'mobile_claim_id' => $claim['id'],
                    'note' => $note,
                    'filename' => $doc['filename'],
                    'filetype' => $doc['filetype'],
                    'filesize' => $doc['filesize'],
                    'checksum' => md5($doc['contents']),
                    'contents' => base64_decode($doc['contents'], true)
                ]);
                $filename[]=$doc['filename'];
            }

            $model = ModelPool::getModel('MobileClaimStatus');
            $status = $model->where('id', self::STATUS_INFO_SUBMITTED_ID)->fetch();

            $issue = json_decode($claim['extra'], true);

            $issue['status'] = [
                'id' => $status['id'],
                'name' => $status['name'],
                'name_en' => $status['name'],
                'name_vi' => $status['name_vi']
            ];

            $issue['notes'][] = [
                'text' => $note,
                'date_submitted' => date('Y-m-d H:i:s'),
                'status' => $status,
                'filename' => implode('; ', $filename)
            ];

            $claim->update([
                'mobile_claim_status_id' => self::STATUS_INFO_SUBMITTED_ID,
                'extra' => json_encode($issue)
            ]);

            $headers = [
                'Authorization' => PCV_ETALK_API_TOKEN,
                'Content-Type' => 'application/json'
            ];

            $files = [];
            foreach ($docs as $doc)
            {
                $files[] = [
                    'name' => $doc['filename'],
                    'content' => $doc['contents']
                ];
            }

            $data = [
                'note' => $note,
                'files' => $files
            ];

            $extra = [
                'id' => $claim['id']
            ];

            $result = $this->httpRequestHandler->request([
                'user' => $member['mbr_no'],
                'base_url' => PCV_ETALK_API_URL,
                'url' => PCV_ETALK_API_URL . '/mobile-claim/add-note/' . $claim['mantis_id'],
                'method' => HttpRequestHandler::METHOD_POST,
                'headers' => $headers,
                'data' => $data,
                'callback' => self::class . '::onClaimNoteAddedToMantis',
                'extra' => $extra
            ]);

            if (!$result)
            {
                $this->sql->rollback();
                return false;
            }

            $callback->onClaimNoteAddedSuccess([
                'username' => $member['mbr_no'],
                'user_id' => $member['id'],
                'claim_id' => $claim['id']
            ]);

            $this->sql->commit();
            return true;
        }
        catch (Exception $e)
        {
            $this->sql->rollback();
            return false;
        }
    }

    /**
     * Event when Claim Note is sent to PCV Etalk
     *
     * @throws
     */
    public static function onClaimNoteAddedToMantis($status, $label, $extra)
    {
        $model = ModelPool::getModel('MobileClaim');
        $claim = $model->where('id', $extra['id'])->fetch();
        if ($claim === false)
        {
            return true;
        }

        $issue = json_decode($claim['extra'], true);
        $issue['status']['name'] = $label;

        return true;
    }
}
