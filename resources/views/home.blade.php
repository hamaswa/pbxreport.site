@extends('layouts.app')

@section('content-header')
    <h1>
        Voice
    </h1>
    <ol class="breadcrumb">
	    <li><a href="{{URL::asset('/')}}cms"><i class="fa fa-home"></i> Dashboard</a></li>
    </ol>
@endsection


@section('content')


<div class="row">
   <div class="col-xs-12">
      <div class="box">
         <div class="box-header">
            <h4 class="box-title">Inbound Queue Daily Stats</h4>
            <div class="box-tools">
            </div>
         </div>
         <!-- /.box-header -->
         <div class="box-body table-responsive no-padding">
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="Received"></div>
                     <h2 class="ssb-title">Talking</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="Answered"></div>
                     <h2 class="ssb-title">Answered Calls</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="Abandoned"></div>
                     <h2 class="ssb-title">Abandoned Calls</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="TotalCalls"></div>
                     <h2 class="ssb-title">Total Calls</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="TotalTime"></div>
                     <h2 class="ssb-title">Total Time</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="TalkTime"></div>
                     <h2 class="ssb-title">Average Talk Time</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="WaitTime"></div>
                     <h2 class="ssb-title">Average Wait Time</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="Holdtime"></div>
                     <h2 class="ssb-title">Average Hold Time</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="AnswerRate"></div>
                     <h2 class="ssb-title">Answer Rate</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="AbandonRate"></div>
                     <h2 class="ssb-title">Abandon Rate</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="Waiting"></div>
                     <h2 class="ssb-title">Waiting</h2>  
                   </a>
                </div>
            </div>
            <!-- ./col -->
            <!--<div class="col-lg-2 col-xs-6">
              <!-- small box >
              <div class="small-box bg-purple">
                <div class="inner" style="text-align:center; vertical-align:middle;">
                  <p>Waiting</p>
                  <h4  id="Waiting"></h4>
                </div>
                <div class="icon">
                  <i class="fa fa-line-chart"></i>
                </div>
              </div>
            </div>-->
         </div>
         <!-- /.box-body -->
      </div>
      <!-- /.box -->
   </div>
</div>

<div class="row">
   <div class="col-xs-12">
      <div class="box">
         <div class="box-header">
            <h4 class="box-title">Outbound Daily Stats</h4>
            <div class="box-tools">
            </div>
         </div>
         <!-- /.box-header -->
         <div class="box-body table-responsive no-padding">
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="OBTotalTime"></div>
                     <h2 class="ssb-title">Total Calls</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="OBAnswer"></div>
                     <h2 class="ssb-title">Answer</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="OBUnanswer"></div>
                     <h2 class="ssb-title">No Answer</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="OBDuration"></div>
                     <h2 class="ssb-title">Total Duration</h2>  
                   </a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="square-service-block">
                   <a href="javascript:void(0)">
                     <div class="ssb-icon" id="OBAVGDuration"></div>
                     <h2 class="ssb-title">Average Duration</h2>  
                   </a>
                </div>
            </div>
         </div>
         <!-- /.box-body -->
      </div>
      <!-- /.box -->
   </div>
</div>


<div class="row">
   <div class="col-xs-12">
      <div class="box">
         <div class="box-header">
            <h4 class="box-title">Real Time report</h4>
            <div class="box-tools">
            </div>
         </div>
         <!-- /.box-header -->
         <div class="box-body table-responsive no-padding">
            <table class="table table-hover" width="100%">
               <tbody>
                  <tr>
                    <th style="width:10%">Agent</th>
                    <th style="width:10%">Status</th>
                    <th style="width:10%">Online Time</th>
                  </tr>
               <tbody id="realBody">
               </tbody>
            </table>
         </div>
         <!-- /.box-body -->
      </div>
      <!-- /.box -->
   </div>
</div>
 
<div class="row">
   <div class="col-xs-12">
      <div class="box">
         <div class="box-header">
            <h4 class="box-title">Hourly report</h4>
            <div class="box-tools">
            </div>
         </div>
         <!-- /.box-header -->
         <div class="box-body table-responsive no-padding">
            <table class="table table-hover" width="100%">
               <tbody>
                  <tr>
                    <th style="width:10%">Time</th>
                    <th style="width:10%">Total</th>
                    <th style="width:10%">Inbound</th>
                    <th style="width:10%">Outbound</th>
                  </tr>
               <tbody id="hourlyBody">
               </tbody>
            </table>
         </div>
         <!-- /.box-body -->
      </div>
      <!-- /.box -->
   </div>
</div>

  <style>
	a:hover, a:focus {
	  color: #FAFAFA;
	  text-decoration: none;
	}
	.square-service-block{
		position:relative;
		overflow:hidden;
		margin:10px auto;
	}
	.square-service-block a {
	  background-color: #FAFAFA;
	  border-radius: 10px;
	  display: block;
	  padding: 50px 0px;
	  text-align: center;
	  width: 100%;
	}
	.square-service-block a:hover{
	  background-color: #FAFAFA;
	  border-radius: 10px;
	  text-decoration:none;
	}
	
	.ssb-icon {
	  color: #3d3d3d;
	  display: inline-block;
	  font-size: 18px;
	  font-weight: 800;
	  margin: 0 0 20px;
	}
	
	h2.ssb-title {
	  color: #DD4B39;
	  font-size: 20px;
	  font-weight: 600;
	  margin:0;
	  padding:0;
	  text-transform: uppercase;
	}

  </style>
@endsection


@push('scripts')

<script type="text/javascript">		

	setTimeout("getRealTime()",1000);
	function getRealTime()
	{
		var url = "{{ route("dashboard-stats") }}"
		$.ajax({
			url: url,
			type: 'GET',
			dataType: 'json',
			data: {method: '_POST', "_token": "{{ csrf_token() }}", submit: true},
			success: function(data) {
				$('#TotalCalls').html(data.TotalCalls);
				$('#Received').html(data.Received);
				$('#Abandoned').html(data.Abandoned);
				$('#Answered').html(data.Answered);
				$('#WaitTime').html(data.WaitTime);
				$('#TalkTime').html(data.TalkTime);
				$('#TotalTime').html(data.TotalTime);
				$('#AbandonRate').html(data.AbandonRate+'%');
				$('#AnswerRate').html(data.AnswerRate+'%');
				$('#Holdtime').html(data.Holdtime);
				$('#OBTotalTime').html(data.OBTotalTime);
				$('#OBAnswer').html(data.OBAnswer);
				$('#OBUnanswer').html(data.OBUnanswer);
				$('#OBDuration').html(data.OBDuration);
				$('#OBAVGDuration').html(data.OBAVGDuration);
				$('#Waiting').html(data.Waiting);
				$("#hourlyBody").html("");
				var index=0;
				$.each(data.Hrs,function(key1,value1){
					$.each(data.HrsIB,function(key2,value2){
						if(value2.Createdhour==value1.Createdhour)
						{
							$("#hourlyBody").append('<tr><td>'+value1.Createdhour+'</td><td>'+(parseInt(value2.Inbound)+parseInt(value1.Outbound))+'</td><td>'+value2.Inbound+'</td><td>'+value1.Outbound+'</td><tr>');
						}
					});
					
					index=index+1;
				});
			},
			error: function (result, status, err) {
				//alert(result.responseText);
				//alert(status.responseText);
				//alert(err.Message);
			}
		});
		
		var url = "{{ route('realtime-stats') }}"
		$.ajax({
			url: url,
			type: 'GET',
			dataType: 'json',
			data: {method: '_GET', "_token": "{{ csrf_token() }}" , submit: true},
			success: function (response) {
                if(response.length>0) {
                    $("#realBody").html("");
                } else {
                    $("#realBody").html("<tr><td colspan='4'>No Agent Login</td></tr>");
                }

                $.each(response, function (key, value) {
                    timeOnline = updateClock(value.login_time)
                    status = value.status;
                    color = '#ffffaa'
                    switch (value.status){
                        case '0':
                            status = "Unknown";
                            color ='#ffcf00';
                            break;
                        case '2':
                            status = "Online";
                            color ='#00ff00';
                            break;
                        case '4':
                            status = "Invalid";
                            color ='#ff4400';
                            break;
                        case '6':
                            status = "Ringing";
                            color ='#004ff0';
                            break;
                        case '8':
                            status = "OnHold";
                            color ='#aaffff';
                            break;
                    }

                    $("#realBody").append($('<tr><td>' + value.interface + '</td><td><span style="background-color:'+color+'">' + status + '</span></td><td now="' + response.currenttime + '"  start="' + value.login_time + '" id=' + value.id + ' class="time-elasped">' + timeOnline + '</td><tr>'));
                });

                function updateClock(startDateTime, nowTime) {
                    var startDateTime = new Date(startDateTime); // YYYY (M-1) D H m s (start time and date from DB)
                    var startStamp = startDateTime.getTime();

                    var estTime = new Date();
                    var nowTime = new Date(estTime.toLocaleString('en-US', {timeZone: 'Asia/Singapore'}));
                    var nowStamp = nowTime.getTime();

                    var diff = Math.round((nowStamp - startStamp) / 1000);

                    var d = Math.floor(diff / (24 * 60 * 60));
                    diff = diff - (d * 24 * 60 * 60);
                    var h = Math.floor(diff / (60 * 60));
                    diff = diff - (h * 60 * 60);
                    var m = Math.floor(diff / (60));
                    diff = diff - (m * 60);
                    var s = diff;

                    return   h + ":" + m + ":" + s;

                }
            },
			error: function (result, status, err) {
				///alert(result.responseText);
				///alert(status.responseText);
				///alert(err.Message);
			},
		});
	}

	

</script>

@endpush