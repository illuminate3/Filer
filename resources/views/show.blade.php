@if($count == 1)
    @if(!empty($files))
        <?php
        $info   = pathinfo($files['file']);
        $ext    = strtolower($info['extension']);
        ?>
        @if (in_array($ext, ['jpg','jpeg', 'png', 'gif']) )
            <a href="{!! URL::to($files['folder'])!!}/{!!$files['file']!!}" target="_blank"><img src="{!! URL::to('/image'.$files['folder'])!!}/sm_{!! $files['file'] !!}" class="img-thumbnail image-responsive"></a>
        @else
            <div id="file">
                <a href="{!! URL::to($files['folder'])!!}/{!!$files['file']!!}" target="_blank">{!!$files['file']!!}</a>
            </div>
        @endif
    @else
    	File not uploaded.
    @endif
@else
        @forelse($files as $key => $file)
            <?php
            $info   = pathinfo($file['file']);
            $ext    = strtolower($info['extension']);
            ?>
            @if (in_array($ext, ['jpg','jpeg', 'png', 'gif']) )
            <div id="img_box_{!!$key!!}" class="img_box col-md-3 col-sm-3 col-xs-6">
                    <a href="{!! URL::to($file['folder'])!!}/{!!$file['file']!!}" target="_blank"><img src="{!! URL::to('/image'.$file['folder'])!!}/sm_{!! $file['file'] !!}" class="img-thumbnail image-responsive"></a>

            </div>
            @else
                <div id="file">
                    <a href="{!! URL::to($files['folder'])!!}/{!!$files['file']!!}" target="_blank">{!!$files['file']!!}</a>
                </div>
            @endif
        @empty
        Files not uploaded.
        @endif

@endif