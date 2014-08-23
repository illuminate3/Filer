    <div class="owl-carousel controlls-over product-image" data-plugin-options='{"items": 1, "singleItem": true, "navigation": true, "pagination": true, "transitionStyle":"fadeUp"}'>
                                
                                  @foreach($images as $file)
                                <div>
                                    <img alt="" class="img-responsive" src="{{URL::to('/image/'. $settings['package'].'/'.$settings['module'].'/'.$settings['id'].'/'.$settings['category'].
                '/'.$settings['size'].'_'.$file['file'])}}">
                                </div>
                                 @endforeach


    </div>