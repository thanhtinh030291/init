<?php

namespace Lza\App\Client\Modules\Api\V20\Claim;


use Lza\Config\Models\ModelPool;

/**
 * Handle Update Claim Status action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimPatchStatusPresenter extends ClientApiClaimPresenter
{
    const READY_FOR_PROCESS = 18;

    /**
     * Validate inputs and do Update Claim Status request
     *
     * @throws
     */
    public function doUpdateStatus($mantisId, $memberNo, $status, $label, $notes)
    {
        $member = $this->doesMemberExist($memberNo);
        if (!$member)
        {
            $member = $this->doesMemberExistByDependant($memberNo);
            if (!$member)
            {
                return 1; //
            }
        }

        $claim = $this->doesClaimExistByIssueId($mantisId, $member['id']);
        if (!$claim)
        {
            return 2;
        }

        $status = $this->doesStatusExist($status);
        if (!$status)
        {
            return 3;
        }
        $status['label'] = $label;

        foreach ($notes as &$note)
        {
            $note['status'] = $this->doesStatusExist($note['status']);
        }

        return $this->updateClaimStatus($this, $member, $claim, $status, $notes);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onClaimStatusUpdatedSuccess($data)
    {
        $model = ModelPool::getModel('MobileDevice');
        $devices = $model->where('mobile_user_id', $data['user_id']);
        if (count($devices) === 0)
        {
            return true;
        }
        $receivers = [];
        foreach ($devices as $device)
        {
            $receivers[] = $device['device_token'];
        }

        $this->session->lzalanguage = '';
        $title_en = $this->i18n->updateClaimStatusTitle;
        $note_en = $this->i18n->updateClaimStatusMessage;

        if ($data['status_id'] == self::READY_FOR_PROCESS)
        {
            $note_en .= '. ' . $this->i18n->requestRealFile;
        }

        $this->session->lzalanguage = '_vi';
        $title_vn = $this->i18n->updateClaimStatusTitle;
        $note_vn = $this->i18n->updateClaimStatusMessage;
        if ($data['status_id'] == self::READY_FOR_PROCESS)
        {
            $note_vn .= '. ' . $this->i18n->requestRealFile;
        }

        $title = $title_en."<br>". $title_vn;
        $note = $note_en."<br>".$note_vn;
        
        $data = [
            'claim_id' => $data['claim_id'],
            'status' => $data['status'],
            "body"=> $note,
            "title"=> $title,
            'status_id' => $data['status_id'] //
        ];

        return $this->noteHandler->add('system', 'device', $receivers, $title, $note, $data) !== false;
    }
}
