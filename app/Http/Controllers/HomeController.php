<?php

namespace App\Http\Controllers;

use App\Repositories\ReportsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Auth;
use Hash;


class HomeController extends Controller
{
    /** @var  SubUsersRepository */
    private $reportRepository;

    public function __construct(ReportsRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		return view('home');
    }
	
	public function dashboardStats()
    {
        $dashboardReport = $this->reportRepository->DashboardReport();

		//$dashboardReport['TotalCalls'] = $dashboardReport['Abandoned']+$dashboardReport['Answered'];
		
		if($dashboardReport['TotalCalls']==0) 
			$Connect=1; 
		else 
			$Connect=$dashboardReport['TotalCalls'];
		
		//$dashboardReport['Holdtime'] = gmdate("H:i:s", $dashboardReport['Holdtime']);
		
		$dashboardReport['AbandonRate'] = round($dashboardReport['Abandoned']*100/$Connect);
		$dashboardReport['AnswerRate'] = round($dashboardReport['Answered']*100/$Connect);
		
		$TotalTime = $dashboardReport['TotalTime'];
		$dashboardReport['TotalTime'] = gmdate("H:i:s", $dashboardReport['TotalTime']);
		

		$TotalAnswer=$dashboardReport['Answered']-$dashboardReport['Received'];
		
		if($TotalAnswer==0)
			$TotalAnswer=1;
			
		
		$dashboardReport['TalkTime'] = gmdate("H:i:s", $TotalTime/$TotalAnswer);
		$dashboardReport['WaitTime'] = gmdate("H:i:s", $dashboardReport['WaitTime']/$TotalAnswer);
		$dashboardReport['Holdtime'] = gmdate("H:i:s", $dashboardReport['Holdtime']/$TotalAnswer);

		//$dashboardReport['TalkTime'] = gmdate("H:i:s", round($dashboardReport['TalkTime']/($TotalTime*100)));
		//$dashboardReport['WaitTime'] = gmdate("H:i:s", round($dashboardReport['WaitTime']/($TotalTime*100)));
		
		if($dashboardReport['OBAnswer']==0)
			$OBAnswer=1;
		else
			$OBAnswer=$dashboardReport['OBAnswer'];

		$dashboardReport['OBAVGDuration'] = gmdate("H:i:s", ($dashboardReport['OBDuration']/($OBAnswer)));
		
		$dashboardReport['OBDuration'] = gmdate("H:i:s", $dashboardReport['OBDuration']);
		
		
		
		return response()->json($dashboardReport);
    }
	



}
