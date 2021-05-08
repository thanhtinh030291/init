<?php

namespace Lza\App\Client\Modules\Api\V10\Claim;


use Lza\App\Client\Modules\Api\V10\ClientApiV10View;

use Monolog\Logger;
use Monolog\Handler\LogglyHandler;

/**
 * Process Claim API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimView extends ClientApiV10View
{
    /**
     * @api GET /api/claim/one-time-password/{mbr_no}
     *
     * @throws
     */
    public function getOneTimePassword($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (!$this->isTokenValid($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        if (empty($args['param_0']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }
        $memberNo = $args['param_0'];

        $result = $this->doGetOneTimePassword($memberNo);
        if ($result === false)
        {
            return $response->withStatus(208)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(208)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidTelNo
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!'
        ]);
    }

    /**
     * @api GET /api/claim/issues/{mbr_no}
     *
     * @throws
     */
    public function getIssues($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (!$this->isTokenValid($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        if (empty($args['param_0']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }
        $memberNo = $args['param_0'];

        $claims = $this->doGetClaims($memberNo);
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $claims
        ]);
    }

    /**
     * @api GET /api/claim/issue/{id}
     *
     * @throws
     */
    public function getIssue($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (!$this->isTokenValid($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        if (empty($args['param_0']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidClaimId
            ]);
        }
        $id = $args['param_0'];

        $claim = $this->doGetClaim($id);
        if ($claim === false)
        {
            return $response->withStatus(208)->withJson([
                'code' => 2,
                'message' => $this->i18n->claimNotExist
            ]);
        }
        $dateCreate = new \DateTime($claim['crt_at']);
        $now = new \DateTime();
        $dteDiff  = $dateCreate->diff($now);
        $hoursDiff = $dteDiff->h + $dteDiff->days*24;
        $claim['ranger_time'] = $hoursDiff;
        $claim['accept_add_sub'] = $hoursDiff <= 48 ? true : false; 
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $claim
        ]);
    }

    /**
     * @api POST /api/claim/issue
     *
     * @throws
     */
    public function postIssue($request, $response)
    {
        $log = new Logger('TEST_CLAIM');
        $log->pushHandler(new LogglyHandler('b5a7cbeb-e35b-4ce7-97a6-f8c2e516dd2f/tag/appuat', Logger::INFO));

        $request_data = $request->getParsedBody();

        $docss = $request_data['docs'];
        foreach ($docss as $no => $doc)
        {
            $request_data['docs'][$no]['contents'] = str_split($request_data['docs'][$no]['contents'], 200)[0];
        }

        $log->info("API POST check add claim: ".$request->getURI(),['request' => $request_data]);

        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (!$this->isTokenValid($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        // $otp = $request->getHeaderLine(self::HEADER_OTP);
        // if (!$this->isOtpValid($otp))
        // {
        //     return $response->withStatus(403)->withJson([
        //         'code' => 403,
        //         'message' => $this->i18n->invalidOtp
        //     ]);
        // }

        $data = $request->getParsedBody();
        if (!is_array($data))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidData
            ]);
        }
        
        if (empty($data['mbr_no']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($data['payment_type']) || !is_string($data['payment_type']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidPaymentType
            ]);
        }
        
        if (empty($data['pres_amt']) || intval($data['pres_amt']) <= 0)
        {
            return $response->withStatus(208)->withJson([
                'code' => 4,
                'message' => $this->i18n->invalidPresentedAmount
            ]);
        }

        if (!empty($data['reason']) && !is_string($data['reason']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 5,
                'message' => $this->i18n->invalidReason
            ]);
        }

        if (
            !empty($data['symtom_time']) &&
            date_create_from_format('Y-m-d', $data['symtom_time']) === false
        )
        {
            return $response->withStatus(208)->withJson([
                'code' => 6,
                'message' => $this->i18n->invalidSymtomTime
            ]);
        }

        if (
            !empty($data['occur_time']) &&
            date_create_from_format('Y-m-d', $data['occur_time']) === false
        )
        {
            return $response->withStatus(208)->withJson([
                'code' => 7,
                'message' => $this->i18n->invalidOccurTime
            ]);
        }

        if (!empty($data['body_part']) && !is_string($data['body_part']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 8,
                'message' => $this->i18n->invalidBodyPart
            ]);
        }

        if (!empty($data['incident_detail']) && !is_string($data['incident_detail']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 9,
                'message' => $this->i18n->invalidIncidentDetail
            ]);
        }
        // if (empty($data['note']) || !is_string($data['note']))
        // {
        //     return $response->withStatus(208)->withJson([
        //         'code' => 10,
        //         'message' => $this->i18n->invalidNote
        //     ]);
        // }

        if (empty($data['docs']) || !is_array($data['docs']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 11,
                'message' => $this->i18n->invalidDocs
            ]);
        }
        
        $docs = $data['docs'];
        foreach ($docs as $no => $doc)
        {
            $result = $this->isDocumentValid($doc);
            if ($result === false)
            {
                return $response->withStatus(208)->withJson([
                    'code' => 12,
                    'message' => $this->i18n->invalidDocNo($no + 1)
                ]);
            }
        }

        $memberNo = $data['mbr_no'];
        $payType = !empty($data['payment_type']) ? $data['payment_type'] : null;
        $presAmt = !empty($data['pres_amt']) ? $data['pres_amt'] : null;
        $bankAccId = !empty($data['bank_acc_id']) ? $data['bank_acc_id'] : null;
        $reason = !empty($data['reason']) ? $data['reason'] : null;
        $symtomTime = !empty($data['symtom_time']) ? $data['symtom_time'] : null;
        $occurTime = !empty($data['occur_time']) ? $data['occur_time'] : null;
        $bodyPart = !empty($data['body_part']) ? $data['body_part'] : null;
        $detail = !empty($data['incident_detail']) ? $data['incident_detail'] : null;
        $note = $data['note'];
        $dependentMbrNo = !empty($data['dependent_mbr_no']) ? $data['dependent_mbr_no'] : null;
        $fullname = !empty($data['fullname']) ? $data['fullname'] : null;

        $result = $this->doAddClaim(
            $memberNo, $note, $docs, $payType, $presAmt, $bankAccId, $reason, $symtomTime,
            $occurTime, $bodyPart, $detail, $dependentMbrNo, $fullname
        );

        if ($result === false)
        {
            return $response->withStatus(208)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === -1)
        {
            return $response->withStatus(208)->withJson([
                'code' => 13,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === -2)
        {
            return $response->withStatus(208)->withJson([
                'code' => 14,
                'message' => $this->i18n->bankAccountNotExist
            ]);
        }
        elseif (is_int($result))
        {
            return $response->withStatus(208)->withJson([
                'code' => 15,
                'message' => $this->i18n->docDuplicated($result + 1)
            ]);
        }
        return $response->withStatus(201)->withJson([
            'code' => 0,
            'message' => $this->i18n->claimAdded
        ]);
    }

    /**
     * @api PATCH /api/claim/status/{issue_id}
     *
     * @throws
     */
    public function patchStatus($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        if (empty($args['param_0']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidClaimId
            ]);
        }

        $data = $request->getParsedBody();
        if ($data === null)
        {
            return $response->withStatus(208)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidData
            ]);
        }

        if (empty($data['mbr_no']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($data['status']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 4,
                'message' => $this->i18n->invalidStatus
            ]);
        }

        if (empty($data['label']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 5,
                'message' => $this->i18n->invalidStatusLabel
            ]);
        }

        if (empty($data['notes']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 6,
                'message' => $this->i18n->invalidNotes
            ]);
        }

        $mantisId = $args['param_0'];
        $memberNo = $data['mbr_no'];
        $status = $data['status'];
        $label = $data['label'];
        $notes = $data['notes'];
        $result = $this->doUpdateStatus($mantisId, $memberNo, $status, $label, $notes);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(208)->withJson([
                'code' => 7,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 2)
        {
            return $response->withStatus(208)->withJson([
                'code' => 8,
                'message' => $this->i18n->claimNotExist
            ]);
        }
        elseif ($result === 3)
        {
            return $response->withStatus(208)->withJson([
                'code' => 9,
                'message' => $this->i18n->statusNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->statusUpdated
        ]);
    }

    /**
     * @api POST /api/claim/note/{claim_id}
     *
     * @throws
     */
    public function postNote($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (!$this->isTokenValid($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        // $otp = $request->getHeaderLine(self::HEADER_OTP);
        // if (!$this->isOtpValid($otp))
        // {
        //     return $response->withStatus(403)->withJson([
        //         'code' => 403,
        //         'message' => $this->i18n->invalidOtp
        //     ]);
        // }

        if (empty($args['param_0']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidClaimId
            ]);
        }

        $data = $request->getParsedBody();
        if (!is_array($data))
        {
            return $response->withStatus(208)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidData
            ]);
        }

        if (empty($data['mbr_no']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (!isset($data['note']) || !$this->isNoteValid($data['note']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 4,
                'message' => $this->i18n->invalidNote
            ]);
        }

        if (!is_array($data['docs']))
        {
            return $response->withStatus(208)->withJson([
                'code' => 5,
                'message' => $this->i18n->invalidDocs
            ]);
        }

        $docs = $data['docs'];
        foreach ($docs as $no => $doc)
        {
            $result = $this->isDocumentValid($doc);
            if ($result === false)
            {
                return $response->withStatus(208)->withJson([
                    'code' => 6,
                    'message' => $this->i18n->invalidDocNo($no + 1)
                ]);
            }
        }

        $claimId = $args['param_0'];
        $memberNo = $data['mbr_no'];
        $note = $data['note'];
        $result = $this->doAddClaimNote($claimId, $memberNo, $note, $docs);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === -1)
        {
            return $response->withStatus(208)->withJson([
                'code' => 7,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === -2)
        {
            return $response->withStatus(208)->withJson([
                'code' => 8,
                'message' => $this->i18n->claimNotExist
            ]);
        }
        elseif (is_int($result))
        {
            return $response->withStatus(208)->withJson([
                'code' => 9,
                'message' => $this->i18n->docDuplicated($result + 1)
            ]);
        }
        return $response->withStatus(201)->withJson([
            'code' => 0,
            'message' => $this->i18n->noteAdded
        ]);
    }

    /**
     * @throws
     */
    private function isNoteValid($note)
    {
        return strlen($note) <= 200;
    }

    /**
     * @throws
     */
    private function isDocumentValid($doc)
    {
        $log = new Logger('TEST_CLAIM');
        $log->pushHandler(new LogglyHandler('b5a7cbeb-e35b-4ce7-97a6-f8c2e516dd2f/tag/appuat', Logger::INFO));

        if (!is_array($doc))
        {
            $log->info("API POST check add claim: ",['error' => 'mang doc khong phai array']);
            return false;
        }

        if (
            empty($doc['filename']) ||
            empty($doc['filetype']) ||
            empty($doc['filesize']) ||
            empty($doc['contents'])
        )
        {
            $log->info("API POST check add claim: ",['error' => 'thieu truong du lieu']);
            return false;
        }

        if (strpos($doc['filename'], '.') === false)
        {
            $log->info("API POST check add claim: ",['error' => 'filename khong co dau cham']);
            return false;
        }

        if (
            strpos($doc['filetype'], 'image/') === false &&
            strpos($doc['filetype'], 'application/') === false
        )
        {
            $log->info("API POST check add claim: ",['error' => 'Kieu du lieu khong phai image hoac application']);
            return false;
        }

        // $contents = base64_decode($doc['contents'], true);
        // $filesize = strlen($contents);

        // if (intval($doc['filesize']) !== $filesize)
        // {
        //     $log->info("API POST check add claim: ",['error' => 'File check file size']);
        //     return false;
        // }

        // if ($doc['contents'] !== base64_encode($contents))
        // {
        //     return false;
        // }

        return true;
    }
}
