<?php

namespace app\modules\serviceProfile\services;

use app\modules\serviceProfile\models\ServiceProfile;

class RevisionComparisonService
{
    public function compare(ServiceProfile $current, ServiceProfile $previous): array
    {
        return $this->compareSections($current->sections,$previous->sections);
    }

    public function compareSections(array $currentSections,array $previousSections): array
    {
        $currentByCode=[]; foreach($currentSections as $section) $currentByCode[$section->section_code]=$section;
        $previousByCode=[]; foreach($previousSections as $section) $previousByCode[$section->section_code]=$section;
        $codes=array_values(array_unique(array_merge(array_keys($currentByCode),array_keys($previousByCode))));
        $rows=[];$summary=['added'=>0,'changed'=>0,'removed'=>0,'unchanged'=>0];
        foreach($codes as $code){
            $new=$currentByCode[$code]??null;$old=$previousByCode[$code]??null;
            if(!$old) $status='added'; elseif(!$new) $status='removed'; else $status=$this->fingerprint($new)===$this->fingerprint($old)?'unchanged':'changed';
            $summary[$status]++;
            $rows[]=['code'=>$code,'title'=>$new?->title??$old?->title??$code,'status'=>$status,'current'=>$new,'previous'=>$old];
        }
        usort($rows,static fn(array $a,array $b):int => (($a['current']?->sort_order??$a['previous']?->sort_order??9999)<=>($b['current']?->sort_order??$b['previous']?->sort_order??9999)));
        return ['summary'=>$summary,'rows'=>$rows];
    }

    private function fingerprint($section): string
    {
        return hash('sha256',json_encode(['title'=>$section->title,'block_type'=>$section->block_type,'is_required'=>(bool)$section->is_required,'content'=>$this->normalize($section->content),'data'=>$section->getData()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    private function normalize(?string $value): string
    {
        return preg_replace('/\s+/u',' ',trim(strip_tags((string)$value)))??'';
    }
}
