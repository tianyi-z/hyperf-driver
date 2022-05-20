<?php

declare(strict_types=1);
/**
 *合并配置的.
 */

namespace YuanxinHealthy\HyperfDriver\ConfigNacos;

use Hyperf\Utils\Arr;

class NacosDriver extends \Hyperf\ConfigNacos\NacosDriver
{
    protected function updateConfig(array $config)
    {
        $listenerConfig = $this->config->get('config_center.drivers.nacos.listener_config');
        $root = $this->config->get('config_center.drivers.nacos.default_key');
        foreach ($config ?? [] as $key => $conf) {
            if (is_int($key)) {
                $key = $root;
            }
            $mergeMode = $this->config->get('config_center.drivers.nacos.merge_mode');
            if (is_array($listenerConfig) && isset($listenerConfig[$key]['merge_mode'])) {
                $mergeMode = $listenerConfig[$key]['merge_mode'];
            }
            if (is_array($conf)) {
                if ($mergeMode === Constants::CONFIG_MERGE_APPEND) {
                    $conf = Arr::merge($this->config->get($key, []), $conf);
                } elseif ($mergeMode === Constants::CONFIG_MERGE) {
                    $conf = array_merge($this->config->get($key, []), $conf);
                }
            }
            $this->config->set($key, $conf);
        }
    }
}
