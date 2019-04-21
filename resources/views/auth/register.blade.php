@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Register</div>

                    <div class="panel-body">
                        <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                            {{ csrf_field() }}

                            <div class="col-md-6 form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" required autofocus>

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email"
                                           value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>


                            <div class="col-md-6 form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">Mobile No</label>

                                <div class="col-md-6">
                                    <input id="mobile_no" type="number" class="form-control" name="mobile_no"
                                           value="{{ old('mobile_no') }}" required>

                                    @if ($errors->has('mobile_no'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('mobile_no') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 form-group{{ $errors->has('did_no') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">Did No</label>

                                <div class="col-md-6">
                                    <input id="did_no" type="number" class="form-control" name="did_no"
                                           value="{{ old('did_no') }}" required>

                                    @if ($errors->has('did_no'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('did_no') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="col-md-6 form-group{{ $errors->has('extension_no') ? ' has-error' : '' }}">
                                <label for="extension_no" class="col-md-4 control-label">Extension</label>

                                <div class="form-group col-sm-6">

                                    <select class="chosen-select form-control" data-placeholder="Select Extension"
                                            id="extension_id" name="extension_no[]" required multiple="multiple">
                                        @foreach($extension as $ext)
                                            <option value="{{ $ext->id }}">{{ $ext->description }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('extension_no'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('extension_no') }}</strong>
                                    </span>
                                    @endif

                                </div>
                            </div>
                            @role ('administrator')
                            <div class="form-group col-sm-6{{ $errors->has('active') ? ' has-error' : '' }}">
                                <label for=" active" class="col-md-4 control-label">Status</label>

                                <div class="form-group col-sm-6">
                                    <label class="radio-inline">
                                        {!! Form::radio('active', "1", null, array("checked" => true)) !!} Active
                                    </label>

                                    <label class="radio-inline">
                                        {!! Form::radio('active', "0", null) !!} Inactive
                                    </label>
                                </div>
                                @if ($errors->has('active'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('active') }}</strong>
                                    </span>
                                @endif

                            </div>

                            <div class="form-group col-sm-6{{ $errors->has('role') ? ' has-error' : '' }}">
                                <label for="role" class="col-md-4 control-label">Role</label>
                                <div class="form-group col-sm-6">
                                    <select class="chosen-select form-control" data-placeholder="Select Role"
                                            id="role_id" name="role" required>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @if ($errors->has('role'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('role') }}</strong>
                                    </span>
                                @endif

                            </div>
                            @endrole

                            <div class="form-group">
                                <div class="col-md-12 col-md-offset-10">
                                    <button type="submit" class="btn btn-primary">
                                        Register
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
