<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

abstract class RepeatOptionsAbstract implements RepeatOptionsInterface
{
    /**
     * @var int
     */
    protected $_interval;

    /**
     * @var Date
     */
    protected $_terminationDate;

    /**
     * @return Date
     */
    public function terminationDate()
    {
        return $this->_terminationDate;
    }

    /**
     * @param  int  $interval
     * @param  Date  $terminationDate
     */
    protected function __construct($interval, $terminationDate)
    {
        $this->_interval = $interval;
        $this->_terminationDate = $terminationDate;
    }

    public function configurationString()
    {
        return sprintf('interval=%s|termination=%s', $this->_interval, $this->_terminationDate->toDatabase());
    }

    public function equals(RepeatOptionsInterface $repeatOptions)
    {
        return $this->configurationString() == $repeatOptions->configurationString();
    }

    public function hasSameConfigurationAs(RepeatOptionsInterface $repeatOptions)
    {
        return get_class($this) == get_class($repeatOptions) && $this->_interval == $repeatOptions->_interval;
    }
}
