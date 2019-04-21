<?php

namespace App\Repositories;

use DB;
use Auth;
use Session;

class ReportsRepository {

    /**
     * @var App\Models\User
     */
    protected $db_pbx_cdr;

    public function DashboardReport($start=null,$end=null)
    {
        $start = isset($start)? $start: date("Y-m-d")." 00:00:00";
        $end = isset($end)? $end: date("Y-m-d")." 23:59:59";
        $json = array();


        $userExtention = implode(',',Auth::User()->extensions()->Pluck("extension_no")->ToArray()).", ".Auth::User()->did_no;

        $where = "((src in ($userExtention) AND Length(dst)>4) OR dst in ($userExtention))";

        $where = $where." and calldate between '".$start."' and '".$end."'";
        $Result = DB::connection('mysql3')
            ->table('cdr')
            ->select(DB::raw("DATE_FORMAT(calldate, '%Y-%m-%d %H:00') Createdhour,
                              count(*) as Total, 
                              IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, 
                              IFNULL(sum(case when src in ($userExtention) AND Length(dst)>4 then 1 end),0) as Outbound, 
                              sum(case when billsec>0 then 1 else 0 end) as Completed, 
                              sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration"))
            ->whereRaw($where)
            ->groupby(DB::raw("DATE_FORMAT(calldate, '%Y-%m-%d %H:00')"))->Get();


        $json['Hrs'] = $Result;

        $query = "select DATE_FORMAT(created, '%Y-%m-%d %H:00') as Createdhour, count(*) as Inbound 
                  from queue_log where verb in ('ENTERQUEUE') 
                  and created between '".$start." 00:00:00' and '".$end." 23:59:59' 
                  group by DATE_FORMAT(created, '%Y-%m-%d %H:00')";
        $Result = DB::connection('mysql2')->select($query);
        $json['HrsIB'] = $Result;

        //OB stats
        $where = "(src in ($userExtention) AND Length(dst)>4 )";
        $where = $where." and calldate between '".$start."' and '".$end."'";
        $Result = DB::connection('mysql3')->table('cdr')->select(DB::raw("count(*) as Total, IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, IFNULL(sum(case when src in ($userExtention) then 1 end),0) as Outbound, sum(case when billsec>0 then 1 else 0 end) as Completed, sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration, sum(billsec) as Billing"))
            ->whereRaw($where)->Get();
        $json['OBTotalTime'] = 0;
        $json['OBAnswer'] = 0;
        $json['OBUnanswer'] = 0;
        $json['OBDuration'] = 0;

        foreach($Result as $row)
        {
            $json['OBTotalTime'] = $row->Total;
            $json['OBAnswer'] = $row->Completed;
            $json['OBUnanswer'] = $row->Missed;
            $json['OBDuration'] = $row->Duration;
        }
        /*$query = "select verb, count(*) as total FROM queue_log where verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT') and queue IN ('4001', '4002') and created between '".$start."' and '".$end."' group by verb";
        $Result = DB::connection('mysql2')->select($query);
        $json['Received']="0";
        $json['Abandoned']="0";
        $json['Answered']="0";
        foreach($Result as $row)
        {
            if($row->verb == 'ENTERQUEUE') {
                $json['Received'] = $row->total;
            }
            else if($row->verb == 'ABANDON')
            {
                $json['Abandoned'] = $row->total;
            }
            else if($row->verb == 'CONNECT') {
                $json['Answered'] = $row->total;
            }
        }*/
        $queue =  implode(',',Auth::User()->queue()->Pluck("queue","queue")->ToArray());


        /*

        $query = "select sum(incall) as incall, sum(answer) as answer, sum(abandon) as abandon, sum(holdtime) as holdtime
                    FROM queuestats where queue IN ($queue) and created_at between '".$start."' and '".$end."'";
        $Result = DB::select($query);
        foreach($Result as $row)
        {
            $json['Abandoned'] = (isset($row->abandon)?$row->abandon:0);
            $json['Answered'] = (isset($row->answer)?$row->answer:0);
            $json['Holdtime'] = (isset($row->holdtime)?$row->holdtime:0);
        }

        */

        $query = "select count(*) as answer from queue_log 
                    where verb in ('CONNECT') and created between '".$start."' and '".$end."'";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $query .=  " and call_id not in 
                    (select call_id from queue_log where verb in ('COMPLETEAGENT', 'COMPLETECALLER')";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";
        $query .= "  and created between '".$start."' and '".$end."')";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $json['Received'] = $row->answer;
        }

        $query = "select  count( if(verb='abandon',1,NULL)) as abandon,
                    count(if(verb='connect',1,NULL)) as answered,
                    count(if(verb='enterqueue',1,NULL)) as totalcalls,
                    Ceiling(count(if(verb='connect',1,NULL))*100/count( if(verb='enterqueue',1,NULL))) as answeravg, 
                    Ceiling(count( if(verb='abandon',1,NULL))*100/count( if(verb='enterqueue',1,NULL))) as abandonavg
                    from queue_log where 
                    verb in ('connect','abandon','ENTERQUEUE') 
                    and created between '" . $start . "' and '" . $end . "'";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $json['TotalCalls'] = $row->totalcalls;
            $json['Abandoned'] = (isset($row->abandon)?$row->abandon:0);
            $json['Answered'] = (isset($row->answered)?$row->answered:0);
            $json['Holdtime'] = strtotime(isset($row->holdtime)?$row->holdtime:0);

        }

        $query = "select ROUND(sum(data1)) AS waittime from queue_log where verb='CONNECT' 
                  and  created between '".$start."' and '".$end."'";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $seconds =$row->waittime;
            $parse=gmdate("H:i:s", $seconds);
            $json['WaitTime'] = $row->waittime;
        }

        $query = "select ROUND(sum(data2)) AS talktime from queue_log where verb='COMPLETEAGENT' 
                  and created between '".$start."' and '".$end."'";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $talktime =$row->talktime;
            $json['TalkTime'] = $row->talktime;
        }

        $query = "select sum(data2) AS totaltime from queue_log where verb in ('COMPLETECALLER', 'COMPLETEAGENT') 
                  and  created between '".$start."' and '".$end."'";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $totaltime =$row->totaltime;
            $json['TotalTime'] = $row->totaltime;
        }

        $query = "select count(*) as waiting from queue_log 
                    where verb in ('ENTERQUEUE') and created between '".$start."' and '".$end."' 
                    and call_id not in ( select call_id from queue_log where verb in ('CONNECT', 'ABANDON') 
                    and created between '".$start."' and '".$end."')";
        $query .= (isset($queue) and $queue!="") ? " and queue IN ($queue)":"";

        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $json['Waiting'] = $row->waiting;
        }
        return $json;
    }
}

