<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MobileClaim;
use App\Models\MobileUser;
use App\Models\MobileClaimStatus;
use App\Models\MobileUserBankAccount;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Validator;
use Illuminate\Support\Facades\App;
use FPDM;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ClaimController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
    }
    /**
     * Get a list of Claims of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function issues()
    {
        $user = Auth::user();
        $issues = MobileClaim::select('mobile_claim.id','name','name_vi', 'code','note','mantis_id')->join('mobile_claim_status', 'mobile_claim_status.id', '=', 'mobile_claim_status_id')
            ->where('mobile_user_id', $user->id)
            ->get()->toArray();
        return $this->sendResponse($issues, 'OK', 0); 
    }

     /**
     * Get a Claims of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function issue($id)
    {
        $user = Auth::user();
        $claim = MobileClaim::join('mobile_claim_status', 'mobile_claim_status.id', '=', 'mobile_claim_status_id')
            ->where('mobile_claim.id', $id)
            ->first();
        if($claim == null){
            return $this->sendError(__('frontend.not_found'), 404, 404); 
        }
        $dateCreate = new \DateTime($claim->created_at);
        $now = new \DateTime();
        $dteDiff  = $dateCreate->diff($now);
        $hoursDiff = $dteDiff->h + $dteDiff->days*24;
        $claim['ranger_time'] = $hoursDiff;
        $claim['accept_add_sub'] = $hoursDiff <= 48 ? true : false; 
        $claim['extra'] = json_decode($claim['extra'],true);
        return $this->sendResponse($claim, 'OK', 0); 
    }

    /**
     * Post a Claims of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function issue_create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mbr_no' => 'required',
            'payment_type' => 'required',
            'reason' => 'required',
            'pres_amt' => 'required',
            'docs' => 'required',
            'symtom_time' => 'required_if:reason,illness',
            'occur_time' => 'required_if:reason,accident',
            'incident_detail' => 'required_if:reason,accident',
            'body_part' => 'required_if:reason,accident',
            'bank_acc_id'  => 'required_if:payment_type,bankTransfer',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }
        
        $user = Auth::user();
        $memberNo = $request->mbr_no;
        $payType = $request->payment_type ;
        $presAmt = $request->pres_amt;
        $bankAccId = $request->bank_acc_id;
        $reason = $request->reason;
        $symtomTime = $request->symtom_time;
        $occurTime = $request->occur_time;
        $bodyPart = $request->body_part;
        $detail = $request->incident_detail;
        $note =  $request->note;
        $fullname = $request->fullname;
        $docs = $request->docs;
        $pdfBuilder = new Dompdf();
        $pdfBuilder->set_paper('A4', 'portrait');

        $user_claim = MobileUser::where('mbr_no', $request->mbr_no)->first();
        $html = '';
        $combinedDocs = [];
        if($bankAccId != null ){
            $MobileUserBankAccount = MobileUserBankAccount::findOrFail($bankAccId);
        }
        $mime_accept = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/rtf',
            'text/csv',
            'application/x-7z-compressed',
            'application/zip',
            'application/vnd.rar'
        ];
        $mime_image_accept = [
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/bmp',
            'image/vnd.microsoft.icon',
            'image/tiff',
            'svg' => 'image/svg+xml'
        ];
        $dirk = Carbon::now()->format('m_Y');
        $path = get_path_upload();
        $filenames = [];
        $html = null;
        foreach($docs as $id => $doc)
        {
            
            if( in_array($doc['filetype'], $mime_accept) )
            {
                $combinedDocs[] = $docs[$id];
            }
            elseif( in_array($doc['filetype'], $mime_image_accept) )
            {
                $img = '
                    <img
                        style="max-width:100%;"
                        src="data:' . $doc['filetype'] . ';base64,' . $doc['contents'] . '"
                    />
                ';
                $html .= $img;
            }
            $filenames[] = [
                'url' => saveImageBase64($doc['contents'],$path),
                'disk' => $dirk,
                'note' => 1,
            ];
        }
        if($html != null){
            $pdfBuilder->load_html($html);
            $pdfBuilder->render();
            $pdf = $pdfBuilder->output();
            $fileContents = base64_encode($pdf);
            $filesize = strlen($fileContents);
            $combinedDocs[] = [
                'filetype' => 'application/pdf',
                'filesize' => $filesize,
                'filename' => 'user_upload_' . time() . ".pdf",
                'contents' => $fileContents
            ];
        }
        
        $lang = App::currentLocale();
        if($lang == 'en'){
            $fields = [
                'Insured Persons Name' => $fullname,
                'Policy No' => $user->pocy_no,
                'Member No' => $memberNo,
                'Correspondence Address' => $user->address,
                'Email' => $user->email,
                'Telephone' => $user->tel,
                'Date daymonthyear'=> date("d/m/Y"),
                'Signed' => $user->fullname,
                'When did the accident occur' => $occurTime,
                'Where did the accident occur' => '',
                'Please describe how the incident occurred 1' => $detail,
                'Which parts of the body was injured' => $bodyPart,
                'When was the first doctor visit treatment daymonthyear' => '',
                'Where was the first visit treatment name of hospital clinic 1' => '',
                'Name of the disease doctors diagnosis' => '',

                'When did the symptom first appear' => $symtomTime,
                'When did you first consult a doctor on this condition datemonthyear'=> '',
                'Where was the first visit treatment name of hospital clinic 2' => '',
                '3 Claims amount' => $presAmt,
                // 'Account Holders Name' => 'Lê Văn Chỉnh Bank',
                // 'Account No' => '123123434321412344',
                // 'Bank Name' => 'ACB',
                // 'Bank Address' => 'bank address, 123',
                'Date daymonthyear_2' => date("d/m/Y H:i:s"),
                'Signed_2' => 'Submitted via mobile PCV app'
            ];

            if (!is_null($bankAccId))
            {
                $fields['Account Holders Name'] = $MobileUserBankAccount->bank_acc_name;
                $fields['Account No'] = $MobileUserBankAccount->bank_acc_no;
                $fields['Bank Name'] = $MobileUserBankAccount->bank_name;
                $fields['Bank Address'] = $MobileUserBankAccount->bank_address;
            }
            $pdf = resource_path("files/claimform-en.pdf");
        }else{
            $fields = [
                'fill_1' => $fullname, //Tên Người Được Bảo Hiểm
                'fill_2' => $user->pocy_no, //Số Hợp đồng
                'fill_3' => $memberNo, //Mã số
                'fill_4' => $user->address, //Địa chỉ liên lạc
                'Email' => $user->email,
                'fill_6' => $user->tel, //Điện Thoại
                'fill_7'=> date("d/m/Y"), //Ngày (ngày/ tháng/ năm
                'Ký tên' => $user->fullname,

                'fill_9' => $occurTime, //a) Tai nạn xảy ra khi nào
                'fill_10' => '', //b) Tai nạn xảy ra ở đâu
                'fill_11' => $detail, //c) Vui lòng mô tả ngắn gọn hoàn cảnh xảy ra tai nạn
                // '1' => 'fill_11 Apparire a intendo ripararci cosa quale le forse come, manifestamente nella o io tal oppinione e la noi. Cose quali.',
                // '2' => 'lorem',
                'fill_14' => $bodyPart, //d) Những phần nào của cơ thể bị thương
                'fill_15' => '', //e) Thời gian thăm khám/ điều trị lần đầu tiên là khi nào (ngày/tháng/năm
                'fill_16' => '', //f) Nơi đầu tiên đến khám/ điều trị (tên bệnh viện/ phòng khám
                'fill_17' => '', //g) Có biên bản của cảnh sát không? Có

                'fill_18'=> '', //a) Tên bệnh/ chẩn đoán của bác sĩ
                'fill_19' => $symtomTime, //b) Triệu chứng đầu tiên xuất hiện khi nào
                'fill_20' => '', //c) Lần đầu tiên đến bác sĩ thăm khám cho vấn đề này là khi nào (ngày/tháng/năm
                'fill_21' => '', //d) Nơi đầu tiên đến khám/ điều trị (tên của bệnh viện/ phòng khám
                'fill_22' => $presAmt, //3. Số tiền yêu cầu bồi thường
                // 'fill_23' => 'fill 23', //Tên chủ tài khoản
                // 'fill_24' => 'fill 24', //Số tài khoản
                // 'fill_26' => 'fill 26', //Địa chỉ ngân hàng
                'fill_27' => date("d/m/Y H:i:s"), //Ngày (ngày/ tháng/ năm
                // 'Tên ngân hàng' => '', //Tên ngân hàng
                'toggle_1' => $payType == 'cash'? true:false, //Tiền mặt
                'toggle_3' => $payType == 'cash'? false:true, //Chuyển khoản (Vui lòng điền chi tiết thông tin tài khoản VND dưới đây
                'toggle_4' => false, //Nếu có, vui lòng cung cấp biên bản của cảnh sát cho chúng tôi
                'Ký tên_2' => 'Được tạo thông qua ứng dụng' //Ký tên
            ];
            
            if (!is_null($bankAccId))
            {
                $fields['fill_23'] = $MobileUserBankAccount->bank_acc_name;
                $fields['fill_24'] = $MobileUserBankAccount->bank_acc_no;
                $fields['Tên ngân hàng'] = $MobileUserBankAccount->bank_name;
                $fields['fill_26'] = $MobileUserBankAccount->bank_address;
            }
            
            $pdf = resource_path("files/claimform-vi.pdf");
        }
        $pdf = new FPDM($pdf);
        $pdf->useCheckboxParser = TRUE;
        $pdf->Load($fields, TRUE);
        $pdf->Merge();
        $pdf = $pdf->Output('S');
        $fileContents = base64_encode($pdf);
        $filesize = strlen($fileContents);
        
        $combinedDocs[] = [
            'filetype' => 'application/pdf',
            'filesize' => $filesize,
            'filename' => 'claim_form_'.$memberNo .'_'. time() . ".pdf",
            'contents' => $fileContents
        ];
        $filenames[] = [
            'url' => saveImageBase64($fileContents,$path),
            'disk' => $dirk,
            'note' => 1,
        ];
        
        try {
            DB::beginTransaction();
            $id_status_new = MobileClaimStatus::where('code',10)->first()->id;
            $claim = MobileClaim::create([
                'crt_by' => $user->id,
                'mantis_id' => 0,
                'mobile_user_id' => $user->id,
                'pay_type' => $payType,
                'pres_amt' => $presAmt,
                'mobile_user_bank_account_id' => $bankAccId,
                'reason' => $reason,
                'symtom_time' => $symtomTime,
                'occur_time' => $occurTime,
                'body_part' => $bodyPart,
                'incident_detail' => $detail,
                'note' => $note ? $note : "none",
                'dependent_memb_no' => $request->mbr_no,
                'fullname' => $fullname,
                'mobile_claim_status_id' => $id_status_new
            ]);
            $claim->mobile_claim_file()->createMany($filenames);
            $files = [];
            foreach ($combinedDocs as $doc)
            {
                $files[] = [
                    'name' => $doc['filename'],
                    'content' => $doc['contents']
                ];
            }
            $data_up_mantis = [
                'pocy_no' => $user->pocy_no,
                'mbr_no' => $dependentMbrNo ?? $user->mbr_no,
                'fullname' => $fullname ?? $user->fullname,
                'note' => $note,
                'files' => $files,
                'pay_type' => $payType,
                'pres_amt' => $presAmt,
                'bank_name' => is_null($bankAccId) ? "none" : data_get($MobileUserBankAccount,'bank_name'),
                'bank_address' => is_null($bankAccId) ? "none" : data_get($MobileUserBankAccount,'bank_address'),
                'bank_acc_no' => is_null($bankAccId) ?  "none" : data_get($MobileUserBankAccount,'bank_acc_no'),
                'bank_acc_name' => is_null($bankAccId) ?  "none" : data_get($MobileUserBankAccount,'bank_acc_name'),
                'reason' => $reason,
                'symtom_time' => $symtomTime,
                'occur_time' => $occurTime,
                'body_part' => $bodyPart,
                'incident_detail' => $detail,
            ];
            
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => config('constants.PCV_ETALK_API_TOKEN'),
            ];
            $client = new \GuzzleHttp\Client([
                    'headers' => $headers
                ]);
            
            $response = $client->request("POST", config('constants.PCV_ETALK_API_URL').'/mobile-claim/add' , ['form_params'=>$data_up_mantis]);
            $res = json_decode($response->getBody(),true);
            $claim->mantis_id = data_get($res,'id');
            $claim->extra = json_encode($res);
            $claim->save();
            DB::commit();
            return $this->sendResponse($claim , __('frontend.add_claim_message') , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }

}
