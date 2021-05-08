<?php

namespace Lza\App\Client\Modules;


use Exception;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Runtime\BaseController;
use Lza\LazyAdmin\Utility\Tool\HttpRequestHandler;

/**
 * Base Controller for Front End
 *
 * @var httpRequestHandler
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientController extends BaseController
{
    /**
     * Create Token when user request reset password to database
     *
     * @throws
     */
    public function createRequestPasswordToken($callback, $user)
    {
        $model = ModelPool::getModel('UserResetPassword');
        $model->where('email', $user['email'])->delete();

        $token = md5($user['username'] . date('Y-m-d-H-i-s'));
        $result = $model->insert([
            'email' => $user['email'],
            'token' => $token,
            'expire' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime(date('Y-m-d H:i:s'))))
        ]);

        if ($result)
        {
            $callback->onSuccess([
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'token' => $token
            ]);
        }
        else
        {
            $callback->onError('Failed to create token string!');
        }
    }

    /**
     * Update new user password to database
     *
     * @throws
     */
    public function changePassword($callback, $user, $password)
    {
        $result = $user->update([
            "password" => $this->encryptor->hash($password, 2),
            'expiry' => date('Y-m-d H:i:s', strtotime('+ ' . PASSWORD_EXPIRED_PERIOD))
        ]);

        if ($result)
        {
            $callback->onSuccess($user);
        }
        else
        {
            $callback->onError('Invalid Password!');
        }
    }

    /**
     * Updae password to database as reset requested
     *
     * @throws
     */
    public function resetPassword($callback, $user, $password = null)
    {
        $result = $user->update([
            "password" => $this->encryptor->hash($password, 2),
            'expiry' => date('Y-m-d H:i:s', strtotime('+ ' . PASSWORD_EXPIRED_PERIOD))
        ]);

        if ($result !== false)
        {
            $model = ModelPool::getModel('UserResetPassword');
            $model->where('email', $user['email'])->delete();
            $callback->onSuccess($user);
        }
        else
        {
            $callback->onError('Invalid Password!');
        }
    }

    /**
     * Delete temporary Direct Billing requests of Cathay in database
     *
     * @throws
     */
    public function deleteTempCathayGops($email)
    {
        $model = ModelPool::getModel('CathayDbClaim');
        $data = $model->where([
            'cathay_history.email' => $email,
            'cathay_db_claim.status' => 'Pending'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }

        $model = ModelPool::getModel('CathayHistory');
        $data = $model->where([
            'email' => $email,
            'pocy_no' => 'temp'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }
    }

    /**
     * Insert Direct Billing requests of Cathay to history in database
     *
     * @throws
     */
    public function writeCathayLog(
        $email, $ipAddress, $time, $mbrNo, $dob, $provId, $diagnosis, $note, $callTime,
        $telNo, $benefits, $incurDate, $pocyNo = null, $member = null, $status = null
    )
    {
        if (isset($member) && $member !== false)
        {
            foreach ($member as $key => $value)
            {
                if (is_int($key))
                {
                    unset($member[$key]);
                }
            }
        }

        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('CathayHistory');
            $item = [
                'email' => $email,
                'ip_address' => $ipAddress,
                'time' => $time,
                'pocy_no' => $pocyNo,
                'mbr_no' => $mbrNo,
                'dob' => $dob->format('Y-m-d'),
                'incur_date' => $incurDate->format('Y-m-d'),
                'provider_id' => $provId,
                'diagnosis' => $diagnosis,
                'note' => $note,
                'call_time' => $callTime,
                'tel_no' => $telNo,
                'result' => $this->encryptor->jsonEncode($member)
            ];
            $result = $model->insert($item);
            $id = $result['id'];

            $model = ModelPool::getModel('CathayDbClaim');
            foreach ($benefits as $dbRefNo => $benefit)
            {
                $dbRefNo = $mbrNo . $dbRefNo;
                $item = [
                    'db_ref_no' => $dbRefNo,
                    'cathay_history_id' => $result['id'],
                    'cathay_head_id' => $benefit['ben_head'],
                    'pres_amt' => $benefit['pres_amt'],
                    'app_amt' => $benefit['app_amt'],
                    'status' => $status
                ];
                $result2 = $model->insert($item);
            }
            $this->sql->commit();
        }
        catch (Exception $e)
        {
            $this->sql->rollback();
            throw $e;
        }

        return $id;
    }

    /**
     * Send newly created Direct Billing requests to Cathay Etalk
     *
     * @throws
     */
    public function sendToCathayEtalk(
        $id, $email, $mbrNo, $provId, $diagnosis, $note, $callTime, $telNo, $benefits, $incurDate, $status
    )
    {
        $model = ModelPool::getModel('CathayHead');
        $claims = [];
        foreach ($benefits as $dbRefNo => $benefit)
        {
            $dbRefNo = $mbrNo . $dbRefNo;
            $dbRefNos[] = $dbRefNo;

            $heads = $model->where('cathay_head.id', $benefit['ben_head']);
            $heads = $heads->select('
                cathay_benefit.ben_type,
                cathay_benefit.ben_desc
            ');
            $head = $heads->fetch();

            $claims[] = [
                'db_ref_no' => $dbRefNo,
                'ben_type' => $head['ben_type'],
                'ben_desc' => $head['ben_desc'],
                'pres_amt' => intval($benefit['pres_amt']),
                'app_amt' => intval($benefit['app_amt']),
                'status' => $status
            ];
        }

        $model = ModelPool::getModel('Provider');
        $provider = $model->where('id', $provId)->fetch();

        $headers = [
            'Authorization' => CATHAY_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];

        $data = [
            'email' => $email,
            'mbr_no' => $mbrNo,
            'mbr_name' => $this->session->search['mbr_name'],
            'provider' => $provider['name'],
            'diagnosis' => $diagnosis,
            'visit_date' => $incurDate->format('Y-m-d'),
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'claims' => $claims
        ];

        $extra = [
            'id' => $id
        ];

        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => CATHAY_ETALK_API_URL,
            'url' => CATHAY_ETALK_API_URL . '/direct-billing/add',
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data,
            'callback' => self::class . '::onSentToCathayEtalk',
            'extra' => $extra
        ]);
    }

    /**
     * Event when Direct Billing requests is sent to Cathay Etalk
     *
     * @throws
     */
    public static function onSentToCathayEtalk($status, $mantisId, $extra)
    {
        $model = ModelPool::getModel('CathayHistory');
        return false !== $model->select('id', $extra['id'])->update([
            'upd_by' => 'system',
            'mantis_id' => $mantisId
        ]);
    }

    /**
     * Call API to Cathay Etalk to update Direct Billing requests
     *
     * @throws
     */
    public function updateToCathayEtalk($email, $dbRefNo, $note, $callTime, $telNo, $presAmt, $appAmt, $status)
    {
        $headers = [
            'Authorization' => CATHAY_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];
        $data = [
            'email' => $email,
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'pres_amt' => intval($presAmt),
            'app_amt' => intval($appAmt),
            'status' => $status
        ];
        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => CATHAY_ETALK_API_URL,
            'url' => CATHAY_ETALK_API_URL . '/direct-billing/update/' . $dbRefNo,
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data
        ]);
    }

    /**
     * Delete temporary Direct Billing requests of Fubon in database
     *
     * @throws
     */
    public function deleteTempFubonGops($email)
    {
        $model = ModelPool::getModel('FubonDbClaim');
        $data = $model->where([
            'fubon_history.email' => $email,
            'fubon_db_claim.status' => 'Pending'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }

        $model = ModelPool::getModel('FubonHistory');
        $data = $model->where([
            'email' => $email,
            'pocy_no' => 'temp'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }
    }

    /**
     * Insert Direct Billing requests of Fubon to history in database
     *
     * @throws
     */
    public function writeFubonLog(
        $email, $ipAddress, $time, $mbrNo, $dob, $provId, $diagnosis, $note, $callTime,
        $telNo, $benefits, $incurDate, $pocyNo = null, $member = null, $status = null
    )
    {
        if (isset($member) && $member !== false)
        {
            foreach ($member as $key => $value)
            {
                if (is_int($key))
                {
                    unset($member[$key]);
                }
            }
        }

        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('FubonHistory');
            $item = [
                'email' => $email,
                'ip_address' => $ipAddress,
                'time' => $time,
                'pocy_no' => $pocyNo,
                'mbr_no' => $mbrNo,
                'dob' => $dob->format('Y-m-d'),
                'incur_date' => $incurDate->format('Y-m-d'),
                'provider_id' => $provId,
                'diagnosis' => $diagnosis,
                'note' => $note,
                'call_time' => $callTime,
                'tel_no' => $telNo,
                'result' => $this->encryptor->jsonEncode($member)
            ];
            $result = $model->insert($item);
            $id = $result['id'];

            $model = ModelPool::getModel('FubonDbClaim');
            foreach ($benefits as $dbRefNo => $benefit)
            {
                $dbRefNo = $mbrNo . $dbRefNo;
                $item = [
                    'db_ref_no' => $dbRefNo,
                    'fubon_history_id' => $result['id'],
                    'fubon_head_id' => $benefit['ben_head'],
                    'pres_amt' => $benefit['pres_amt'],
                    'app_amt' => $benefit['app_amt'],
                    'status' => $status
                ];
                $result2 = $model->insert($item);
            }
            $this->sql->commit();
        }
        catch (Exception $e)
        {
            $this->sql->rollback();
            throw $e;
        }

        return $id;
    }

    /**
     * Send newly created Direct Billing requests to Fubon Etalk
     *
     * @throws
     */
    public function sendToFubonEtalk(
        $id, $email, $mbrNo, $provId, $diagnosis, $note, $callTime, $telNo, $benefits, $incurDate, $status
    )
    {
        $model = ModelPool::getModel('FubonHead');
        $claims = [];
        foreach ($benefits as $dbRefNo => $benefit)
        {
            $dbRefNo = $mbrNo . $dbRefNo;
            $dbRefNos[] = $dbRefNo;

            $heads = $model->where('fubon_head.id', $benefit['ben_head']);
            $heads = $heads->select('
                fubon_benefit.ben_type,
                fubon_benefit.ben_desc
            ');
            $head = $heads->fetch();

            $claims[] = [
                'db_ref_no' => $dbRefNo,
                'ben_type' => $head['ben_type'],
                'ben_desc' => $head['ben_desc'],
                'pres_amt' => intval($benefit['pres_amt']),
                'app_amt' => intval($benefit['app_amt']),
                'status' => $status
            ];
        }

        $model = ModelPool::getModel('Provider');
        $provider = $model->where('id', $provId)->fetch();

        $headers = [
            'Authorization' => FUBON_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];

        $data = [
            'email' => $email,
            'mbr_no' => $mbrNo,
            'mbr_name' => $this->session->search['mbr_name'],
            'provider' => $provider['name'],
            'diagnosis' => $diagnosis,
            'visit_date' => $incurDate->format('Y-m-d'),
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'claims' => $claims
        ];

        $extra = [
            'id' => $id
        ];

        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => FUBON_ETALK_API_URL,
            'url' => FUBON_ETALK_API_URL . '/direct-billing/add',
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data,
            'callback' => self::class . '::onSentToFubonEtalk',
            'extra' => $extra
        ]);
    }

    /**
     * Event when Direct Billing requests is sent to Fubon Etalk
     *
     * @throws
     */
    public static function onSentToFubonEtalk($status, $mantisId, $extra)
    {
        $model = ModelPool::getModel('FubonHistory');
        return false !== $model->select('id', $extra['id'])->update([
            'upd_by' => 'system',
            'mantis_id' => $mantisId
        ]);
    }

    /**
     * Call API to Fubon Etalk to update Direct Billing requests
     *
     * @throws
     */
    public function updateToFubonEtalk($email, $dbRefNo, $note, $callTime, $telNo, $presAmt, $appAmt, $status)
    {
        $headers = [
            'Authorization' => FUBON_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];
        $data = [
            'email' => $email,
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'pres_amt' => intval($presAmt),
            'app_amt' => intval($appAmt),
            'status' => $status
        ];
        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => FUBON_ETALK_API_URL,
            'url' => FUBON_ETALK_API_URL . '/direct-billing/update/' . $dbRefNo,
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data
        ]);
    }

    /**
     * Delete temporary Direct Billing requests of PCV in database
     *
     * @throws
     */
    public function deleteTempPcvGops($email)
    {
        $model = ModelPool::getModel('PcvDbClaim');
        $data = $model->where([
            'pcv_history.email' => $email,
            'pcv_db_claim.status' => 'Pending'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }

        $model = ModelPool::getModel('PcvHistory');
        $data = $model->where([
            'email' => $email,
            'pocy_no' => 'temp'
        ]);
        foreach ($data as $item)
        {
            $item->delete();
        }
    }

    /**
     * Insert Direct Billing requests of PCV to history in database
     *
     * @throws
     */
    public function writePcvLog(
        $email, $ipAddress, $time, $mbrNo, $dob, $provId, $diagnosis, $note, $callTime,
        $telNo, $benefits, $incurDate, $pocyNo = null, $member = null, $status = null
    )
    {
        if (isset($member) && $member !== false)
        {
            foreach ($member as $key => $value)
            {
                if (is_int($key))
                {
                    unset($member[$key]);
                }
            }
        }

        try
        {
            $this->sql->start();
            $model = ModelPool::getModel('PcvHistory');
            $item = [
                'email' => $email,
                'ip_address' => $ipAddress,
                'time' => $time,
                'pocy_no' => $pocyNo,
                'mbr_no' => $mbrNo,
                'dob' => $dob->format('Y-m-d'),
                'incur_date' => $incurDate->format('Y-m-d'),
                'provider_id' => $provId,
                'diagnosis' => $diagnosis,
                'note' => $note,
                'call_time' => $callTime,
                'tel_no' => $telNo,
                'result' => $this->encryptor->jsonEncode($member)
            ];
            $result = $model->insert($item);
            $id = $result['id'];

            $model = ModelPool::getModel('PcvDbClaim');
            foreach ($benefits as $dbRefNo => $benefit)
            {
                $dbRefNo = $mbrNo . $dbRefNo;
                $item = [
                    'db_ref_no' => $dbRefNo,
                    'pcv_history_id' => $result['id'],
                    'pcv_head_id' => $benefit['ben_head'],
                    'pres_amt' => $benefit['pres_amt'],
                    'app_amt' => $benefit['app_amt'],
                    'status' => $status
                ];
                $result2 = $model->insert($item);
            }
            $this->sql->commit();
        }
        catch (Exception $e)
        {
            $this->sql->rollback();
            throw $e;
        }

        return $id;
    }

    /**
     * Send newly created Direct Billing requests to PCV Etalk
     *
     * @throws
     */
    public function sendToPcvEtalk(
        $id, $email, $mbrNo, $provId, $diagnosis, $note, $callTime, $telNo, $benefits, $incurDate, $status
    )
    {
        $model = ModelPool::getModel('PcvHead');
        $claims = [];
        foreach ($benefits as $dbRefNo => $benefit)
        {
            $dbRefNo = $mbrNo . $dbRefNo;
            $dbRefNos[] = $dbRefNo;

            $heads = $model->where('pcv_head.id', $benefit['ben_head']);
            $heads = $heads->select('
                pcv_benefit.ben_type,
                pcv_benefit.ben_desc
            ');
            $head = $heads->fetch();

            $claims[] = [
                'db_ref_no' => $dbRefNo,
                'ben_type' => $head['ben_type'],
                'ben_desc' => $head['ben_desc'],
                'pres_amt' => intval($benefit['pres_amt']),
                'app_amt' => intval($benefit['app_amt']),
                'status' => $status
            ];
        }

        $model = ModelPool::getModel('Provider');
        $provider = $model->where('id', $provId)->fetch();

        $headers = [
            'Authorization' => PCV_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];

        $data = [
            'email' => $email,
            'mbr_no' => $mbrNo,
            'mbr_name' => $this->session->search['mbr_name'],
            'provider' => $provider['name'],
            'diagnosis' => $diagnosis,
            'visit_date' => $incurDate->format('Y-m-d'),
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'claims' => $claims
        ];

        $extra = [
            'id' => $id
        ];

        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => PCV_ETALK_API_URL,
            'url' => PCV_ETALK_API_URL . '/direct-billing/add',
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data,
            'callback' => self::class . '::onSentToPcvEtalk',
            'extra' => $extra
        ]);
    }

    /**
     * Event when Direct Billing requests is sent to PCV Etalk
     *
     * @throws
     */
    public static function onSentToPcvEtalk($status, $mantisId, $extra)
    {
        $model = ModelPool::getModel('PcvHistory');
        return false !== $model->select('id', $extra['id'])->update([
            'upd_by' => 'system',
            'mantis_id' => $mantisId
        ]);
    }

    /**
     * Call API to PCV Etalk to update Direct Billing requests
     *
     * @throws
     */
    public function updateToPcvEtalk($email, $dbRefNo, $note, $callTime, $telNo, $presAmt, $appAmt, $status)
    {
        $headers = [
            'Authorization' => PCV_ETALK_API_TOKEN,
            'Content-Type' => 'application/json'
        ];
        $data = [
            'email' => $email,
            'note' => $note,
            'call_time' => $callTime,
            'tel_no' => $telNo,
            'pres_amt' => intval($presAmt),
            'app_amt' => intval($appAmt),
            'status' => $status
        ];
        return $this->httpRequestHandler->request([
            'user' => $this->session->get('user.username'),
            'base_url' => PCV_ETALK_API_URL,
            'url' => PCV_ETALK_API_URL . '/direct-billing/update/' . $dbRefNo,
            'method' => HttpRequestHandler::METHOD_POST,
            'headers' => $headers,
            'data' => $data
        ]);
    }
}
