<?php
/**
 *  发送邮件email
 *
 * Kerisy Framework
 *
 * PHP Version 7
 *
 * @author          kaihui.wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @package         kerisy/framework
 * @version         3.0.0
 */

namespace Kerisy\Foundation\Bootstrap\Task;

use Kerisy\Config\Config;
use Kerisy\Foundation\Exception\ConfigNotFoundException;
use Kerisy\Support\Log;
use Kerisy\Foundation\Mail\Mail;

class Email
{
    /**
     *  执行函数
     * 
     * @param $receiver
     * @param $sender
     * @param $title
     * @param $msg
     * @return int
     * @throws ConfigNotFoundException
     */
    public function perform($receiver, $sender, $title, $msg)
    {
        Mail::Load();
        $sender = is_array($sender) ? $sender : [$sender => $sender];
        $receiver = is_array($receiver) ? $receiver : [$receiver];

        $message = \Swift_Message::newInstance()
            ->setSubject($title)
            ->setFrom($sender)
            ->setTo($receiver)
            ->setBody($msg, 'text/html', 'utf-8');

        $config = Config::get("app.email.server");
        if (!$config) {
            throw new ConfigNotFoundException("email.server not config");
        }

        $transport = \Swift_SmtpTransport::newInstance($config['smtp'], $config['port'])
            ->setUsername($config['username'])
            ->setPassword($config['password'])
            ->setEncryption($config['encryption']);

        $mailer = \Swift_Mailer::newInstance($transport);

        $failures = [];
        $result = $mailer->send($message, $failures);
        if (!$result) {
            Log::error($result);
        }
        return $result;
    }
}