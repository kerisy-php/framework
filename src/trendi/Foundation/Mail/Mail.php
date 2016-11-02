<?php
/**
 * User: Peter Wang
 * Date: 16/10/14
 * Time: 下午5:11
 */
namespace Trendi\Foundation\Mail;

class Mail
{
    
    public static function Load()
    {
        require_once __DIR__ . "/swift_required.php";
    }

}