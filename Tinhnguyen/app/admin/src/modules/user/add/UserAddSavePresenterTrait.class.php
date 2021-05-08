<?php

namespace Lza\App\Admin\Modules\User\Add;


/**
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
trait UserAddSavePresenterTrait
{
    /**
     * @var string Username of the User
     */
    private $username;

    /**
     * @var string Fullname of the User
     */
    private $fullname;

    /**
     * @var string Email of the User
     */
    private $email;

    /**
     * @var string Password of the User
     */
    private $password;

    /**
     * Validate inputs and do Add User request
     *
     * @throws
     */
    public function doSave($item, $fields, $many)
    {
        $this->username = $item['username'];
        $this->fullname = $item['fullname'];
        $this->email = $item['email'];
        $this->password = $item['password'];

        return parent::doSave($item, $fields, $many);
    }

    /**
     * Event when an action is successfully executed
     *
     * @throws
     */
    public function onSaveSuccess($data = null)
    {
        $url = WEBSITE_ROOT;

        $companyName= $this->setting->companyName;
        $subject = $this->i18n->addUserEmailSubject($url, $this->fullname, $this->email);
        $message = $this->i18n->addUserEmailMessage(
            $this->fullname,
            $url,
            $this->username,
            $this->email,
            $this->password,
            $companyName
        );

        $fromName = $this->setting->companyName . ' Inquiry';
        $fromEmail = $this->setting->email;
        $this->mailer->add($this->username, $fromName, $fromEmail, $this->fullname, $this->email, $subject, $message);
        parent::onSaveSuccess($data);
    }
}
