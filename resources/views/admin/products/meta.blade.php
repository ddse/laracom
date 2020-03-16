@if(!$meta->isEmpty())
@foreach($meta as $meta)
<div class="form-group meta_attribute">
    <label for="{{ $meta->meta_key }}">{{ $meta->meta_key }}</label>
    <div class="input-group">
        <input type="hidden" name="{{ $meta->meta_key }}" id="{{ $meta->meta_key }}" placeholder="Meta Test 1" class="form-control" value="{{ $meta->meta_value }}">
        @php
            $metakey=['quantity','ps','bl','cs'];
            $meta->meta_key = str_replace('meta_', '', $meta->meta_key);
        @endphp
        @if(in_array($meta->meta_key, $metakey))
            @foreach(json_decode($meta->meta_value) as $key=>$val)
                <input type="text" name="{{ $meta->meta_key . '_' . $key  }}" id="{{ $meta->meta_key . '_' . $key }}" placeholder="Meta Test 1" class="form-control" value="{{ $val }}">
            @endforeach
        @elseif($meta->meta_key == 'release')
            @foreach(json_decode($meta->meta_value) as $val)
                @foreach($val as $k=>$v)
                    <input type="text" name="{{ $meta->meta_key . '_' . $key  }}" id="{{ $meta->meta_key . '_' . $key  }}" placeholder="Meta Test 1" class="form-control" value="{{$k}}">
                    <input type="text" name="{{ $meta->meta_key . '_' . $key  }}" id="{{ $meta->meta_key . '_' . $key  }}" placeholder="Meta Test 1" class="form-control" value="{{$v}}">
                @endforeach
            @endforeach
        @elseif($meta->meta_key == 'feature')
            @foreach(json_decode($meta->meta_value) as $key=>$val)
            @php
                $key++;
            @endphp
                <input type="text" name="{{ $meta->meta_key.$key }}" id="{{ $meta->meta_key.$key }}" placeholder="Meta Test 1" class="form-control" value="{{$val}}">
            @endforeach
        @else
            <input type="text" name="{{ $meta->meta_key }}" id="{{ $meta->meta_key }}" placeholder="Meta Test 1" class="form-control" value="{{ $meta->meta_value }}">
        @endif
    </div>
</div>
@endforeach
@endif