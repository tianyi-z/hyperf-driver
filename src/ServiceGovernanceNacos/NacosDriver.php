<?php

declare(strict_types=1);
/**
 *服务发现的
 */

namespace YuanxinHealthy\HyperfDriver\ServiceGovernanceNacos;

use Hyperf\Nacos\Exception\RequestException;
use Hyperf\Utils\Codec\Json;

class NacosDriver extends \Hyperf\ServiceGovernanceNacos\NacosDriver
{
    public function getNodes(string $uri, string $name, array $metadata): array
    {
        $groupName = $this->config->get('services.drivers.nacos.group_name');
        $namespaceId = $this->config->get('services.drivers.nacos.namespace_id');
        $consumers = $this->config->get('services.consumers');
        if (!empty($consumers) && is_array($consumers)) {
            $consumers = array_column($consumers, null, 'name');
            if (!empty($consumers[$name]['namespace_id'])) {
                $namespaceId = $consumers[$name]['namespace_id'];
            }
            if (!empty($consumers[$name]['group_name'])) {
                $groupName = $consumers[$name]['group_name'];
            }
        }
        $response = $this->client->instance->list($name, [
            'groupName' => $groupName,
            'namespaceId' => $namespaceId,
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new RequestException((string)$response->getBody(), $response->getStatusCode());
        }

        $data = Json::decode((string)$response->getBody());
        $hosts = $data['hosts'] ?? [];
        $nodes = [];
        foreach ($hosts as $node) {
            if (isset($node['ip'], $node['port']) && ($node['healthy'] ?? false)) {
                $nodes[] = [
                    'host' => $node['ip'],
                    'port' => $node['port'],
                    'weight' => $this->getWeight($node['weight'] ?? 1),
                ];
            }
        }
        return $nodes;
    }

    private function getWeight($weight): int
    {
        return intval(100 * $weight);
    }
}
