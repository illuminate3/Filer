<ul class="grid cs-style-1">

	@foreach($images as $file)
	<li>
        <figure>
				<img  src= "{{URL::to('/image/'. $settings['package'].'/'.$settings['module'].'/'.$settings['id'].'/'.$settings['category'].
				'/'.$settings['size'].'_'.$file['file'])}}" class="{{{($file['default']) ? 'default' : ''}}} ">
			<figcaption>
				<h3>{{$file['name']}}</h3>
                <span>{{($file['default']) ? '<i class="fa fa-check-circle-o fa-3x"></i>' : '<i class="fa fa-circle-o fa-3x"></i>'}}</span>
                <a href="{{URL::to('/admin/'.$settings['package'].'/'.$settings['module'].'/edit/'.$settings['id'].'/'.$file['id'])}}"><i class="fa fa-pencil"></i></a>
                <a href="{{URL::to('/admin/'.$settings['package'].'/'.$settings['module'].'/default/'.$settings['id'].'/'.$file['id'])}}"><i class="fa fa-check"></i></a>
                <a href="{{URL::to('/admin/'.$settings['package'].'/'.$settings['module'].'/delete/'.$settings['id'].'/'.$file['id'])}}"><i class="fa fa-times"></i> </a>

			</figcaption>
		</figure>
    </li>

	@endforeach
</ul>