<ul class="grid cs-style-1" style="list-style:none;">

    @foreach($images as $file)
    <li style="float:left;display:block;margin-left:10px;">
        <figure>
                <img  src= "{{URL::to('/image/'. $settings['package'].'/'.$settings['module'].'/'.$settings['id'].'/'.$settings['category'].
                '/'.$settings['size'].'_'.$file['file'])}}" class="{{{($file['default']) ? 'default' : ''}}} img-responsive">
            <figcaption>
                <h6>{{$file['name']}}</h6>
                <a href="{{URL::to('/admin/'.$settings['package'].'/'.$settings['module'].'/imageDelete/'.$settings['id'].'/'.$file['id'])}}" style="margin-top:0px;"><i class="fa fa-times" style="margin-top:0px;"></i> </a>
            </figcaption>
        </figure>
    </li>

    @endforeach
</ul>
