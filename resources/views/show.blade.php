@if($count == 1)
    @if(!empty($files))
    <div id="file">
        <a href="{!! URL::to($files['folder'])!!}/{!!$files['file']!!}" target="_blank">{!!$files['file']!!}</a>
    </div>
    @else
    	File not uploaded.
    @endif
@else
        @forelse($files as $key => $file)
        <div id="img_box_{!!$key!!}" class="img_box col-md-3 col-sm-3 col-xs-6">
                <a href="{!! URL::to($file['folder'])!!}/{!!$file['file']!!}" target="_blank"><img src="{!! URL::to('/image'.$file['folder'])!!}/sm_{!! $file['file'] !!}" class="img-thumbnail image-responsive"></a>

        </div>
        @empty
        Files not uploaded.
        @endif

@endif