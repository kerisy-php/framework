<?php
/**
 *
 *
 * @author          Kaihui Wang <hpuwang@gmail.com>
 * @copyright      (c) 2015 putao.com, Inc.
 * @since           16/7/2
 */

namespace Kerisy\Job;


class JobRetryException extends JobException
{

    private $delay_seconds = 7200;
    public function setDelay($delay) {
        $this->delay_seconds = $delay;
    }
    public function getDelay() {
        return $this->delay_seconds;
    }
}