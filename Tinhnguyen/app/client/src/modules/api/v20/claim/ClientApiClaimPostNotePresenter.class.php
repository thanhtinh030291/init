<?php

namespace Lza\App\Client\Modules\Api\V20\Claim;


use Lza\App\Client\Modules\Api\V20\Claim\ClientApiClaimPresenter;
use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\PdfBuilder;

/**
 * Handle Add Claim Note action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimPostNotePresenter extends ClientApiClaimPresenter
{
    /**
     * Validate inputs and do Add New Claim Note request
     *
     * @throws
     */
    public function doAddClaimNote($claimId, $memberNo, $note, $docs)
    {
        $member = $this->doesMemberExist($memberNo);
        if (!$member)
        {
            $member = $this->doesMemberExistByDependant($memberNo);
            if (!$member)
            {
                return -1;
            }
        }

        $claim = $this->doesClaimExist($claimId, $member['id']);
        if (!$claim)
        {
            return -2;
        }

        $pdfBuilder = DIContainer::resolve(PdfBuilder::class);
        $html = '';
        $combinedDocs = [];
        foreach ($docs as $id => $doc)
        {
            if ($doc['filetype'] === PdfBuilder::MIME_TYPE)
            {
                $combinedDocs[]=$docs[$id];
            }
            else
            {
                $img = '
                    <img
                        style="max-width:100%;"
                        src="data:' . $doc['filetype'] . ';base64,' . $doc['contents'] . '"
                    />
                ';
                $html .= $img;
            }
        }
        $pdf = $pdfBuilder->build($html);
        $fileContents = base64_encode($pdf);
        $filesize = strlen($fileContents);

        $combinedDocs[] = [
            'filetype' => PdfBuilder::MIME_TYPE,
            'filesize' => $filesize,
            'filename' => 'camera_' . time() . ".pdf",
            'contents' => $fileContents
        ];

        foreach ($combinedDocs as $id => $doc)
        {
            $result = $this->doesDocumentExist($doc['filesize'], $doc['contents']);
            if ($result)
            {
                return $id;
            }
        }
        if(empty($note)){
            $note = ' ';
        }

        return $this->addClaimNote($this, $member, $claim, $note, $combinedDocs);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onClaimNoteAddedSuccess($data)
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
        $title_en = $this->i18n->noteAddTitle;
        $note_en = $this->i18n->noteAddMessage;
        $this->session->lzalanguage = '_vi';
        $title_vn = $this->i18n->noteAddTitle;
        $note_vn = $this->i18n->noteAddMessage;

        $title = $title_en."<br>". $title_vn;
        $note = $note_en."<br>".$note_vn;

        $data = [
            'claim_id' => $data['claim_id'],
            "body"=> $note,
            "title"=> $title
        ];

        return $this->noteHandler->add('system', 'device', $receivers, $title, $note, $data) !== false;
    }
}
