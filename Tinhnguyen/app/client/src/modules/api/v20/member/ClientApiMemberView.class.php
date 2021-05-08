<?php

namespace Lza\App\Client\Modules\Api\V20\Member;
use GuzzleHttp\Client;

use Lza\App\Client\Modules\Api\V20\ClientApiV20View;

/**
 * Process Member API
 * @var str_helper
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberView extends ClientApiV20View
{
    const MOBILE_DEFAULT_PASSWORD = 'msY}w7J@7';

    /**
     * @api POST /api/member/register
     *
     * @throws
     */
    public function postRegister($request, $response)
    {
        
        $data = $request->getParsedBody();

        $data['language'] = !isset($data['language']) || strpos($data['language'], 'vi') === false ? '' : '_vi';
        $this->session->lzalanguage = $data['language'];

        if (empty($data['pocy_no']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->pocy_no_requid
            ]);
        }
        if (!$this->validator->validateEmail($data['email'],true))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->email_requild
            ]);
        }
        
        //check Polihoder
        $rp_check_pocy = $this->doCheckPocy($data['pocy_no']);
        if($rp_check_pocy == false){
            return $response->withStatus(200)->withJson([
                'code' => 101,
                'message' => $this->i18n->pocy_no_not_exit
            ]);
        }
        
        //check init or not email 
        $unit_mb = $this->checkUnitOrNotEmail($data['pocy_no'], $data['email']);
        if(count($unit_mb) != 1){
            return $response->withStatus(200)->withJson([
                'code' => 10,
                'message' => 'Please use eKYC'
            ]);
        }else{
            $memberNo = $unit_mb[0]['mbr_no'];
            $password = uniqid();
            $result = $this->doCreateMobileUser($memberNo, [], null, $data['language'], $password, $data['email']);
            if ($result === false)
            {
                return $response->withStatus(500)->withJson([
                    'code' => 500,
                    'message' => $this->i18n->internalServerError
                ]);
            }
            elseif ($result === 1)
            {
                return $response->withStatus(400)->withJson([
                    'code' => 107,
                    'message' => $this->i18n->memberNotExist
                ]);
            }
            elseif ($result === 0)
            {
                return $response->withStatus(400)->withJson([
                    'code' => 108,
                    'message' => $this->i18n->accountExist
                ]);
            }
            return $response->withStatus(200)->withJson([
                'code' => 9,
                'message' => sprintf($this->i18n->register_success, $data['email']),
            ]);
        }
        
        
    }

    /**
     * @api POST /api/member/forget-password
     *
     * @throws
     */
    public function postForgetPassword($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;
        $data = $request->getParsedBody();

        if (!$this->validator->validateEmail($data['email'],true))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->email_requild
            ]);
        }
        $result = $this->doCreateResetPasswordRequest($data['email']);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->accountNotExist
            ]);
        }
        return $response->withStatus(201)->withJson([
            'code' => 0,
            'message' => $this->i18n->resetCreated
        ]);
    }

    /**
     * @api PATCH /api/member/forget-password/{mbr_no}
     *
     * @throws
     */
    public function patchForgetPassword($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $token = $request->getHeaderLine(self::HEADER_SESSION_ID);
        if (empty($token))
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }

        if (empty($args['param_0']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();

        if (empty($data['password']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidPassword
            ]);
        }

        $memberNo = $args['param_0'];
        $password = $data['password'];
        $result = $this->doResetPassword($memberNo, $password, $token);
        if ($result === 0)
        {
            return $response->withStatus(403)->withJson([
                'code' => 403,
                'message' => $this->i18n->invalidToken
            ]);
        }
        elseif ($result === -1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->resetExpired
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(200)->withJson([
                'code' => 0,
                'message' => $this->i18n->passwordUpdated
            ]);
        }
        return $response->withStatus(500)->withJson([
            'code' => 500,
            'message' => $this->i18n->internalServerError
        ]);
    }

    /**
     * @api POST /api/member/login
     *
     * @throws
     */
    public function postLogin($request, $response, $args)
    {
        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;

        $data = $request->getParsedBody();

        if (empty($data['password']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidPassword
            ]);
        }

        if (!$this->validator->validateEmail($data['email'],true))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->email_requild
            ]);
        }
        $user = $data['email'];
        $pass = $data['password'];
        $session = $this->doLogin($user, $pass);
        if ($session === false)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidAccountPassword
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->logined,
            'fullname' => $session['fullname'],
            'session_id' => $session['token'],
            'mbr_no' => $session['mbr_no'],
            'email' => $session['email']
        ]);
    }

    /**
     * @api POST /api/member/ekyc/
     *
     * @throws
     */
    public function postEkyc($request, $response, $args)
    {

        $language = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $this->session->lzalanguage = $language;
        
        $data = $request->getParsedBody();
        if (empty($data['pocy_no']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->pocy_no_requid
            ]);
        };
        if (!$this->validator->validateEmail($data['email'],true))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->email_requild
            ]);
        }
        if (empty($data['photo_front']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->id_font_requid
            ]);
        }
        if (empty($data['photo_back']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->id_back_requid
            ]);
        }
        if (empty($data['photo_face']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 400,
                'message' => $this->i18n->face_requid
            ]);
        }
        //check Polihoder
        $rp_check_pocy = $this->doCheckPocy($data['pocy_no']);
        if($rp_check_pocy == false){
            return $response->withStatus(200)->withJson([
                'code' => 101,
                'message' => $this->i18n->pocy_no_not_exit
            ]);
        }

        $body_face_matching = [
            'img1' => $data['photo_front'],
            'img2' => $data['photo_face'],
        ];

        $body_face_orc = [
            'img1' => $data['photo_front'],
            'img2' => $data['photo_back'],
        ];
        
        $rp_api = $this->doEkyc($body_face_orc ,  $body_face_matching, $language);
        if($rp_api['code'] != 0){
            return $response->withStatus(400)->withJson([
                'code' => $rp_api['code'],
                'message' => $rp_api['message']
            ]);
        }else{
            $mbr_name = strtoupper($this->str_helper->vn_to_str($rp_api['mbr_name']));
            $dob = date_create_from_format('d-m-Y', $rp_api['dob'])->format('Y-m-d');
            
            $is_member = $this->getMember($data['pocy_no'], $mbr_name, $dob);
            if($is_member == false){
                return $response->withStatus(400)->withJson([
                    'code' => 101,
                    'message' => $this->i18n->invalid_effect_member
                ]);
            }
            $mbr_no = $is_member['mbr_no'];
            $isset_account = $this->issetAccount($data['pocy_no'], $is_member['mbr_no']);
            if($isset_account != false){
                return $response->withStatus(400)->withJson([
                    'code' => 102,
                    'message' => $this->i18n->account_exist
                ]);

            }
            
        }
        //register account

        $password = uniqid();
        $result = $this->doCreateMobileUser($mbr_no, $password, $language, $data['email']);

        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => sprintf($this->i18n->register_success, $data['email']),
        ]);
    }

    /**
     * @api PATCH /api/member/password/{mbr_no}
     *
     * @throws
     */
    public function patchPassword($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();

        if (empty($data['old']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidOldPass
            ]);
        }

        if (empty($data['new']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidNewPass
            ]);
        }

        $memberNo = $args['param_0'];
        $oldPass = $data['old'];
        $newPass = $data['new'];
        $result = $this->doUpdatePassword($memberNo, $oldPass, $newPass);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->passwordUpdated
        ]);
    }

    /**
     * @api GET /api/member/info/{mbr_no}
     *
     * @throws
     */
    public function getInfo($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $info = $this->doGetInfo($memberNo);
        if ($info === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif (count($info) === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'info' => $info
        ]);
    }

    /**
     * @api GET /api/member/benefits/{mepl_oid}
     *
     * @throws
     */
    public function getBenefits($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $lang = $request->getHeaderLine(self::HEADER_LANGUAGE);
        $benefits = $this->doGetBenefits($memberNo, $lang);
        if ($benefits === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif (count($benefits) === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'data' => $benefits
        ]);
    }

    /**
     * @api GET /api/member/related-members/{mbr_no}
     *
     * @throws
     */
    public function getRelatedMembers($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $members = $this->doGetRelatedMembers($memberNo);
        if ($members === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'members' => $members
        ]);
    }

    /**
     * @api GET /api/member/devices/{mbr_no}
     *
     * @throws
     */
    public function getDevices($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $devices = $this->doGetDevices($memberNo);
        if ($devices === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($devices === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'devices' => $devices
        ]);
    }

    /**
     * @api POST /api/member/device/{mbr_no}
     *
     * @throws
     */
    public function postDevice($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();

        if (empty($data['token']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidToken
            ]);
        }

        $memberNo = $args['param_0'];
        $token = $data['token'];
        $result = $this->doAddDevice($token, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->deviceExist
            ]);
        }
        return $response->withStatus(201)->withJson([
            'code' => 0,
            'message' => $this->i18n->deviceAdded
        ]);
    }

    /**
     * @api PUT /api/member/device/{mbr_no}/{token}
     *
     * @throws
     */
    public function putDevice($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($args['param_1']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidDevice
            ]);
        }

        $memberNo = $args['param_0'];
        $token = $args['param_1'];
        $result = $this->doUpdateDevice($token, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 5,
                'message' => $this->i18n->deviceNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->deviceUpdated
        ]);
    }

    /**
     * @api DELETE /api/member/device/{mbr_no}/{device_token}
     *
     * @throws
     */
    public function deleteDevice($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($args['param_1']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidDevice
            ]);
        }

        $memberNo = $args['param_0'];
        $token = $args['param_1'];
        $result = $this->doDeleteDevice($token, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 5,
                'message' => $this->i18n->deviceNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->deviceDeleted
        ]);
    }

    /**
     * @api GET /api/member/bank-accounts/{mbr_no}
     *
     * @throws
     */
    public function getBankAccounts($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $accounts = $this->doGetBankAccounts($memberNo);
        if ($accounts === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($accounts === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'bank_accounts' => $accounts
        ]);
    }

    /**
     * @api POST /api/member/bank-account/{mbr_no}
     *
     * @throws
     */
    public function postBankAccount($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();
        if (empty($data['bank_account']) || !$this->isBankAccountValid($data['bank_account']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidBankAccount
            ]);
        }

        $memberNo = $args['param_0'];
        $bankAccount = $data['bank_account'];
        $result = $this->doAddBankAccount($bankAccount, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->bankAccountExist
            ]);
        }
        return $response->withStatus(201)->withJson([
            'code' => 0,
            'message' => $this->i18n->bankAccountAdded
        ]);
    }

    /**
     * @api PUT /api/member/bank-account/{mbr_no}/{acc_id}
     *
     * @throws
     */
    public function putBankAccount($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($args['param_1']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidBankAccountId
            ]);
        }

        $data = $request->getParsedBody();

        $bankAccount = isset($data['bank_account']) ? $data['bank_account'] : null;
        if (empty($data['bank_account']) || !$this->isBankAccountValid($data['bank_account']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->invalidBankAccount
            ]);
        }

        $memberNo = $args['param_0'];
        $id = $args['param_1'];
        $bankAccount = $data['bank_account'];
        $result = $this->doUpdateBankAccount($bankAccount, $id, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 5,
                'message' => $this->i18n->bankAccountNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->bankAccountUpdated
        ]);
    }

    /**
     * @api DELETE /api/member/bank-account/{mbr_no}/{acc_id}
     *
     * @throws
     */
    public function deleteBankAccount($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        if (empty($args['param_1']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidBankAccountId
            ]);
        }

        $memberNo = $args['param_0'];
        $id = $args['param_1'];
        if($this->checkRelatedClaim($id)){
            return $response->withStatus(200)->withJson([
                "code" => '01',
                "message" => $this->i18n->relatedClaim
            ]);
        }
        $result = $this->doDeleteBankAccount($id, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 5,
                'message' => $this->i18n->bankAccountNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->bankAccountDeleted
        ]);
    }

    /**
     * @api PATCH /api/member/photo/{mbr_no}
     *
     * @throws
     */
    public function patchPhoto($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();

        if (!empty($data['photo']) && !$this->isPhotoValid($data['photo']))
        {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->invalidPhoto
            ]);
        }

        $memberNo = $args['param_0'];
        $photo = $data['photo'];
        $result = $this->doUpdatePhoto($photo, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->photoUpdated
        ]);
    }

    /**
     * @api PATCH /api/member/language/{mbr_no}
     *
     * @throws
     */
    public function patchLanguage($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $data = $request->getParsedBody();

        if (empty($data['language']))
        {
            $data['language'] = '';
        }

        $memberNo = $args['param_0'];
        $language = $data['language'];
        $language = empty($language) || strpos($language, 'vi') === false ? '' : '_vi';

        $result = $this->doUpdateLanguage($language, $memberNo);
        if ($result === false)
        {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        }
        elseif ($result === 0)
        {
            return $response->withStatus(400)->withJson([
                'code' => 3,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        elseif ($result === 1)
        {
            return $response->withStatus(400)->withJson([
                'code' => 4,
                'message' => $this->i18n->invalidLanguage
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => $this->i18n->languageUpdated
        ]);
    }

    /**
     * @api GET /api/member/insurance-card/{mbr_no}
     *
     * @throws
     */
    public function getInsuranceCard($request, $response, $args)
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
            return $response->withStatus(400)->withJson([
                'code' => 1,
                'message' => $this->i18n->invalidMemberNo
            ]);
        }

        $memberNo = $args['param_0'];
        $card = $this->doGetInsuranceCard($memberNo, $request->getHeaderLine(self::HEADER_LANGUAGE));
        if ($card == false) {
            return $response->withStatus(500)->withJson([
                'code' => 500,
                'message' => $this->i18n->internalServerError
            ]);
        } elseif ($card === 0) {
            return $response->withStatus(400)->withJson([
                'code' => 2,
                'message' => $this->i18n->memberNotExist
            ]);
        }
        return $response->withStatus(200)->withJson([
            'code' => 0,
            'message' => 'OK!',
            'insurance_card' => $card
        ]);
    }

    /**
     * @throws
     */
    private function isBankAccountValid($account)
    {
        if (!is_array($account))
        {
            return false;
        }

        if (
            empty($account['bank_name']) ||
            empty($account['bank_address']) ||
            empty($account['bank_acc_no']) ||
            empty($account['bank_acc_name'])
        )
        {
            return false;
        }

        return true;
    }

    /**
     * @throws
     */
    private function isPhotoValid($photo)
    {
        if (!is_array($photo))
        {
            return false;
        }

        if (
            empty($photo['filename']) ||
            empty($photo['filetype']) ||
            empty($photo['filesize']) ||
            empty($photo['contents'])
        )
        {
            return false;
        }

        if (strpos($photo['filename'], '.') === false)
        {
            return false;
        }

        if (strpos($photo['filetype'], 'image/') === false)
        {
            return false;
        }

        $contents = base64_decode($photo['contents'], true);
        if ($photo['contents'] !== base64_encode($contents))
        {
            return false;
        }

        return true;
    }
}
