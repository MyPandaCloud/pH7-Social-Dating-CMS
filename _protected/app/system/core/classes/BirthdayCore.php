<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2013-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Core / Class
 */

namespace PH7;

use PH7\Framework\Mail\Mail;
use stdClass;

class BirthdayCore extends Core
{
    const MAX_BULK_EMAIL_NUMBER = 300;
    const SLEEP_SEC = 10;

    /** @var int */
    private static $_iTotalSent = 0;

    /**
     * Sent Birthday emails.
     *
     * @return int Total emails sent.
     */
    public function sendMails()
    {
        $oBirths = (new BirthdayCoreModel)->get();
        $oMail = new Mail;

        foreach ($oBirths as $oBirth) {
            // Do not send any emails at the same time to avoid overloading the mail server.
            if (self::$_iTotalSent > self::MAX_BULK_EMAIL_NUMBER) {
                sleep(self::SLEEP_SEC);
            }

            if ($this->sendMail($oBirth, $oMail)) {
                self::$_iTotalSent++;
            }
        }
        unset($oMail, $oBirths);

        return self::$_iTotalSent;
    }

    /**
     * Send birthday emails to users.
     *
     * @param stdClass $oUser User data from the DB.
     * @param Mail $oMail
     *
     * @return int Number of recipients who were accepted for delivery.
     */
    protected function sendMail(stdClass $oUser, Mail $oMail)
    {
        $this->view->content = t('Hi %0%!', $oUser->firstName) . '<br />' .
            t("The %site_name%'s team wish you a very happy birthday!") . '<br />' .
            t('Enjoy it well and enjoy yourself!');

        $sHtmlMsg = $this->view->parseMail(
            PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_DEFAULT_THEME . '/tpl/mail/sys/mod/user/birthday.tpl',
            $oUser->email
        );

        $aInfo = [
            'subject' => t('Happy Birthday %0%!', $oUser->firstName),
            'to' => $oUser->email
        ];

        return $oMail->send($aInfo, $sHtmlMsg);
    }
}
