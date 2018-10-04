<?php
namespace kordar\timer;

use yii\base\Component;

class Timer extends Component
{
    /**
     * @var int
     */
    public $start = 0;

    /**
     * @var null
     */
    public $activeSign = null;

    /**
     * @var bool
     */
    public $isAll = false;

    /**
     * @var array
     */
    public $manager = [];

    protected function updateActiveSingle()
    {
        if ($this->activeSign === null && $this->start === 0) {
            return 'init';
        }

        if (!isset($this->manager[$this->activeSign])) {
            return 'none';
        }

        $time = time();
        $lastTime = $this->manager[$this->activeSign] - ($time - $this->start);
        $lastTime = $lastTime > 0 ? $lastTime : 0;

        if ($lastTime === 0) {
            // 检测时间，自动停止计时器
            unset($this->manager[$this->activeSign]);
            $this->activeSign = null;
            $this->start = 0;
            return 'stop';
        }

        // 更新当前计时器
        $this->manager[$this->activeSign] = $lastTime;
        $this->start = $time;
        return 'continue';
    }

    // 插入倒计时
    public function addSecond($sign, $second)
    {
        // 激活中，更新倒计时为最新状态
        $activeSign = $this->activeSign;
        $status = $this->updateActiveSingle();

        if (isset($this->manager[$sign])) {
            $this->manager[$sign] += intval($second);
        } else {
            $this->manager[$sign] = intval($second);
        }

        // 激活中计时器被关闭之后，需重新启动该计时器
        if ($activeSign === $sign && $status === 'stop') {
            $this->activeSign = $sign;
            $this->start = time();
        }
    }

    public function switchSign($sign)
    {
        $activeSign = $this->activeSign;
        $status = $this->updateActiveSingle();

        // 关闭
        if ($sign === null) {
            $this->activeSign = null;
            $this->start = 0;
            return true;
        }

        // 初始切换
        if ($status === 'init') {
            $this->activeSign = $sign;
            $this->start = time();
            return true;
        }

        if ($activeSign === $sign) {
            return false;
        }

        // 不同计时器之间的切换
        $this->activeSign = $sign;
        $this->start = time();
        return true;
    }

    public function getSecondBySign($sign)
    {
        $this->updateActiveSingle();
        return isset($this->manager[$sign]) ? $this->manager[$sign] : 0;
    }

}