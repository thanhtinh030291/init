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
        $issues = MobileClaim::select('mobile_claim.id','name','name_vi', 'code','note','mantis_id','mobile_claim.updated_at','is_read')->join('mobile_claim_status', 'mobile_claim_status.id', '=', 'mobile_claim_status_id')
            ->orderBy('mobile_claim.updated_at','DESC')
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
        $claim = MobileClaim::select('mobile_claim.*','name','name_vi','code')->join('mobile_claim_status', 'mobile_claim_status.id', '=', 'mobile_claim_status_id')
            ->where('mobile_claim.id', $id)
            ->first();
        if($claim == null){
            return $this->sendError(__('frontend.not_found'), 404, 404); 
        }
        $claim->is_read = 1;
        $claim->save();
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
            'fullname' => 'required',
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
        $note =  $request->note != null ? $request->note : 'none';
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
                // 'Account Holders Name' => 'L?? V??n Ch???nh Bank',
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
                'fill_1' => $fullname, //T??n Ng?????i ???????c B???o Hi???m
                'fill_2' => $user->pocy_no, //S??? H???p ?????ng
                'fill_3' => $memberNo, //M?? s???
                'fill_4' => $user->address, //?????a ch??? li??n l???c
                'Email' => $user->email,
                'fill_6' => $user->tel, //??i???n Tho???i
                'fill_7'=> date("d/m/Y"), //Ng??y (ng??y/ th??ng/ n??m
                'K?? t??n' => $user->fullname,

                'fill_9' => $occurTime, //a) Tai n???n x???y ra khi n??o
                'fill_10' => '', //b) Tai n???n x???y ra ??? ????u
                'fill_11' => $detail, //c) Vui l??ng m?? t??? ng???n g???n ho??n c???nh x???y ra tai n???n
                // '1' => 'fill_11 Apparire a intendo ripararci cosa quale le forse come, manifestamente nella o io tal oppinione e la noi. Cose quali.',
                // '2' => 'lorem',
                'fill_14' => $bodyPart, //d) Nh???ng ph???n n??o c???a c?? th??? b??? th????ng
                'fill_15' => '', //e) Th???i gian th??m kh??m/ ??i???u tr??? l???n ?????u ti??n l?? khi n??o (ng??y/th??ng/n??m
                'fill_16' => '', //f) N??i ?????u ti??n ?????n kh??m/ ??i???u tr??? (t??n b???nh vi???n/ ph??ng kh??m
                'fill_17' => '', //g) C?? bi??n b???n c???a c???nh s??t kh??ng? C??

                'fill_18'=> '', //a) T??n b???nh/ ch???n ??o??n c???a b??c s??
                'fill_19' => $symtomTime, //b) Tri???u ch???ng ?????u ti??n xu???t hi???n khi n??o
                'fill_20' => '', //c) L???n ?????u ti??n ?????n b??c s?? th??m kh??m cho v???n ????? n??y l?? khi n??o (ng??y/th??ng/n??m
                'fill_21' => '', //d) N??i ?????u ti??n ?????n kh??m/ ??i???u tr??? (t??n c???a b???nh vi???n/ ph??ng kh??m
                'fill_22' => $presAmt, //3. S??? ti???n y??u c???u b???i th?????ng
                // 'fill_23' => 'fill 23', //T??n ch??? t??i kho???n
                // 'fill_24' => 'fill 24', //S??? t??i kho???n
                // 'fill_26' => 'fill 26', //?????a ch??? ng??n h??ng
                'fill_27' => date("d/m/Y H:i:s"), //Ng??y (ng??y/ th??ng/ n??m
                // 'T??n ng??n h??ng' => '', //T??n ng??n h??ng
                'toggle_1' => $payType == 'cash'? true:false, //Ti???n m???t
                'toggle_3' => $payType == 'cash'? false:true, //Chuy???n kho???n (Vui l??ng ??i???n chi ti???t th??ng tin t??i kho???n VND d?????i ????y
                'toggle_4' => false, //N???u c??, vui l??ng cung c???p bi??n b???n c???a c???nh s??t cho ch??ng t??i
                'K?? t??n_2' => '???????c t???o th??ng qua ???ng d???ng' //K?? t??n
            ];
            
            if (!is_null($bankAccId))
            {
                $fields['fill_23'] = $MobileUserBankAccount->bank_acc_name;
                $fields['fill_24'] = $MobileUserBankAccount->bank_acc_no;
                $fields['T??n ng??n h??ng'] = $MobileUserBankAccount->bank_name;
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
            $data_save = [
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
                'note' => $note ,
                'dependent_memb_no' => $request->mbr_no,
                'fullname' => $fullname,
                'mobile_claim_status_id' => $id_status_new,
                'mbr_no' => $memberNo,
                'pocy_no' => $user->pocy_no,
                'company' => $user->company,
            ];
            if (!is_null($bankAccId))
            {
                $data_save['bank_acc_name'] = $MobileUserBankAccount->bank_acc_name;
                $data_save['bank_acc_no'] = $MobileUserBankAccount->bank_acc_no;
                $data_save['bank_name'] = $MobileUserBankAccount->bank_name;
                $data_save['bank_address'] = $MobileUserBankAccount->bank_address;
            }
            $claim = MobileClaim::create($data_save);
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
                'mbr_no' => $request->mbr_no,
                'fullname' => $fullname != null ? $fullname : $user->fullname,
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

    /**
     * Post a Note of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function note_create(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'docs' => 'required_without_all:note',
            'note' => 'required_without_all:docs'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }
        $claim = MobileClaim::where('id',$id)->first();
        if($claim == null){
            return $this->sendError(__("frontend.invalid_claim_id") , 400 , 400 );   
        }
        
        $combinedDocs = [];
        $dirk = Carbon::now()->format('m_Y');
        $path = get_path_upload();
        $filenames = [];
        $html = null;
        $docs = $request->docs;
        $note = $request->note;
        if(!empty($docs)){
            foreach ($docs as $id => $doc)
            {
                if ($doc['filetype'] === 'application/pdf')
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
                $filenames[] = [
                    'url' => saveImageBase64($doc['contents'],$path),
                    'disk' => $dirk,
                    'note' => 1,
                ];
            }
            if($html != null){
                $pdfBuilder = new Dompdf();
                $pdfBuilder->set_paper('A4', 'portrait');
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
        }
        $note = $request->note ? $request->note : " none ";

        try {
            DB::beginTransaction();
            $claim->mobile_claim_file()->createMany($filenames);
            $files = [];
            $filename = [];
            if(!empty($docs)){
                foreach ($combinedDocs as $doc)
                {
                    $files[] = [
                        'name' => $doc['filename'],
                        'content' => $doc['contents']
                    ];
                    $filename[]=$doc['filename'];
                }
            }
            $status = MobileClaimStatus::where('code',17)->first();
            $issue = json_decode($claim->extra, true);
            $issue['status'] = [
                'id' => $status->id,
                'name' => $status->name,
                'name_en' => $status->name,
                'name_vi' => $status->name_vi
            ];
            $issue['notes'][] = [
                'text' => $note,
                'date_submitted' => date('Y-m-d H:i:s'),
                'status' => $status,
                'filename' => implode('; ', $filename)
            ];

            $claim->mobile_claim_status_id = $status->id;
            $claim->extra = json_encode($issue);
            $data_up_mantis = [
                'note' => $note,
                'files' => $files,
            ];
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => config('constants.PCV_ETALK_API_TOKEN'),
            ];
            $client = new \GuzzleHttp\Client([
                    'headers' => $headers
                ]);
            
            $response = $client->request("POST", config('constants.PCV_ETALK_API_URL').'/mobile-claim/add-note/'.$claim->mantis_id, ['form_params'=>$data_up_mantis]);
            $res = json_decode($response->getBody(),true);
            $claim->save();
            DB::commit();
            return $this->sendResponse($claim , __('frontend.note_added') , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }

    /**
     * Post a Note of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function update_status(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }
       $mantis_id = $id;
       $company = $request->company ? $request->company : 'pcv';
       $status_code = $request->status;
       $claim  =  MobileClaim::where('mantis_id', $mantis_id)->where('company', $company)->first();
       if($claim == null ){
            return $this->sendError( __('frontend.claim_not_exist'), 400 , 400 );
       }
       $status = MobileClaimStatus::where('code', $status_code)->first();
       if($claim == null ){
                return $this->sendError( __('frontend.status_not_exist'), 400 , 400 );
        }
       $issue = json_decode($claim->extra, true);
       $issue['notes'] = $notes;
       $issue['status'] = [
        'id' => $status->id,
        'code' => $status->code,
        'name' => $status->name,
        'name_en' => $status->name,
        'name_vi' => $status->name_vi
       ];
        try {
            DB::beginTransaction();
            $claim->mobile_claim_status_id = $status->id;
            $claim->extra = json_encode($issue);
            $claim->is_read = 0;
            $claim->save();

            push_notify_fcm(__('frontend.update_claim_status_title') , $request->note , $claim->mobile_user_id);
            DB::commit();
            return $this->sendResponse($claim , __('frontend.note_added') , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }
}
