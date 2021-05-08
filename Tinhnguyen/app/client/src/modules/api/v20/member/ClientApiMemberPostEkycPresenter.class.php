<?php

namespace Lza\App\Client\Modules\Api\Member;


use Lza\Config\Models\ModelPool;

/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPostEkycPresenter extends ClientApiMemberPresenter
{

    private static $mess_match = [
        0	=> "Successful",
        1	=> "The ID card photo is not existed",
        2	=> "The picture is a photocopy version of the id card",
        3	=> "The ID card photo is suspected of tampering",
        4	=> "The ID card photo does not contain a face",
        5	=> "The portrait photo does not contain a face",
        6	=> "Photo contains more than one face",
        7	=> "Wearing sunglasses	Đeo kính đen",
        8	=> "Wearing a hat	Đội mũ",
        9	=> "Wearing a mask	Đeo khẩu trang",
        10	=> "Photo taken from picture, screen, blurred noise or sign of fraud",
        11	=> "The face in the picture is too small",
        12	=> "The face in the portrait photo is too close to the margin",
    ];
    private static $mess_match_vi = [
        0	=> "Thành công",
        1	=> "Ảnh đầu vào không có giấy tờ tùy thân",
        2	=> "Ảnh giấy tờ tùy thân là bản photocopy",
        3	=> "Ảnh giấy tờ tùy thân có dấu hiệu giả mạo",
        4	=> "Ảnh giấy tờ tùy thân không có mặt",
        5	=> "Ảnh chân dung không có mặt",
        6	=> "Ảnh chứa nhiều hơn một mặt người",
        7	=> "Đeo kính đen",
        8	=> "Đội mũ",
        9	=> "Đeo khẩu trang",
        10	=> "Ảnh chụp từ bức ảnh khác, màn hình thiết bị, bị mờ nhiễu hoặc có dấu hiệu gian lận",
        11	=> "Mặt người trong ảnh quá nhỏ",
        12	=> "Mặt người trong ảnh quá gần với lề",
    ];

    /**
     * @throws
     */
    public function doEkyc($body_face_orc, $body_face_matching, $lang = "_vi"){
        $mess_match = $lang == "_vi" ? self::$mess_match_vi : self::$mess_match;
        $headers = [
            'Content-Type' => 'application/json'
        ];
        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'auth' => [
                KEY_API_EKYC,
                SECRET_API_EKYC
            ]
        ]);
            $mbr_name = null;
            $dob = null;
        try {
            $response_ocr = $client->request("POST", URL_API_EKYC."/api/v2/ekyc/cards?format_type=base64&get_thumb=false" , ['json'=>$body_face_orc]);
            $response_ocr =  json_decode($response_ocr->getBody()->getContents());
            if($response_ocr->errorCode != 0){
                return [
                    'code' => $response_ocr->errorCode,
                    'message' => $response_ocr->errorMessage,
                    'mbr_name' => $mbr_name,
                    'dob' =>  $dob,
                ];
            }else{
                if(isset($response_ocr->data[1])){
                    $mbr_name = $response_ocr->data[1]->info->name;
                    $dob = $response_ocr->data[1]->info->dob;
                }else{
                    return [
                        'code' => 400,
                        'message' => $this->i18n->id_font_requid,
                        'mbr_name' => $mbr_name,
                        'dob' =>  $dob,
                    ];
                }
            }

            //match_face
            $response_match = $client->request("POST", URL_API_EKYC."/api/v2/ekyc/face_matching?type1=card&format_type=base64&is_thumb=false" , ['json'=>$body_face_matching]);
            $response_match =  json_decode($response_match->getBody()->getContents());
            
            if($response_match->data->invalidCode != 0 && $response_match->data->matching <= 75){
                return [
                    'code' => $response_match->data->invalidCode,
                    'message' => $mess_match[$response_match->data->invalidCode],
                    'mbr_name' => $mbr_name,
                    'dob' =>  $dob,
                ];
            }elseif($response_match->data->matching <= 75){
                return [
                    'code' => 106,
                    'message' => $this->i18n->ekycNotMatch,
                    'mbr_name' => $mbr_name,
                    'dob' =>  $dob,
                ];
            }
            
        } catch (\Throwable $th) {
            return [
                'code' => 500,
                'message' => $this->i18n->sys_error,
                'mbr_name' => $mbr_name,
                'dob' =>  $dob,
            ];
        }

        return  [
            'code' => 0,
            'message' => 'success',
            'mbr_name' => $mbr_name,
            'dob' =>  $dob,
        ];
    }

    public function doCheckPocy($pocy){
        $model = ModelPool::getModel('PcvMember');
        $members = $model->where('pocy_no = ?', $pocy)->fetch();
        
        if($members== false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * @throws
     */
    public function getMember($pocy, $name , $dob)
    {
        $model = ModelPool::getModel('pcv_member');
        $member = $model->select('mbr_name', 'dob', 'pocy_no', 'mbr_no')->where('pocy_no = ? and mbr_name = ? and dob = ?', $pocy, $name, $dob);
        return $member->fetch();
    }

    public function issetAccount($pocy, $mbr_no){
        $model = ModelPool::getModel('mobile_user');
        $account = $model->where('pocy_no = ? and mbr_no = ?', $pocy, $mbr_no);
        return $account->fetch();
    }

    /**
     * Validate inputs and do Create Mobile User request
     *
     * @throws
     */
    public function doCreateMobileUser($memberNo,  $password , $langCode ,$email)
    {

        $member = $this->isMemberValid($memberNo);

        return $this->createMobileUser(
            $this, $member, [], null, $langCode, $password , $email
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onAccountCreatedSuccess($data)
    {
        if (is_null($data['email']) && MOBILE_ALLOW_WITHOUT_EMAIL){
            return true;
        }
        $companyName = $this->setting->companyName;
        $email = $this->setting->email;
        $subject = $this->i18n->createMobileUserSubject($companyName);
        $message = $this->i18n->createMobileUserMessage(
            $data['fullname'],
            $companyName,
            $data['email'],
            $data['password']
        );

        return $this->mailer->add(
            $data['username'],
            "$companyName Enquiry",
            $email,
            $data['fullname'],
            $data['email'],
            $subject,
            $message
        );
    }
}
