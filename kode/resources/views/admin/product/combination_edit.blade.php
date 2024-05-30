@if(count($combinations[0]) > 0)
<table class="table table-bordered ">
    <thead>
        <tr>
            <td class="text-start">
                {{translate('Variant')}}
            </td>
            <td class="text-start">
                {{translate('Variant Price')}}
            </td>
            <td class="text-start">
                {{translate('Quantity')}}
            </td>
        </tr>
    </thead>
    <tbody>
        @foreach ($combinations as $key => $combination)
            @php
                $str = '';
                foreach ($combination as $key => $item){
                    if($key > 0 ) {
                        $str .= '-'.str_replace(' ', '', $item);
                    }
                    else {

                         $str .= str_replace(' ', '', $item);

                    }
                    $stock = $product->stock->where('attribute_value', $str)->first();
                }
            @endphp
            @if(strlen($str) > 0)
            <tr class="variant">
                <td>
                    <label class="control-label mt-2 mb-0">{{ $str }}</label>
                </td>

                <td>
                    <input type="number"  name="price_{{ $str }}" value="@php
                            if ($product->unit_price == $unit_price) {
                                if($stock != null){
                                    echo round(($stock->price));
                                }
                                else {
                                    echo round(($unit_price));
                                }
                            }
                            else{
                                echo round(($unit_price));
                            }
                            @endphp" min="0" step="0.01" class="form-control" required>
                </td>

                <td>
                    <input type="number"  name="qty_{{ $str }}" value="@php
                            if($stock != null){
                                echo $stock->qty;
                            }
                            else{
                                echo '10';
                            }
                           @endphp" min="0" step="1" class="form-control" required>
                </td>
            </tr>
            @endif
        @endforeach

    </tbody>
</table>
@endif
