<?php
namespace kordar\timer;

use yii\base\Component;

/**
 * Class RedisTimer
 * @package kordar\timer
 */
class RedisSingleTimer extends Component
{
    /**
     * @var null | \yii\redis\Connection
     */
    public $redis = null;

    /**
     * @var string
     */
    public $name = 'redis';

    /**
     * @var string
     */
    public $redisKey = 'TIMER-9732BE1F';

    /**
     * @var null | Timer
     */
    private $timer = null;

    public function init()
    {
        $this->redis = \Yii::$app->get($this->name);
    }

    private function updateTimer($id, $timer)
    {
        $this->redis->hset($this->redisKey, $id, serialize(get_object_vars($timer)));
    }

    /**
     * @param $id
     * @return object | AutoTimer
     * @throws \yii\base\InvalidConfigException
     */
    private function getTimerObject($id)
    {
        $params['class'] = AutoTimer::className();
        $serialize = $this->redis->hget($this->redisKey, $id);
        if (!empty($serialize)) {
            $param = unserialize($serialize);
            $params = array_merge($params, $param);
        }
        return \Yii::createObject($params);
    }

    /**
     * @param $id
     * @param $sign
     * @param $second
     * @throws \yii\base\InvalidConfigException
     */
    public function setTimer($id, $sign, $second)
    {
        $timer = $this->getTimerObject($id);
        $timer->addSecond($sign, $second);
        $this->updateTimer($id, $timer);
    }

    /**
     * @param $id
     * @param $sign
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function getSecond($id, $sign)
    {
        $timer = $this->getTimerObject($id);
        $second = $timer->getSecondBySign($sign);
        $this->updateTimer($id, $timer);
        return $second;
    }

    /**
     * @param $id
     * @param $sign
     * @return bool
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function open($id, $sign)
    {
        $timer = $this->getTimerObject($id);
        $timer->switchSign($sign);
        $this->updateTimer($id, $timer);
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     */
    public function close($id)
    {
        $timer = $this->getTimerObject($id);
        $timer->switchSign(null);
        $this->updateTimer($id, $timer);
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function autoStart($id)
    {
        $timer = $this->getTimerObject($id);
        $timer->autoStart();
        $this->updateTimer($id, $timer);
        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function autoStop($id)
    {
        $timer = $this->getTimerObject($id);
        $timer->autoStop();
        $this->updateTimer($id, $timer);
        return true;
    }
}