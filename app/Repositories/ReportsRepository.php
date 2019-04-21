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


    public function QueueReport($inputs)
    {
        $queue = Auth::User()->queue()->Pluck("queue")->ToArray();
        $queue[]="00000";

        $start = (isset($inputs['dateFrom']) ? $inputs['dateFrom']." 00:00:00" : date("Y-m-d")." 00:00:00");
        $end = (isset($inputs['dateTo']) ? $inputs['dateTo']." 23:59:59" : date("Y-m-d")." 23:59:59");
        $json = array();


        $query = "select queue, sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received ,";
        $query.= "sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , ";
        $query.= "         sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned FROM queue_log ";
        $query.= " where queue IN (". implode(',',$queue ) . ") and  verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')";

        $query.= isset($inputs['queue'])?" and queue = '" . $inputs['queue'] ."'":"";

        $query.= " and created between '".$start."' and '".$end."'";
        $query.=" group by queue";

        $Result = DB::connection('mysql2')->select($query);
        return $Result;
    }

    public  function QueueReportByStatus($req){
        $query="";
        $groupby="";

        if(isset($req['queryby'])){

            if(isset($req['queryby']) and $req['queryby']=='month'){
                $start  = $req['year']."-01-01";
                $end  = $req['year']."-12-31";
                $groupby= "Group by  year(created), Month(created)";
                $query="select created, MONTHNAME(STR_TO_DATE(MONTH(created), '%m')) as month,
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received , 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , 
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned 
                  FROM queue_log where queue = '". $req['queue']."' and verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')
                  and created between '" . $start . "' and '". $end. "' " .$groupby;

            }
            else if (isset($req['queryby']) and $req['queryby'] == 'week') {
                $time = explode("-", $req['daterange']);
                $start = date("Y-m-d", strtotime($time[0])) . " 00:00";
                $end = date("Y-m-d", strtotime($time[1])) . " 23:59";
                $groupby= "Group by Month(created), Week(created)";

                $query="select concat(
                  STR_TO_DATE(concat(year(created), \" \" , week(created), ' sunday'), '%X %V %W'), ' <> ', 
                  STR_TO_DATE(concat(year(created), \" \" , week(created), ' saturday'), '%X %V %W')) AS week, 
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received , 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , 
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned 
                  FROM queue_log where queue = '". $req['queue']."' and verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')
                  and created between '" . $start . "' and '". $end. "' " .$groupby;

            }
            else if (isset($req['queryby']) and $req['queryby'] == 'day') {
                $time = explode("-", $req['daterange']);
                $start = date("Y-m-d", strtotime($time[0])) . " 00:00";
                $end = date("Y-m-d", strtotime($time[1])) . " 23:59";
                $groupby= "Group by  day";

                $query="select date(created) as day,
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received , 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , 
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned 
                  FROM queue_log where queue = '". $req['queue']."' and verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')
                  and created between '" . $start . "' and '". $end. "' " .$groupby;

            }
            else if (isset($req['queryby']) and $req['queryby'] == 'hour') {
                $start = date("Y-m-d", strtotime($req['daterange'])) . " 00:00";
                $end = date("Y-m-d", strtotime($req['daterange'])) . " 23:59";
                $groupby = "group by  hour";

                $query="select Hour(created) as hour,
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received , 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , 
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned 
                  FROM queue_log where queue = '". $req['queue']."' and verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')
                  and created between '" . $start . "' and '". $end. "' " .$groupby;



            } else if (isset($req['queryby']) and $req['queryby'] == 'minute') {
                $time = explode("-", $req['timepicker']);
                $start = date("Y-m-d", strtotime($req['daterange'])) . " " . $time[0];
                $end = date("Y-m-d", strtotime($req['daterange'])) . " " . $time[1];
                $groupby = "GROUP BY minute";
                $query="select  DATE_FORMAT(created,'%H:%i') minute,
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as Received , 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as Answered , 
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as Abandoned 
                  FROM queue_log where queue = '". $req['queue']."' and verb IN ('ENTERQUEUE', 'ABANDON', 'CONNECT')
                  and created between '" . $start . "' and '". $end. "' " .$groupby;

            }


            //return $query;
        }


        $Result = DB::connection('mysql2')->select($query);

        return array($Result,array('queryby'=>$req['queryby']));


    }

    public function ioUserReport($inputs)
    {

        //$where = "TRIM( SUBSTRING_INDEX(SUBSTRING_INDEX(clid,'>',1),'<',-1)) in (".implode(',',Auth::User()->Extension()->Pluck("extension_no")->ToArray()).") ";

        //$channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',',Auth::User()->Extension()->Pluck("extension_no")->ToArray());

        //$where = "$channel in ($userExtention) ";

        $where = "((src in ($userExtention) AND Length(dst)>4) OR dst in ($userExtention) )";

        $calling_from = "";

        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where." and calldate between '".$dateFrom." 00:00:00' and '".$dateTo." 23:59:59'";
        $where .= " and TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(clid,'>',1),'<',-1)) in ($userExtention) ";

        if(isset($inputs['calling_from']) != '')
        {
            $calling_from = $inputs['calling_from'];
            $where = $where." and TRIM( dst )='".$calling_from."'";
        }


        if (isset($inputs['type']) and $inputs['type'] != "") {

            $data = DB::connection('mysql3')->table('cdr')->select(DB::raw("TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(clid,'>',1),'<',-1)) AS caller_id_number, count(*) as Total, IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, IFNULL(sum(case when src in ($userExtention) AND Length(dst)>4 then 1 end),0) as Outbound, sum(case when billsec>0 then 1 else 0 end) as Completed, sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration"))
                ->whereRaw($where)
                ->get();

            $this->downloadCallReport($inputs['type'], $data);
        } else {
            return DB::connection('mysql3')->table('cdr')->select(DB::raw("TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(clid,'>',1),'<',-1)) AS caller_id_number, count(*) as Total, IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, IFNULL(sum(case when src in ($userExtention) AND Length(dst)>4 then 1 end),0) as Outbound, sum(case when billsec>0 then 1 else 0 end) as Completed, sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration"))
                ->whereRaw($where)
                ->groupby("clid")
                ->paginate(10)
                ->withPath('?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from);
        }
    }

    public function ioCallReport($inputs)
    {


        $channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',', Auth::User()->Extension()->Pluck("extension_no")->ToArray()) . ", " . Auth::User()->did_no;
        $where = "$channel in ($userExtention) ";

        $where = "((src in ($userExtention) AND Length(dst)>4) OR dst in ($userExtention) )";

        $dispo = "";
        $direction = "";
        $calling_from = "";
        $dialed_number = "";
        if (isset($inputs['dispo']) != '') {
            $dispo = $inputs['dispo'];
            if ($inputs['dispo'] == 0) {
                $disposition = "NO ANSWER";
            }
            if ($inputs['dispo'] == 1) {
                $disposition = "ANSWERED";
            }
            if ($inputs['dispo'] == 2) {
                $disposition = "BUSY";
            }
            if ($inputs['dispo'] == 3) {
                $disposition = "FAILED";
            }
            $where = $where . " and disposition='" . $disposition . "'";
        }

        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where . " and calldate between '" . $dateFrom . " 00:00:00' and '" . $dateTo . " 23:59:59'";

        if (isset($inputs['direction']) != '') {
            $direction = $inputs['direction'];
            $where = $where . " " . ($inputs['direction'] == 1 ? " and src in ($userExtention)" : " and dst in ($userExtention)");
        }

        if (isset($inputs['calling_from']) != '') {
            $calling_from = $inputs['calling_from'];
            $where = $where . " and src='" . $calling_from . "'";
        }

        if (isset($inputs['dialed_number']) != '') {
            $dialed_number = $inputs['dialed_number'];
            $where = $where . " and dst='" . $dialed_number . "'";
        }

        if (isset($inputs['type']) and $inputs['type'] != "") {

//            $data = DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
//                ->whereRaw($where)
//                ->get();
            //
            $data = DB::connection('mysql3')->table('cdr')->select(DB::raw("case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS 'Call Date Time', clid AS CallerID,dst AS Destination,disposition as Status,billsec as 'Talk Time',recordingfile As Recording"))
                ->whereRaw($where)
                ->get();

            $this->downloadCallReport($inputs['type'], $data);
        } else {

            return DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
                ->whereRaw($where)
                ->paginate(10)
                ->withPath('?dispo=' . $dispo . '&direction=' . $direction . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from . '&dialed_number=' . $dialed_number);
        }
    }

    public function downloadCallReport($type,$data){

        $data = json_decode(json_encode($data), True);
        return Excel::create('crd_data', function($excel) use ($data) {
            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download($type);

    }

    public function iCallReport($inputs)
    {
        //$where = "TRIM(dst) in (".implode(',',Auth::User()->Extension()->Pluck("extension_no")->ToArray()).") ";

        $channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',', Auth::User()->Extension()->Pluck("extension_no")->ToArray()) . ", " . Auth::User()->did_no;
        $where = "$channel in ($userExtention) ";

        $where = "dst in ($userExtention)";

        $dispo = "";
        $direction = "";
        $calling_from = "";
        $dialed_number = "";
        if (isset($inputs['dispo']) != '') {
            $dispo = $inputs['dispo'];
            if ($inputs['dispo'] == 0) {
                $disposition = "NO ANSWER";
            }
            if ($inputs['dispo'] == 1) {
                $disposition = "ANSWERED";
            }
            if ($inputs['dispo'] == 2) {
                $disposition = "BUSY";
            }
            if ($inputs['dispo'] == 3) {
                $disposition = "FAILED";
            }
            $where = $where . " and disposition='" . $disposition . "'";
        }

        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where . " and calldate between '" . $dateFrom . " 00:00:00' and '" . $dateTo . " 23:59:59'";

        if (isset($inputs['direction']) != '') {
            $direction = $inputs['direction'];
            $where = $where . " " . ($inputs['direction'] == 1 ? " and src in ($userExtention)" : " and dst in ($userExtention)");
        }

        if (isset($inputs['calling_from']) != '') {
            $calling_from = $inputs['calling_from'];
            $where = $where . " and src='" . $calling_from . "'";
        }

        if (isset($inputs['dialed_number']) != '') {
            $dialed_number = $inputs['dialed_number'];
            $where = $where . " and dst='" . $dialed_number . "'";
        }
        if (isset($inputs['type']) and $inputs['type'] != "") {

            $data = DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
                ->whereRaw($where)
                ->get();

            $this->downloadCallReport($inputs['type'], $data);
        } else {
            return DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
                ->whereRaw($where)
                ->paginate(10000)
                ->withPath('?dispo=' . $dispo . '&direction=' . $direction . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from . '&dialed_number=' . $dialed_number);
        }
    }

    public function oCallReport($inputs)
    {

        $channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',', Auth::User()->Extension()->Pluck("extension_no")->ToArray()) . ", " . Auth::User()->did_no;
        $where = "$channel in ($userExtention) ";

        $where = "src in ($userExtention) AND Length(dst)>4 ";

        $dispo = "";
        $direction = "";
        $calling_from = "";
        $dialed_number = "";
        if (isset($inputs['dispo']) != '') {
            $dispo = $inputs['dispo'];
            if ($inputs['dispo'] == 0) {
                $disposition = "NO ANSWER";
            }
            if ($inputs['dispo'] == 1) {
                $disposition = "ANSWERED";
            }
            if ($inputs['dispo'] == 2) {
                $disposition = "BUSY";
            }
            if ($inputs['dispo'] == 3) {
                $disposition = "FAILED";
            }
            $where = $where . " and disposition='" . $disposition . "'";
        }

        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where . " and calldate between '" . $dateFrom . " 00:00:00' and '" . $dateTo . " 23:59:59'";

        if (isset($inputs['direction']) != '') {
            $direction = $inputs['direction'];
            $where = $where . " " . ($inputs['direction'] == 1 ? " and src in ($userExtention)" : " and dst in ($userExtention)");
        }

        if (isset($inputs['calling_from']) != '') {
            $calling_from = $inputs['calling_from'];
            $where = $where . " and src='" . $calling_from . "'";
        }

        if (isset($inputs['dialed_number']) != '') {
            $dialed_number = $inputs['dialed_number'];
            $where = $where . " and dst='" . $dialed_number . "'";
        }

        if (isset($inputs['type']) and $inputs['type'] != "") {

            $data = DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
                ->whereRaw($where)
                ->get();

            $this->downloadCallReport($inputs['type'], $data);
        } else {
            return DB::connection('mysql3')->table('cdr')->select(DB::raw("$channel as channelVal, DATE_FORMAT(calldate,'%d-%m-%Y %H:%i:%s') AS calldate,cnam, src AS outbound_caller_id,dst AS destination,disposition,billsec, (duration-billsec) as ringtime, recordingfile As Recording, case when dst in ($userExtention) then 'Inbound' else 'Outbound' end as Direction, clid AS CallerID"))
                ->whereRaw($where)
                ->paginate(10000)
                ->withPath('?dispo=' . $dispo . '&direction=' . $direction . '&dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from . '&dialed_number=' . $dialed_number);
        }
    }

    public function iUserReport($inputs)
    {
        $channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',', Auth::User()->Extension()->Pluck("extension_no")->ToArray()) . ", " . Auth::User()->did_no;

        $where = "dst in ($userExtention)";
        $calling_from = "";
        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where . " and calldate between '" . $dateFrom . " 00:00:00' and '" . $dateTo . " 23:59:59'";
        if (isset($inputs['calling_from']) != '') {
            $calling_from = $inputs['calling_from'];
            $where = $where . " and TRIM(dst)='" . $calling_from . "'";
        }


        return DB::connection('mysql3')->table('cdr')->select(DB::raw("CONCAT(cnam,'<',dst,'>') AS caller_id_number,cnam, dst, count(*) as Total, IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, IFNULL(sum(case when src in ($userExtention) AND Length(dst)>4 then 1 end),0) as Outbound, sum(case when billsec>0 then 1 else 0 end) as Completed, sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration, sum(billsec) as Billing"))
            ->whereRaw($where)
            ->groupby("dst")
            ->groupby("cnam")
            ->paginate(10)
            ->withPath('?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from);

    }

    public function oUserReport($inputs)
    {
        $channel = "TRIM(REPLACE(SUBSTRING(channel,1,LOCATE(\"-\",channel,LENGTH(channel)-8)-1),\"SIP/\",\"\"))";
        $userExtention = implode(',', Auth::User()->Extension()->Pluck("extension_no")->ToArray()) . ", " . Auth::User()->did_no;

        $where = "src in ($userExtention) AND Length(dst)>4 ";
        $calling_from = "";
        $dateFrom = (isset($inputs['dateFrom']) ? $inputs['dateFrom'] : date("Y-m-d"));
        $dateTo = (isset($inputs['dateTo']) ? $inputs['dateTo'] : date("Y-m-d"));
        Session::put('dateFrom', $dateFrom);
        Session::put('dateTo', $dateTo);
        $where = $where . " and calldate between '" . $dateFrom . " 00:00:00' and '" . $dateTo . " 23:59:59'";
        if (isset($inputs['calling_from']) != '') {
            $calling_from = $inputs['calling_from'];
            $where = $where . " and TRIM(dst)='" . $calling_from . "'";
        }


        return DB::connection('mysql3')->table('cdr')->select(DB::raw("CONCAT(cnam,'<',cnum,'>')  AS caller_id_number, cnam, count(*) as Total, IFNULL(sum(case when dst in ($userExtention) then 1 end),0) as Inbound, IFNULL(sum(case when src in ($userExtention) AND Length(dst)>4 then 1 end),0) as Outbound, sum(case when billsec>0 then 1 else 0 end) as Completed, sum(case when billsec=0 then 1 else 0 end) as Missed, sum(billsec) as Duration, sum(billsec) as Billing"))
            ->whereRaw($where)
            ->groupby("cnum")
            ->groupby("cnam")
            ->paginate(10)
            ->withPath('?dateFrom=' . $dateFrom . '&dateTo=' . $dateTo . '&calling_from=' . $calling_from);

    }

    public function distributionSubData($req){
        $type= $req['type'];
        $start=$req['dateFrom'];
        $end=$req['dateTo'];
        $queue=$req['queue'];
        $agent=$req['agent'];
        $json['type']=$type;
        switch ($type){
            case "queue":
                $queue = $req['typeval'];
                $query = "Select distinct call_id, created as date, verb,agent,event,data,data1,data2,data3,data4
                         from queue_log 
                         where verb in ('connect','abandon','ENTERQUEUE') and created >= '" . $start . "' 
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";
                $json['data'] = DB::connection('mysql2')->select($query);
                return $json;
                break;
            case "month":
                $month = $req['typeval'];
                $query = "Select created as date, 
                        call_id,verb,agent,event,data,data1,data2,data3,data4
                         from queue_log 
                         where created>='" . $start . "' 
                         and DATE_FORMAT(created,'%M %Y') = '" . $month . "' 
                         and verb in ('connect','abandon','ENTERQUEUE') 
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";
                $json['data'] = DB::connection('mysql2')->select($query);

                return $json;
                break;
            case "week":
                $week=$req['typeval'];
                $query = "Select created as date,
                        call_id,verb,agent,event,data,data1,data2,data3,data4
                         from queue_log 
                         where Week(created) = '" . $week . "' 
                         and created>='".$start."' 
                         and verb in ('connect','abandon','ENTERQUEUE') 
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";
                $json['data'] = DB::connection('mysql2')->select($query);
                return $json;
                break;

            case "day":
                $day = $req['typeval'];
                $query = "Select created as date,
                        call_id,verb,agent,event,data,data1,data2,data3,data4
                         from queue_log 
                         where Date_format(created,'%Y-%m-%d') = '" . $day . "'  
                         and verb in ('connect','abandon','ENTERQUEUE') 
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";
                $json['data'] = DB::connection('mysql2')->select($query);
                return $json;
                break;

            case "hour":
                $hour = $req['typeval'];
                $query = "Select created as date,
                          call_id,verb,agent,event,data,data1,data2,data3,data4
                         from queue_log 
                         where hour(created) = '" . $hour . "' and created >= '". $start ."'  
                         and verb in ('connect','abandon','ENTERQUEUE')
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";

                $json['data'] = DB::connection('mysql2')->select($query);
                return $json;
                break;
            case "dayweek":
                $day = $req['typeval'];
                $query = "Select created as date,
                        call_id,verb,agent,event,data,data1,data2,data3,data4
                         from queue_log
                         where Date_format(created,'%Y-%m-%d') = '" . $day . "'
                         and created >= '". $start ."'  and verb in ('connect','abandon','ENTERQUEUE')
                         and queue in (" . (isset($queue) and $queue!=""?$queue:"queuequeue"). ")";
                $json['data'] = DB::connection('mysql2')->select($query);
                return $json;
                break;



        }


    }

    public function realTimeReport($request){
        $userExtensions = Auth::User()->Extension()->Pluck("extension_no")->ToArray();
        $userExtensions[] = Auth::User()->did_no;

        $sql = "Select * from agentlogin 
        where interface in ('" . implode("','",$userExtensions) . "') 
        and event='QueueMemberAdded'
        and  logout_time is NULL";
        //echo $sql;
        return DB::connection('mysql')->select($sql);
    }



    public function distribution($req)
    {
        $date = explode("-", $req['daterange']);
        $start = date('Y-m-d', strtotime($date[0]));
        $starthr = $req['hour1'] . ":" . $req['minute1'];
        $end = date('Y-m-d', strtotime($date[1]));
        $endhr = $req['hour2'] . ":" . $req['minute2'];
        $json = array();
        $json['available_queue'] = implode(',',$req['queue']);
        $json['start_date'] = date('Y/m/d', strtotime($start));
        $json['end_date'] = date('Y/m/d', strtotime($start));
        $json['hour_range'] = $req['hour1'] . ":" . $req['minute1'] . " - " . $req['hour2'] . ":" . $req['minute2'];
        $json['timefrom'] = $req['hour1'] . ":" . $req['minute1'];
        $json['timeto'] = $req['hour2'] . ":" . $req['minute2'];
        $json['period'] = round((strtotime($end)  - strtotime($start)) / (60 * 60 * 24))+1;
        $json['datefrom'] = $start;
        $json['dateto'] = $end;
        $json['agents'] = isset($req['agents'])?implode(',',$req['agents']):"N0NE";
        $ext = '"'.implode('","',$this->extensions($json['agents'])) .'"';




        $query = "select sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  sum(CASE verb When 'ABANDON' Then 1 else 0 End) as abandon, 
                  sum(CASE verb When 'CONNECT' Then 1 else 0 End) as answered,
                  Ceiling(sum(CASE verb When 'CONNECT' Then 1 else 0 End)*100/count(distinct call_id)) as answeravg,
                  Ceiling(sum(CASE verb When 'ABANDON' Then 1 else 0 End)*100/count(distinct call_id)) as abandonavg
                  from queue_log where verb in ('connect','abandon','ENTERQUEUE') 
                  and queue in (" . $json['available_queue'] . ")
                  and call_id in 
                  
                  (select call_id from queue_log 
                        where (agent in ($ext) and verb='connect') 
                        or verb='abandon')

                  and DATE_FORMAT(created, '%H:%i') between '" . $starthr . "' and '" . $endhr . "'
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59'";

        $Result = DB::connection('mysql2')->select($query);

        foreach ($Result as $row) {
            $json['total_calls']['Received'] = $row->received;
            $json['total_calls']['Answered'] = $row->answered;
            $json['total_calls']['Abandoned'] = $row->abandon;
            $json['total_calls']['AbandonRate'] = $row->abandonavg;
            $json['total_calls']['AnswerRate'] = $row->answeravg;
        }

        /*$query = "select count(distinct call_id) as answered from queue_log
                    where verb='connect' and queue in (". $json['available_queue'] .")
                    and created between '".$start."' and '".$end."'";
        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $json['total_calls']['Answered'] = $row->answered;
        }

        $query = "select count(distinct call_id) as abandon from queue_log
                    where verb='abandon' and queue in (". $json['available_queue'] .")
                    and created between '".$start."' and '".$end."'";
        $Result = DB::connection('mysql2')->select($query);
        foreach($Result as $row)
        {
            $json['total_calls']['Abandoned'] = $row->abandon;
        } */

        //$json['total_calls']['AbandonRate'] = round($json['total_calls']['Abandoned'] * 100 / $json['total_calls']['Received']);
        //$json['total_calls']['AnswerRate'] = round($json['total_calls']['Answered'] * 100 / $json['total_calls']['Received']);

        $query = "select queue, sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg
                  from queue_log where 
                  verb in ('connect','abandon','ENTERQUEUE') 
                  and queue in (" . $json['available_queue'] . ")
                  
                  and call_id in 
                        (select call_id from queue_log 
                        where (agent in ($ext) and verb='connect') 
                        or verb='abandon')
                        
                  and created between '" . $start . "  00:00:00' and '"  . $end . " 23:59:59' group by queue";


        $json['dist_by_queue'] = DB::connection('mysql2')->select($query);
        $json['dist_by_queue_chart'] = json_encode(DB::connection('mysql2')->select($query), 1);

//        foreach ($json['dist_by_queue'] as $key => $item) {
//            $query = "Select distinct call_id, created as date, verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where verb in ('connect','abandon','ENTERQUEUE') and created >= '" . $start . "' and queue='" . $item->queue . "'";
//            $json['dist_by_queue'][$key]->sub_data = DB::connection('mysql2')->select($query);
//
//        }


        $query = "select DATE_FORMAT(created,'%M %Y') as month,queue, 
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg,
                  count(if(verb='EXITWITHTIMEOUT',1,NULL)) unanswered
                  from queue_log where  
                  queue in (" . $json['available_queue'] . ")
                  
                  and call_id in                  
                  (select call_id from queue_log 
                        where (agent in ($ext) and verb='connect') 
                        or verb='abandon')
                        
                  and verb IN ('ABANDON', 'CONNECT','ENTERQUEUE')
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59' group by month order by id";

        $json['dist_by_month'] = DB::connection('mysql2')->select($query);

//        foreach ($json['dist_by_month'] as $key => $item) {
//            $query = "Select created as date,
//                        call_id,verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where created>='" . $start . "' and DATE_FORMAT(created,'%M %Y') = '" . $item->month . "' and verb in ('connect','abandon','ENTERQUEUE') and queue in ( " . $json['available_queue'] . ")";
//
//            $json['dist_by_month'][$key]->sub_data = DB::connection('mysql2')->select($query);
//        }

        $groupby = "Group by Month(created), Week(created)";

        $query = "select Week(created) AS week, 
                   queue, sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg
                  FROM queue_log where 
                  queue in (" . $json['available_queue'] . ")

                    and call_id in               
                        (select call_id from queue_log 
                        where (agent in ($ext) and verb='connect') 
                        or verb='abandon')                  
                  
                  and verb IN ('ABANDON', 'CONNECT','ENTERQUEUE')
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59' " . $groupby . " order by id";

        $json['dist_by_week'] = DB::connection('mysql2')->select($query);

//            foreach ($json['dist_by_week'] as $key => $item) {
//                $query = "Select created as date,
//                        call_id,verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where Week(created) = '" . $item->week . "' and created>='".$start."' and verb in ('connect','abandon','ENTERQUEUE') and queue in ( " . $json['available_queue'] . ")";
//
//                $json['dist_by_week'][$key]->sub_data = DB::connection('mysql2')->select($query);
//
//                //*///
//            }


        $groupby = "Group by day";

        $query = "select Date_format(created,'%Y-%m-%d') AS day, 
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg,
                  count(if(verb='EXITWITHTIMEOUT',1,NULL)) unanswered
                  FROM queue_log where 
                  queue in (" . $json['available_queue'] . ")

                  and call_id in                  
                  (select call_id from queue_log 
                        where (agent in ($ext) and verb='connect') 
                        or verb='abandon')
                  and verb IN ('ABANDON', 'CONNECT','ENTERQUEUE')
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59' " . $groupby . " order by id";

        $json['dist_by_day'] = DB::connection('mysql2')->select($query);
        $json['dist_by_day_chart'] = json_encode(DB::connection('mysql2')->select($query), 1);

//            foreach ($json['dist_by_day'] as $key => $item) {
//                $query = "Select created as date,
//                        call_id,verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where Date_format(created,'%Y-%m-%d') = '" . $item->day . "'  and verb in ('connect','abandon','ENTERQUEUE') and queue in ( " . $json['available_queue'] . ")";
//
//                $json['dist_by_day'][$key]->sub_data = DB::connection('mysql2')->select($query);
//
//                //*///
//            }


        $groupby = "Group by hour";

        $query = "select Hour(created) AS hour, 
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg,
                  count(if(verb='EXITWITHTIMEOUT',1,NULL)) unanswered
                  FROM queue_log where 
                  queue in (" . $json['available_queue'] . ")
                  and (agent in (". $ext.") or agent ='NONE')
                  and verb IN ('ABANDON', 'CONNECT','ENTERQUEUE')
                  and DATE_FORMAT(created, '%H:%i') between '" . $starthr . "' and '" . $endhr . "'
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59' " . $groupby . " order by hour";

        $json['dist_by_hour'] = DB::connection('mysql2')->select($query);
        $json['dist_by_hour_chart'] = json_encode(DB::connection('mysql2')->select($query), 1);

//            foreach ($json['dist_by_hour'] as $key => $item) {
//                $query = "Select created as date,
//                          call_id,verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where hour(created) = '" . $item->hour . "' and created >= '". $start ."'
//                         and verb in ('connect','abandon','ENTERQUEUE') and queue in ( " . $json['available_queue'] . ")";
//
//                $json['dist_by_hour'][$key]->sub_data = DB::connection('mysql2')->select($query);
//
//                //*///
//            }


        $groupby = "Group by day";

        $query = "select Dayname(created) AS day, 
                  sum(CASE verb When 'ENTERQUEUE' Then 1 else 0 End) as received,
                  count(if(verb='abandon',1,NULL)) as abandon, 
                  count(if(verb='connect',1,NULL)) as answered,
                  Ceiling(count(if(verb='connect',1,NULL))*100/count(distinct call_id)) as answeravg,
                  Ceiling(count(if(verb='abandon',1,NULL))*100/count(distinct call_id)) as abandonavg,
                  count(if(verb='EXITWITHTIMEOUT',1,NULL)) unanswered
                  FROM queue_log where 
                  queue in (" . $json['available_queue'] . ")
                  and (agent in (". $ext.") or agent ='NONE')
                  and verb IN ('ABANDON', 'CONNECT','ENTERQUEUE')
                  and created between '" . $start . " 00:00:00' and '" . $end . " 23:59:59' " . $groupby . " order by DAYOFWEEK(created)";

        $json['dist_by_weekday'] = DB::connection('mysql2')->select($query);

//           foreach ($json['dist_by_weekday'] as $key => $item) {
//                $query = "Select created as date,
//                        call_id,verb,agent,event,data,data1,data2,data3,data4
//                         from queue_log
//                         where Date_format(created,'%Y-%m-%d') = '" . $item->day . "' and created >= '". $start ."'  and verb in ('connect','abandon','ENTERQUEUE') and queue in ( " . $json['available_queue'] . ")";
//
//                $json['dist_by_weekday'][$key]->sub_data = DB::connection('mysql2')->select($query);
//
//
//            }
        //*///


        return $json;

    }

public function extensions($where=""){
        if($where=="") {
            $data = DB::connection('mysql4')->table('devices')->select(DB::raw('id,description'))->get()->ToArray();
        }
        else
            $data = DB::connection('mysql4')->table('devices')->select(DB::raw('id,description'))->whereRaw('id in ('.$where.')')->get()->toArray();


    $data=json_decode(json_encode($data), true);
    $temp_ext = array();
    foreach ($data as $item) {
        $temp_ext[$item['id']]=$item['description'];
    }
    return $temp_ext;
}

}

