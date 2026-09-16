<?php
namespace app\modules\hr\services;

use DomainException;
use yii\db\Connection;

/** Explicit operator command only. Never runs as a side effect of viewing results. */
class EngagementRetentionService
{
    private Connection $db;
    public function __construct(Connection $db) { $this->db=$db; }
    public function due(?string $now=null): array
    {
        $now=$now??gmdate('Y-m-d H:i:s'); $service=new EngagementService($this->db); $due=[];
        foreach($service->query('round')->where(['status'=>['open','closed','published']])->all($this->db) as $round) {
            $expiry=(new \DateTimeImmutable($round['close_at'],new \DateTimeZone('UTC')))->modify('+'.(int)$round['retention_days'].' days')->format('Y-m-d H:i:s');
            if ($expiry<=$now && $service->query('invitation')->where(['round_id'=>$round['id']])->exists($this->db)) $due[]=['id'=>(int)$round['id'],'expires_at'=>$expiry];
        }
        return $due;
    }
    public function purge(int $roundId, string $reason, ?string $now=null): void
    {
        if (trim($reason)==='' || mb_strlen($reason)>255) throw new DomainException('A retention reason is required (maximum 255 characters).');
        $now=$now??gmdate('Y-m-d H:i:s'); $service=new EngagementService($this->db);
        $this->db->transaction(function() use($roundId,$reason,$now,$service) {
            $round=$service->row('round',$roundId,true);
            $expiry=(new \DateTimeImmutable($round['close_at'],new \DateTimeZone('UTC')))->modify('+'.(int)$round['retention_days'].' days')->format('Y-m-d H:i:s');
            if (!in_array($round['status'],['open','closed','published'],true) || $now<$expiry) throw new DomainException('Retention period has not expired.');
            if (!$service->query('invitation')->where(['round_id'=>$roundId])->exists($this->db)) return;
            if ($round['status']==='open') $this->db->createCommand()->update($service->table('round'),['status'=>'closed'],['id'=>$roundId])->execute();
            $responses=$service->query('response')->select('id')->where(['round_id'=>$roundId])->column($this->db);
            $this->db->createCommand()->delete($service->table('answer'),['response_id'=>$responses])->execute();
            $this->db->createCommand()->delete($service->table('response'),['round_id'=>$roundId])->execute();
            $this->db->createCommand()->delete($service->table('invitation'),['round_id'=>$roundId])->execute();
            $this->db->createCommand()->insert($service->table('audit'),['round_id'=>$roundId,'event'=>'raw_data_purged','actor_id'=>null,'reason'=>$reason,'occurred_at'=>$now])->execute();
        });
    }
}
