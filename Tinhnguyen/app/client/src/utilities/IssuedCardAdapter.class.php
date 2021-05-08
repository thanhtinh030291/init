<?php

namespace Lza\App\Client\Utilities;


/**
 * Convert Member data to Card
 *
 * @var i18n
 * @var session
 * @var sql
 * @var member
 *
 * @author Le Vinh Nghiem (le.vinhnghiem@gmail.com)
 */
interface IssuedCardAdapter
{
    const EMAIL_BODY = "
        <h2>%s</h2>
        <table>
            <tbody>
                %s%s%s%s%s%s%s%s%s%s%s%s%s%s%s
            </tbody>
        </table>
    ";

    const EMAIL_ROW = '
        <tr>
            <td width="30%%%%" style="text-align:right">%s:</td>
            <td><strong>%s</strong><br /><strong>%s</strong></td>
        </tr>
    ';

    /**
     * Get data to be displayed on the web page
     */
    function getDisplayData();

    /**
     * Get data to be displayed on the email
     */
    function getHtmlData();
}
