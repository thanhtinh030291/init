<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MobileClaim;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Validator;
use Illuminate\Support\Facades\App;
use FPDM;

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
            'payment_type' => 'required',
            'pres_amt' => 'required',
            'symtom_time' => 'required|date',
            'occur_time' => 'required|date',
            'docs' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }
        
        $user = Auth::user();
        $memberNo = $user->mbr_no;
        $payType = $request->payment_type ;
        $presAmt = $request->pres_amt;
        $bankAccId = $request->bank_acc_id;
        $reason = $request->reason;
        $symtomTime = $request->symtom_time;
        $occurTime = $request->occur_time;
        $bodyPart = $request->body_part;
        $detail = $request->incident_detail;
        $note =  $request->note;
        $dependentMbrNo = $request->mbr_no;
        $fullname = $request->fullname;
        $docs = $request->docs;
        $pdfBuilder = new Dompdf();
        $pdfBuilder->set_paper('A4', 'portrait');
        $html = '';
        $combinedDocs = [];
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
        foreach ($docs as $id => $doc)
        {
            // if ($doc['filetype'] === PdfBuilder::MIME_TYPE)
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
        }
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
        

        $lang = App::currentLocale();
   
   
   if($lang == 'en'){
            $fields = [
                'Insured Persons Name' => $user->fullname,
                'Policy No' => $user->pocy_no,
                'Member No' => $user->mbr_no,
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

            // if (!is_null($bankAcc))
            // {
            //     $fields['Account Holders Name'] = $bankAcc['bank_acc_name'];
            //     $fields['Account No'] = $bankAcc['bank_acc_no'];
            //     $fields['Bank Name'] = $bankAcc['bank_name'];
            //     $fields['Bank Address'] = $bankAcc['bank_address'];
            // }
            $pdf = resource_path("files/claimform-en.pdf");
        }else{
            $fields = [
                'fill_1' => $fullname, //Tên Người Được Bảo Hiểm
                'fill_2' => $user->pocy_no, //Số Hợp đồng
                'fill_3' => $user->mbr_no, //Mã số
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
            
            // if (!is_null($bankAcc))
            // {
            //     $fields['fill_23'] = $bankAcc['bank_acc_name'];
            //     $fields['fill_24'] = $bankAcc['bank_acc_no'];
            //     $fields['Tên ngân hàng'] = $bankAcc['bank_name'];
            //     $fields['fill_26'] = $bankAcc['bank_address'];
            // }
            
            $pdf = resource_path("files/claimform-vi.pdf");
        }
        $pdf = new FPDM($pdf);
        $pdf->useCheckboxParser = TRUE;
        $pdf->Load($fields, TRUE);
        $pdf->Merge();
        $PDF = $pdf->Output('S');
        dd($PDF);
        return $this->sendResponse($claim, 'OK', 0); 
    }

}
