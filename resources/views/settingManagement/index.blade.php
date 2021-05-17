@extends('layouts.admin.master')
@section('title','Setting Management')
@section('stylesheets')
<link rel="stylesheet" type="text/css" href="{{asset('css/jquery-ui.min.css?vision=') .$vision }}">
<link href="{{ asset('css/multi_lang.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/ckeditor.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/drawingboard.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/tagsinput.css?vision=') .$vision }}" media="all" rel="stylesheet" type="text/css"/>
@endsection
@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <!-- Start content -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="breadcrumb-holder">
                                    <h1 class="main-title float-left">Setting</h1>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                            <!-- end row -->
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">						
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h3><i class="fa fa-user"></i> Setting details</h3>								
                                    </div>
                                        
                                    <div class="card-body">
                                        
                                    {{ Form::open(array('url' => "admin/setting/update", 'method' => 'post' ,'files' => true, 'id' => 'drawing-form')) }}
                        
                                        <div class="row">
                                            <div class="col-lg-9 col-xl-9">
                                                
                                                <div class="row">				
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            {{ Form::label('version_js_css','Version JS CSS', array('class' => 'labelas')) }} <span class="text-danger">*</span>
                                                            {{ Form::text('version_js_css', $setting->version_js_css, [ 'class' => 'form-control ','placeholder' => __('message.name'), 'required']) }}<br/>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>							
                                    </form>										
                                    </div>    
                                </div>
                            </div>
                        </div>
                            <!-- end col -->	                                  
                    </div>
                    <!-- end row -->	
                </div>

                <div class="content">
                    <div class="container-fluid">
                        <!-- end col -->	  
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">						
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h3><i class="fa fa-user"></i> Get All Plan HBS</h3>								
                                        </div>
                                            
                                        <div class="card-body">
                                            {{ Form::open(array('url' => "admin/setting/updatePlan", 'method' => 'post' ,'files' => true, 'id' => 'drawing-form')) }}
                                                <div class="row">
                                                    <div class="col-lg-9 col-xl-9">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>							
                                            </form>
                                        </div>									
                                    </div>
                                </div>
                            </div>
                        <!-- end col -->
                    </div>
                </div>

                <div class="content">
                    <div class="container-fluid">
                        <!-- end col -->	  
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">						
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h3><i class="fa fa-user"></i> Get All Provider Webbsite</h3>								
                                        </div>
                                            
                                        <div class="card-body">
                                            {{ Form::open(array('url' => "admin/setting/updateProvider", 'method' => 'post' ,'files' => true, 'id' => 'drawing-form')) }}
                                                <div class="row">
                                                    <div class="col-lg-9 col-xl-9">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>							
                                            </form>
                                        </div>									
                                    </div>
                                </div>
                            </div>
                        <!-- end col -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')

<script src="{{ asset('js/tinymce.js?vision=') .$vision }}"></script>
<script src="{{ asset('js/drawingboard.js?vision=') .$vision }}"></script>
<script src="{{ asset('js/tagsinput.js?vision=') .$vision }}"></script>
<script type="text/javascript">
var customBoard2 = new DrawingBoard.Board('custom-board-2', {
	controls: [
		'Color',
		{ Size: { type: 'dropdown' } },
		{ DrawingMode: { filler: false } },
		'Navigation',
		'Download'
	],
	size: 1,
	webStorage: 'session',
	enlargeYourContainer: true
});
$('#drawing-form').on('submit', function(e) {
    //get drawingboard content
    var img = customBoard2.getImg();

    //we keep drawingboard content only if it's not the 'blank canvas'
    var imgInput = (customBoard2.blankCanvas == img) ? '' : img;

    //put the drawingboard content in the form field to send it to the server
    $(this).find('input[name=image_signarure]').val( imgInput );

    //we can also assume that everything goes well server-side
    //and directly clear webstorage here so that the drawing isn't shown again after form submission
    //but the best would be to do when the server answers that everything went well
    customBoard2.clearWebStorage();
});
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
@endsection
