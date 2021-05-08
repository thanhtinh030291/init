<?php

namespace Lza\App\Client\Modules\Api\V20\Gop;


use Lza\App\Client\Modules\Api\V20\ClientApiV20View;

/**
 * Process GOP API
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiGopView extends ClientApiV20View
{
    /**
     * @api GET /api/gop/validate/{insurer}/{mbr_no}
     *
     * @throws
     */
    public function getValidate($request, $response, $args)
    {
        if (empty($args['param_0']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidInsurer
            ]);
        }
        $insurer = $args['param_0'];

        if (empty($args['param_1']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }
        $memberNo = $args['param_1'];

        $data = $request->getParsedBody();

        if (!is_array($data))
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidData
            ]);
        }

        if (
            empty($data['incur_date_from']) ||
            !date_create_from_format('Y-m-d', $data['incur_date_from'])
        )
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->invalidIncurDate
            ]);
        }
        $incurDateFrom = $data['incur_date_from'];

        if (
            empty($data['claim_lines']) ||
            !is_array($data['claim_lines']) ||
            !$this->valdateClaimLines($data['claim_lines'])
        )
        {
            return $response->withStatus(400)->withJson([
                'code' => 5,
                'message' => $this->i18n->invalidClaimLines
            ]);
        }
        $claimLines = $data['claim_lines'];

        $result = $this->doValidate($insurer, $memberNo, $incurDateFrom, $claimLines);
        if ($result === false)
        {
            return $response->withStatus(208)->withJson([
                'code' => 6,
                'message' => $this->i18n->memberNotExist
            ]);
        }

        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $result
        ]);
    }

    /**
     * @throws
     */
    public function valdateClaimLines($claimLines)
    {
        foreach ($claimLines as $claimLine)
        {
            if (
                empty($claimLine['clli_oid']) ||
                empty($claimLine['ben_type']) ||
                empty($claimLine['ben_head']) ||
                empty($claimLine['diagnosis']) ||
                empty($claimLine['app_amt'])
            )
            {
                return false;
            }
        }
        return true;
    }
}
