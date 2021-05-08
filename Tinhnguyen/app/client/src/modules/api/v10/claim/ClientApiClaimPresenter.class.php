<?php

namespace Lza\App\Client\Modules\Api\V10\Claim;


use Lza\App\Client\Modules\Api\V10\Member\ClientApiMemberPresenter;
use Lza\Config\Models\ModelPool;

/**
 * Default Presenter for Claim API
 *
 * @var noteHandler
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimPresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and check if Document exists
     *
     * @throws
     */
    public function doesDocumentExist($filesize, $content)
    {
        $checksum = md5($content);
        $model = ModelPool::getModel('MobileClaimFile');
        $files = $model->where([
            'checksum' => $checksum,
            'filesize' => $filesize
        ]);
        return $files->select('id')->fetch() !== false;
    }

    /**
     * Validate inputs and check if Claim exists
     *
     * @throws
     */
    public function doesClaimExist($claimId, $memberId)
    {
        $model = ModelPool::getModel('MobileClaim');
        $claims = $model->where([
            'id' => $claimId,
            'mobile_user_id' => $memberId
        ]);
        return $claims->fetch();
    }

    /**
     * Validate inputs and check if Claim exists by Mantis ID
     *
     * @throws
     */
    public function doesClaimExistByIssueId($mantisId, $memberId)
    {
        $model = ModelPool::getModel('MobileClaim');
        $claims = $model->where([
            'mantis_id' => $mantisId,
            'mobile_user_id' => $memberId
        ]);
        return $claims->fetch();
    }

    /**
     * Validate inputs and check if Claim Status exists
     *
     * @throws
     */
    public function doesStatusExist($code)
    {
        $model = ModelPool::getModel('MobileClaimStatus');
        $statuses = $model->where('code', $code);
        return $statuses->fetch();
    }

    /**
     * @throws
     */
    protected function getClaimExtra($claim, $getFile = false)
    {
        $claim['pocy_no'] = $claim->mobile_user['pocy_no'];
        $claim['mbr_no'] = $claim->mobile_user['mbr_no'];

        $claim['status'] = $claim->mobile_claim_status['name' . $this->session->lzalanguage];
        $claim['status_code'] = $claim->mobile_claim_status['code'];

        if (!is_null($claim['dependent_memb_no']))
        {
            $claim['mbr_no'] = $claim['dependent_memb_no'];
        }
        else
        {
            $claim['fullname'] = $claim->mobile_user['fullname'];
        }

        $claim['bank_name'] = $claim->mobile_user_bank_account['bank_name'];
        $claim['bank_address'] = $claim->mobile_user_bank_account['bank_address'];
        $claim['bank_acc_no'] = $claim->mobile_user_bank_account['bank_acc_no'];
        $claim['bank_acc_name'] = $claim->mobile_user_bank_account['bank_acc_name'];

        $docs = [];
        foreach ($claim->mobile_claim_file() as $id => $file)
        {
            $file['contents'] = $getFile ? base64_encode($file['contents']) : '';
            $docs[] = $file;
        }
        $claim['docs'] = $docs;

        if ($claim['mantis_id'] != 0)
        {
            $claim['extra'] = !empty($claim['extra']) ? json_decode($claim['extra']) : null;
        }
        return $claim;
    }
}
