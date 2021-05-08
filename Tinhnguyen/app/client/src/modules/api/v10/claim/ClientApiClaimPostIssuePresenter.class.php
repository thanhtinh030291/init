<?php

namespace Lza\App\Client\Modules\Api\V10\Claim;


use Lza\Config\Models\ModelPool;
use Lza\LazyAdmin\Utility\Pattern\DIContainer;
use Lza\LazyAdmin\Utility\Tool\PdfBuilder;
use Lza\LazyAdmin\Utility\Tool\PdfFormFiller;

/**
 * Handle Create Claim action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiClaimPostIssuePresenter extends ClientApiClaimPresenter
{
    /**
     * Validate inputs and do Add New Claim request
     *
     * @throws
     */
    public function doAddClaim(
        $memberNo, $note, $docs, $payType, $presAmt, $bankAccId, $reason, $symtomTime,
        $occurTime, $bodyPart, $detail, $dependentMbrNo = null, $fullname = null
    )
    {
        global $ds;
        $member = $this->doesMemberExist($memberNo);

        if (!$member)
        {
            return -1;
        }
        $fullname = $fullname ?? $member['fullname'];
        $member_no = $dependentMbrNo ?? $member['mbr_no'];

        $bankAcc = null;
        if ($bankAccId !== null)
        {
            $bankAcc = $this->doesBankAccountExist($bankAccId);
            if (!$bankAcc)
            {
                return -2;
            }
        }

        $pdfBuilder = DIContainer::resolve(PdfBuilder::class);
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
        $pdf = $pdfBuilder->build($html);
        $fileContents = base64_encode($pdf);
        $filesize = strlen($fileContents);
        
        $combinedDocs[] = [
            'filetype' => PdfBuilder::MIME_TYPE,
            'filesize' => $filesize,
            'filename' => 'user_upload_' . time() . ".pdf",
            'contents' => $fileContents
        ];

        if ($this->session->lzalanguage === '')
        {
            $fields = [
                'Insured Persons Name' => $fullname,
                'Policy No' => $member['pocy_no'],
                'Member No' => $member_no,
                'Correspondence Address' => $member['address'],
                'Email' => $member['email'],
                'Telephone' => $member['tel'],
                'Date daymonthyear'=> date("d/m/Y"),
                'Signed' => $member['fullname'],
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

            if (!is_null($bankAcc))
            {
                $fields['Account Holders Name'] = $bankAcc['bank_acc_name'];
                $fields['Account No'] = $bankAcc['bank_acc_no'];
                $fields['Bank Name'] = $bankAcc['bank_name'];
                $fields['Bank Address'] = $bankAcc['bank_address'];
            }

            $pdf = RES_PATH . "files{$ds}claimform-en.pdf";
        }
        else
        {
            $fields = [
                'fill_1' => $fullname, //Tên Người Được Bảo Hiểm
                'fill_2' => $member['pocy_no'], //Số Hợp đồng
                'fill_3' => $member_no, //Mã số
                'fill_4' => $member['address'], //Địa chỉ liên lạc
                'Email' => $member['email'],
                'fill_6' => $member['tel'], //Điện Thoại
                'fill_7'=> date("d/m/Y"), //Ngày (ngày/ tháng/ năm
                'Ký tên' => $member['fullname'],

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

            if (!is_null($bankAcc))
            {
                $fields['fill_23'] = $bankAcc['bank_acc_name'];
                $fields['fill_24'] = $bankAcc['bank_acc_no'];
                $fields['Tên ngân hàng'] = $bankAcc['bank_name'];
                $fields['fill_26'] = $bankAcc['bank_address'];
            }

            $pdf = RES_PATH . "files{$ds}claimform-vi.pdf";
        }

        $pdfFormFiller = DIContainer::resolve(PdfFormFiller::class);
        $pdf = $pdfFormFiller->fill($pdf, $fields);
        $fileContents = base64_encode($pdf);
        $filesize = strlen($fileContents);
        
        $combinedDocs[] = [
            'filetype' => PdfFormFiller::MIME_TYPE,
            'filesize' => $filesize,
            'filename' => 'claim_form_'.$member['mbr_no'] .'_'. time() . ".pdf",
            'contents' => $fileContents
        ];

        // foreach ($combinedDocs as $id => $doc)
        // {
        //     $result = $this->doesDocumentExist($doc['filesize'], $doc['contents']);
        //     if ($result)
        //     {
        //         return $id;
        //     }
        // }
            
        return $this->addClaim(
            $this, $member, $bankAcc, $note, $combinedDocs, $payType, $presAmt, $reason,
            $symtomTime, $occurTime, $bodyPart, $detail, $dependentMbrNo, $fullname
        );
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onAddClaimSuccess($data)
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

        $title = $this->i18n->addClaimTitle;
        $note = $this->i18n->addClaimMessage;

        $data = [
            "body"=> $note,
            "title"=> $title
        ];

        return $this->noteHandler->add('system', 'device', $receivers, $title, $note, $data) !== false;
    }
}
