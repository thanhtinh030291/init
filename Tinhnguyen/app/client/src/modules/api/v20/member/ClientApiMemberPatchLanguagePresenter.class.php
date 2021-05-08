<?php

namespace Lza\App\Client\Modules\Api\V20\Member;


/**
 * Handle Update Member Language action
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
class ClientApiMemberPatchLanguagePresenter extends ClientApiMemberPresenter
{
    /**
     * Validate inputs and do Update Member Language request
     *
     * @throws
     */
    public function doUpdateLanguage($langCode, $memberNo)
    {
        $member = $this->doesMemberExist($memberNo);
        if ($member === false)
        {
            return 0;
        }

        $language = $this->doesLanguageExist($langCode);
        if ($language === false)
        {
            return 1;
        }

        return $this->updateLanguage($this, $member, $langCode) !== false;
    }
}
